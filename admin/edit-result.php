<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$resultId = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);

if ($resultId <= 0) {
    header("Location: results.php");
    exit;
}

$message = "";
$messageType = "";


/* LOAD ACTIVE STUDENTS */

$students = $conn->query("
    SELECT id, admission_no, first_name, last_name
    FROM students
    WHERE status = 1
    ORDER BY first_name ASC, last_name ASC
");


/* LOAD ACTIVE SUBJECTS */

$subjects = $conn->query("
    SELECT id, subject_code, subject_name, semester
    FROM subjects
    WHERE status = 1
    ORDER BY subject_name ASC
");


/* UPDATE RESULT */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int) ($_POST["student_id"] ?? 0);
    $subject_id = (int) ($_POST["subject_id"] ?? 0);
    $semester = (int) ($_POST["semester"] ?? 0);

    $exam_type = trim($_POST["exam_type"] ?? "");

    $total_marks = (float) ($_POST["total_marks"] ?? 0);
    $obtained_marks = (float) ($_POST["obtained_marks"] ?? 0);

    $remarks = trim($_POST["remarks"] ?? "");


    if (
        $student_id <= 0 ||
        $subject_id <= 0 ||
        $semester <= 0 ||
        $exam_type === "" ||
        $total_marks <= 0 ||
        $obtained_marks < 0
    ) {

        $message = "Please fill all required fields correctly.";
        $messageType = "danger";

    } elseif ($obtained_marks > $total_marks) {

        $message = "Obtained marks cannot be greater than total marks.";
        $messageType = "danger";

    } else {

        /* CALCULATE PERCENTAGE */

        $percentage =
            ($obtained_marks / $total_marks) * 100;


        /* CALCULATE GRADE */

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


        /* PASS / FAIL */

        if ($percentage >= 40) {

            $result_status = "Pass";

        } else {

            $result_status = "Fail";
        }


        $stmt = $conn->prepare("
            UPDATE results
            SET
                student_id = ?,
                subject_id = ?,
                semester = ?,
                exam_type = ?,
                total_marks = ?,
                obtained_marks = ?,
                grade = ?,
                result_status = ?,
                remarks = ?
            WHERE id = ?
        ");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "iiisddsssi",
            $student_id,
            $subject_id,
            $semester,
            $exam_type,
            $total_marks,
            $obtained_marks,
            $grade,
            $result_status,
            $remarks,
            $resultId
        );


        if ($stmt->execute()) {

            $message = "Result updated successfully.";
            $messageType = "success";

        } else {

            if ($stmt->errno === 1062) {

                $message =
                    "This student already has a result for this subject, semester and exam type.";

            } else {

                $message = "Could not update result.";
            }

            $messageType = "danger";
        }

        $stmt->close();
    }
}


/* LOAD CURRENT RESULT */

$stmt = $conn->prepare("
    SELECT *
    FROM results
    WHERE id = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $resultId);
$stmt->execute();

$queryResult = $stmt->get_result();
$resultData = $queryResult->fetch_assoc();

$stmt->close();

if (!$resultData) {
    header("Location: results.php");
    exit;
}


/* CURRENT PERCENTAGE */

$currentPercentage = 0;

