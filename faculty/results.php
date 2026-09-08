<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "faculty"
) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$facultyId = $_SESSION["faculty_id"] ?? null;

if (!$facultyId) {
    header("Location: ../index.php");
    exit;
}

$classes = [];

$classQuery = $conn->prepare("
    SELECT DISTINCT
        timetable.subject_id,
        timetable.department_id,
        timetable.semester,
        subjects.subject_name,
        subjects.subject_code,
        departments.department_name
    FROM timetable
    INNER JOIN subjects
        ON timetable.subject_id = subjects.id
    INNER JOIN departments
        ON timetable.department_id = departments.id
    WHERE timetable.faculty_id = ?
    AND timetable.status = 1
    ORDER BY
        departments.department_name,
        timetable.semester,
        subjects.subject_name
");

$classQuery->bind_param("i", $facultyId);
$classQuery->execute();

$classResult = $classQuery->get_result();

while ($row = $classResult->fetch_assoc()) {
    $classes[] = $row;
}

$selectedClass = null;
$students = [];

$subjectId = isset($_GET["subject_id"])
    ? (int) $_GET["subject_id"]
    : 0;

if ($subjectId > 0 && $facultyId) {

    $selectedQuery = $conn->prepare("
        SELECT
            timetable.subject_id,
            timetable.department_id,
            timetable.semester,
            subjects.subject_name,
            subjects.subject_code,
            departments.department_name
        FROM timetable
        INNER JOIN subjects
            ON timetable.subject_id = subjects.id
        INNER JOIN departments
            ON timetable.department_id = departments.id
        WHERE timetable.subject_id = ?
        AND timetable.faculty_id = ?
        AND timetable.status = 1
        LIMIT 1
    ");

    $selectedQuery->bind_param(
        "ii",
        $subjectId,
        $facultyId
    );

    $selectedQuery->execute();

    $selectedResult = $selectedQuery->get_result();

    if ($selectedResult->num_rows === 1) {

        $selectedClass = $selectedResult->fetch_assoc();

        $selectedExamType = $_GET["exam_type"] ?? "";

        $studentQuery = $conn->prepare("
            SELECT *
            FROM students
            WHERE course = ?
            AND semester = ?
            AND status = 1
            ORDER BY first_name, last_name
        ");

        $studentQuery->bind_param(
            "si",
            $selectedClass["department_name"],
            $selectedClass["semester"]
        );

        $studentQuery->execute();

        $studentResult = $studentQuery->get_result();

        while ($student = $studentResult->fetch_assoc()) {
            $students[] = $student;
        }

        $savedMarks = [];

        $savedTotalMarks = "";

            if ($selectedExamType !== "") {

                $marksQuery = $conn->prepare("
                    SELECT student_id, obtained_marks, total_marks
                    FROM results
                    WHERE subject_id = ?
                    AND semester = ?
                    AND exam_type = ?
                ");

                $marksQuery->bind_param(
                    "iis",
                    $subjectId,
                    $selectedClass["semester"],
                    $selectedExamType
                );

                $marksQuery->execute();

                $marksResult = $marksQuery->get_result();

                while ($mark = $marksResult->fetch_assoc()) {

                    $savedMarks[$mark["student_id"]] =
                        $mark["obtained_marks"];

                    if ($savedTotalMarks === "") {
                        $savedTotalMarks = $mark["total_marks"];
                    }
                }
            }

            
    }


}

// Results
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["save_results"])
) {
    $postSubjectId = (int) ($_POST["subject_id"] ?? 0);
    $semester = (int) ($_POST["semester"] ?? 0);
    $examType = $_POST["exam_type"] ?? "";
    $totalMarks = (float) ($_POST["total_marks"] ?? 0);
    $marksData = $_POST["marks"] ?? [];
    
   
    $allowedExamTypes = [
        "Internal",
        "Mid Term",
        "Final",
        "Practical"
    ];

    if (
        $postSubjectId > 0 &&
        $semester > 0 &&
        $totalMarks > 0 &&
        in_array($examType, $allowedExamTypes, true)
    ) {

        // Verify subject belongs to logged-in faculty
        $verifyQuery = $conn->prepare("
            SELECT id
            FROM timetable
            WHERE subject_id = ?
            AND semester = ?
            AND faculty_id = ?
            AND status = 1
            LIMIT 1
        ");

        $verifyQuery->bind_param(
            "iii",
            $postSubjectId,
            $semester,
            $facultyId
        );

        $verifyQuery->execute();
        $verifyResult = $verifyQuery->get_result();

        if ($verifyResult->num_rows === 1) {

            foreach ($marksData as $studentId => $obtainedMarks) {

                $studentId = (int) $studentId;
                $obtainedMarks = (float) $obtainedMarks;

                if (
                    $obtainedMarks < 0 ||
                    $obtainedMarks > $totalMarks
                ) {
                    continue;
                }

                $percentage =
                    ($obtainedMarks / $totalMarks) * 100;

                if ($percentage >= 90) {
                    $grade = "A+";
                } elseif ($percentage >= 80) {
                    $grade = "A";
                } elseif ($percentage >= 70) {
                    $grade = "B+";
                } elseif ($percentage >= 60) {
                    $grade = "B";
                } elseif ($percentage >= 50) {
                    $grade = "C";
                } elseif ($percentage >= 40) {
                    $grade = "D";
                } else {
                    $grade = "F";
                }

                $resultStatus =
                    $percentage >= 40
                        ? "Pass"
                        : "Fail";

                $saveQuery = $conn->prepare("
                    INSERT INTO results
                    (
                        student_id,
                        subject_id,
                        semester,
                        exam_type,
                        total_marks,
                        obtained_marks,
                        grade,
                        result_status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)

                    ON DUPLICATE KEY UPDATE
                        total_marks = VALUES(total_marks),
                        obtained_marks = VALUES(obtained_marks),
                        grade = VALUES(grade),
                        result_status = VALUES(result_status)
                ");

                if (!$saveQuery) {
    die("Prepare Error: " . $conn->error);
}

                $saveQuery->bind_param(
                    "iiisddss",
                    $studentId,
                    $postSubjectId,
                    $semester,
                    $examType,
                    $totalMarks,
                    $obtainedMarks,
                    $grade,
                    $resultStatus
                );

                if (!$saveQuery->execute()) {
                    die("Result Save Error: " . $saveQuery->error);
                }
            }

            header(
                "Location: results.php?subject_id=" .
                $postSubjectId .
                "&exam_type=" .
                urlencode($examType) .
                "&saved=1"
            );
            exit;
  
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Results | College CMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >
</head>

<body>

<div class="admin-layout">

    <?php include "../includes/faculty-sidebar.php"; ?>

    <div class="main-area">

        <?php include "../includes/faculty-header.php"; ?>

        <main class="dashboard-content">

            <div class="page-heading">
                <div>
                    <h2>Results</h2>
                    <p>
                        Manage results for your assigned subjects.
                    </p>

                    <?php if (isset($_GET["saved"]) && $_GET["saved"] == 1): ?>

                        <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="alert">

                            <i class="bi bi-check-circle-fill me-2"></i>

                            <strong>Success!</strong>
                            Results saved successfully.

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>

                        </div>

                    <?php endif; ?>

                </div>
                
            </div>

            <div class="dashboard-card">

                <form method="GET">

                    <div class="row g-3 align-items-end">

                        <div class="col-md-8">

                            <label class="form-label">
                                Select Subject
                            </label>

                            <select
                                name="subject_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Choose assigned subject
                                </option>

                                <?php foreach ($classes as $class): ?>

                                    <option
                                        value="<?php echo (int)$class["subject_id"]; ?>"
                                    >
                                        <?php
                                        echo htmlspecialchars(
                                            $class["subject_name"]
                                            . " ("
                                            . $class["subject_code"]
                                            . ") - Semester "
                                            . $class["semester"]
                                        );
                                        ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-md-4">

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                <i class="bi bi-search me-1"></i>
                                Load Students
                            </button>

                        </div>

                    </div>

                </form>

                <?php if ($selectedClass): ?>

                <div class="mt-4">

                    <h5>
                        <?php echo htmlspecialchars($selectedClass["subject_name"]); ?>
                        - Semester <?php echo (int)$selectedClass["semester"]; ?>
                    </h5>

                    <?php if (count($students) > 0): ?>

                        <form method="POST">

                            <input
                                type="hidden"
                                name="subject_id"
                                value="<?php echo (int)$subjectId; ?>"
                            >

                            <input
                                type="hidden"
                                name="semester"
                                value="<?php echo (int)$selectedClass["semester"]; ?>"
                            >

                            <input
                                type="hidden"
                                name="subject_id"
                                value="<?php echo (int)$subjectId; ?>"
                            >

                            <div class="row g-3 mb-3">

                                <div class="col-md-6">

                                    <label class="form-label">
                                        Exam Type
                                    </label>

                                    <select
                                        name="exam_type"
                                        class="form-select"
                                        required
                                        onchange="window.location.href='results.php?subject_id=<?php echo (int)$subjectId; ?>&exam_type=' + encodeURIComponent(this.value)"
                                    >

                                        <option value="">Select Exam Type</option>

                                        <option
                                            value="Internal"
                                            <?php echo $selectedExamType === "Internal" ? "selected" : ""; ?>
                                        >
                                            Internal
                                        </option>

                                        <option
                                            value="Mid Term"
                                            <?php echo $selectedExamType === "Mid Term" ? "selected" : ""; ?>
                                        >
                                            Mid Term
                                        </option>

                                        <option
                                            value="Final"
                                            <?php echo $selectedExamType === "Final" ? "selected" : ""; ?>
                                        >
                                            Final
                                        </option>

                                        <option
                                            value="Practical"
                                            <?php echo $selectedExamType === "Practical" ? "selected" : ""; ?>
                                        >
                                            Practical
                                        </option>
                                    </select>

                                </div>

                                <div class="col-md-6">

                                    <label class="form-label">
                                        Total Marks
                                    </label>

                                    <input
                                        type="number"
                                        name="total_marks"
                                        class="form-control"
                                        min="1"
                                        step="0.01"
                                        value="<?php echo $savedTotalMarks !== "" 
                                            ? htmlspecialchars($savedTotalMarks) 
                                            : "100"; ?>"
                                        required
                                    >

                                </div>

                            </div>

                        <div class="table-responsive mt-3">

                            <table class="table align-middle">

                                <thead>
                                    <tr>
                                        <th>Admission No.</th>
                                        <th>Student Name</th>
                                        <th>Course</th>
                                        <th>Semester</th>
                                        <th>Marks</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($students as $student): ?>

                                        <tr>

                                            <td>
                                                <?php echo htmlspecialchars($student["admission_no"]); ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $student["first_name"] . " " .
                                                    $student["last_name"]
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($student["course"]); ?>
                                            </td>

                                            <td>
                                                <?php echo (int)$student["semester"]; ?>
                                            </td>

                                            <td>
                                                <input
                                                    type="number"
                                                    name="marks[<?php echo (int)$student["id"]; ?>]"
                                                    class="form-control"
                                                    min="0"
                                                    step="0.01"
                                                    value="<?php
                                                        echo isset($savedMarks[$student["id"]])
                                                            ? htmlspecialchars($savedMarks[$student["id"]])
                                                            : "";
                                                    ?>"
                                                    placeholder="Marks"
                                                    required
                                                >
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                            <div class="text-end mt-3">

                                <button
                                    type="submit"
                                    name="save_results"
                                    class="btn btn-primary"
                                >
                                    <i class="bi bi-check-circle me-1"></i>
                                    Save Results
                                </button>

                            </div>

</form>

                        </div>

                    <?php else: ?>

                        <div class="alert alert-warning mt-3">
                            No students found for this subject.
                        </div>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

            </div>

        </main>

    </div>

</div>

</body>

</html>