if ((float)$resultData["total_marks"] > 0) {

    $currentPercentage =
        (
            (float)$resultData["obtained_marks"]
            /
            (float)$resultData["total_marks"]
        ) * 100;
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

    <title>Edit Result | College CMS</title>

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

    <?php include "../includes/admin-sidebar.php"; ?>

    <div class="main-area">

        <?php include "../includes/admin-header.php"; ?>

        <main class="dashboard-content">


            <div class="page-heading">

                <div>

                    <h2>Edit Result</h2>

                    <p>
                        Update student examination result.
                    </p>

                </div>

                <a
                    href="results.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Results
                </a>

            </div>


            <?php if ($message !== ""): ?>

                <div class="alert alert-<?php echo $messageType; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <form method="POST">

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo $resultId; ?>"
                    >

                    <div class="row g-4">


                        <!-- STUDENT -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Student *
                            </label>

                            <select
                                name="student_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Student
                                </option>

                                <?php if ($students): ?>

                                    <?php while (
                                        $student = $students->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$student["id"]; ?>"
                                            <?php
                                            if (
                                                (int)$resultData["student_id"]
                                                ===
                                                (int)$student["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $student["admission_no"]
                                                . " - "
                                                . $student["first_name"]
                                                . " "
                                                . $student["last_name"]
                                            );
                                            ?>

                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <!-- SUBJECT -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Subject *
                            </label>

                            <select
                                name="subject_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Subject
                                </option>

                                <?php if ($subjects): ?>

                                    <?php while (
                                        $subject = $subjects->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$subject["id"]; ?>"
                                            <?php
                                            if (
                                                (int)$resultData["subject_id"]
                                                ===
                                                (int)$subject["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $subject["subject_code"]
                                                . " - "
                                                . $subject["subject_name"]
                                                . " (Sem "
                                                . $subject["semester"]
                                                . ")"
                                            );
                                            ?>

                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <!-- SEMESTER -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Semester *
                            </label>

                            <select
                                name="semester"
                                class="form-select"
                                required
                            >

                                <?php for ($i = 1; $i <= 8; $i++): ?>

                                    <option
                                        value="<?php echo $i; ?>"
                                        <?php
                                        if (
                                            (int)$resultData["semester"]
                                            === $i
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Semester <?php echo $i; ?>
                                    </option>

                                <?php endfor; ?>

                            </select>

                        </div>


                        <!-- EXAM TYPE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Exam Type *
                            </label>

                            <select
                                name="exam_type"
                                class="form-select"
                                required
                            >

                                <?php

                                $examTypes = [
                                    "Internal",
                                    "Mid Term",
                                    "Final",
                                    "Practical"
                                ];

                                foreach ($examTypes as $type):

                                ?>

                                    <option
                                        value="<?php echo $type; ?>"
                                        <?php
                                        if (
                                            $resultData["exam_type"]
                                            === $type
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        <?php echo $type; ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- TOTAL MARKS -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Total Marks *
                            </label>

                            <input
                                type="number"
                                name="total_marks"
                                id="total_marks"
                                class="form-control"
                                min="1"
                                step="0.01"
                                value="<?php
                                    echo htmlspecialchars(
                                        $resultData["total_marks"]
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- OBTAINED MARKS -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Obtained Marks *
                            </label>

                            <input
                                type="number"
                                name="obtained_marks"
                                id="obtained_marks"
                                class="form-control"
                                min="0"
                                step="0.01"
                                value="<?php
                                    echo htmlspecialchars(
                                        $resultData["obtained_marks"]
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- PERCENTAGE -->

                        <div class="col-md-4">

                            <label class="form-label">
                                Percentage
                            </label>

                            <input
                                type="text"
                                id="percentage"
                                class="form-control"
                                value="<?php
                                    echo number_format(
                                        $currentPercentage,
                                        2
                                    );
                                ?>%"
                                readonly
                            >

                        </div>


                        <!-- GRADE -->

                        <div class="col-md-4">

                            <label class="form-label">
                                Grade
                            </label>

                            <input
                                type="text"
                                id="grade"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $resultData["grade"]
                                    );
                                ?>"
                                readonly
                            >

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-4">

                            <label class="form-label">
                                Result Status
                            </label>

                            <input
                                type="text"
                                id="result_status"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $resultData["result_status"]
                                    );
                                ?>"
                                readonly
                            >

                        </div>


                        <!-- REMARKS -->

                        <div class="col-12">

                            <label class="form-label">
                                Remarks
                            </label>

                            <textarea
                                name="remarks"
                                class="form-control"
                                rows="4"
                                placeholder="Optional remarks..."
                            ><?php
                                echo htmlspecialchars(
                                    $resultData["remarks"] ?? ""
                                );
                            ?></textarea>

                        </div>


                        <!-- BUTTONS -->

                        <div class="col-12">

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-check-circle"></i>
                                Update Result
                            </button>

                            <a
                                href="results.php"
                                class="btn btn-light ms-2"
                            >
                                Cancel
                            </a>

                        </div>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>


<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>


<script src="../assets/js/admin.js"></script>

<script>

const totalMarksInput =
    document.getElementById("total_marks");

const obtainedMarksInput =
    document.getElementById("obtained_marks");

const percentageInput =
    document.getElementById("percentage");

const gradeInput =
    document.getElementById("grade");

const statusInput =
    document.getElementById("result_status");


function calculateResult() {

    const total =
        parseFloat(totalMarksInput.value) || 0;

    const obtained =
        parseFloat(obtainedMarksInput.value) || 0;


    if (total <= 0) {

        percentageInput.value = "0.00%";
        gradeInput.value = "-";
        statusInput.value = "-";

        return;
    }


    const percentage =
        (obtained / total) * 100;


    percentageInput.value =
        percentage.toFixed(2) + "%";


    let grade = "F";


    if (percentage >= 90) {

        grade = "A+";

    } else if (percentage >= 80) {

        grade = "A";

    } else if (percentage >= 70) {

        grade = "B+";

    } else if (percentage >= 60) {

        grade = "B";

    } else if (percentage >= 50) {

        grade = "C";

    } else if (percentage >= 40) {

        grade = "D";

    } else {

        grade = "F";
    }


    gradeInput.value = grade;


    if (percentage >= 40) {

        statusInput.value = "Pass";

    } else {

        statusInput.value = "Fail";
    }
}


totalMarksInput.addEventListener(
    "input",
    calculateResult
);

obtainedMarksInput.addEventListener(
    "input",
    calculateResult
);

calculateResult();

</script>

</body>
</html>