<?php
session_start();

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["user_role"] !== "faculty"
) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$facultyId = $_SESSION["faculty_id"] ?? null;
$classes = [];

if ($facultyId) {
    $classQuery = $conn->prepare("
        SELECT
            timetable.id AS timetable_id,
            timetable.subject_id,
            timetable.department_id,
            timetable.semester,
            timetable.day_of_week,
            timetable.start_time,
            timetable.end_time,
            timetable.room_no,
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
            FIELD(
                timetable.day_of_week,
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday'
            ),
            timetable.start_time
    ");

    $classQuery->bind_param("i", $facultyId);
    $classQuery->execute();

    $classResult = $classQuery->get_result();

    while ($row = $classResult->fetch_assoc()) {
        $classes[] = $row;
    }
}

if (!$facultyId) {
    header("Location: ../index.php");
    exit;
}


$selectedClass = null;
$students = [];

$classId = isset($_GET["class_id"])
    ? (int) $_GET["class_id"]
    : 0;

if ($classId > 0 && $facultyId) {

    $selectedQuery = $conn->prepare("
        SELECT
            timetable.*,
            subjects.subject_name,
            subjects.subject_code,
            departments.department_name
        FROM timetable
        INNER JOIN subjects
            ON timetable.subject_id = subjects.id
        INNER JOIN departments
            ON timetable.department_id = departments.id
        WHERE timetable.id = ?
        AND timetable.faculty_id = ?
        AND timetable.status = 1
        LIMIT 1
    ");

    if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["save_attendance"])
) {
    $postClassId = (int) ($_POST["class_id"] ?? 0);
    $attendanceData = $_POST["attendance"] ?? [];

    // Verify class belongs to logged-in faculty
    $verifyQuery = $conn->prepare("
        SELECT subject_id
        FROM timetable
        WHERE id = ?
        AND faculty_id = ?
        AND status = 1
        LIMIT 1
    ");

    $verifyQuery->bind_param(
        "ii",
        $postClassId,
        $facultyId
    );

    $verifyQuery->execute();
    $verifyResult = $verifyQuery->get_result();

    if ($verifyResult->num_rows === 1) {

        $classData = $verifyResult->fetch_assoc();
        $subjectId = (int) $classData["subject_id"];
        $attendanceDate = date("Y-m-d");

        foreach ($attendanceData as $studentId => $status) {

            $studentId = (int) $studentId;

            if (!in_array(
                $status,
                ["Present", "Absent", "Leave"],
                true
            )) {
                continue;
            }

            $saveQuery = $conn->prepare("
                INSERT INTO attendance
                    (
                        student_id,
                        subject_id,
                        attendance_date,
                        status
                    )
                VALUES (?, ?, ?, ?)

                ON DUPLICATE KEY UPDATE
                    status = VALUES(status)
            ");

            $saveQuery->bind_param(
                "iiss",
                $studentId,
                $subjectId,
                $attendanceDate,
                $status
            );

            $saveQuery->execute();
        }

        header(
            "Location: attendance.php?class_id=" .
            $postClassId .
            "&saved=1"
        );
        exit;
    }
}

    $selectedQuery->bind_param(
        "ii",
        $classId,
        $facultyId
    );

    $selectedQuery->execute();

    $selectedResult = $selectedQuery->get_result();

    if ($selectedResult->num_rows === 1) {

        $selectedClass = $selectedResult->fetch_assoc();

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

            // Default status
            $student["attendance_status"] = "Present";

            // Check if attendance already exists for today
            $attendanceCheck = $conn->prepare("
                SELECT status
                FROM attendance
                WHERE student_id = ?
                AND subject_id = ?
                AND attendance_date = CURDATE()
                LIMIT 1
            ");

            $attendanceCheck->bind_param(
                "ii",
                $student["id"],
                $selectedClass["subject_id"]
            );

            $attendanceCheck->execute();

            $attendanceResult = $attendanceCheck->get_result();

            if ($attendanceResult->num_rows === 1) {
                $existingAttendance = $attendanceResult->fetch_assoc();
                $student["attendance_status"] = $existingAttendance["status"];
            }

            $students[] = $student;
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

    <title>Attendance | College CMS</title>

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
                    <h2>Attendance</h2>
                    <p>Manage attendance for your assigned classes.</p>

                    <?php if (isset($_GET["saved"]) && $_GET["saved"] == 1): ?>

                        <div class="alert alert-success alert-dismissible fade show mt-3 mb-0" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Success!</strong> Attendance saved successfully.

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
                                Select Class
                            </label>

                            <select
                                name="class_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Choose assigned class
                                </option>

                                <?php foreach ($classes as $class): ?>

                                    <option
                                        value="<?php echo (int)$class["timetable_id"]; ?>"
                                        <?php
                                        echo $classId === (int)$class["timetable_id"]
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        <?php
                                        echo htmlspecialchars(
                                            $class["subject_name"]
                                            . " (" . $class["subject_code"] . ")"
                                            . " | Semester " . $class["semester"]
                                            . " | " . $class["day_of_week"]
                                            . " | "
                                            . date(
                                                "h:i A",
                                                strtotime($class["start_time"])
                                            )
                                            . " - "
                                            . date(
                                                "h:i A",
                                                strtotime($class["end_time"])
                                            )
                                            . " | Room " .
                                            ($class["room_no"] ?: "-")
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

                <?php if ($selectedClass) { ?>

    <div class="mt-4">

        <!-- Class Summary -->
        <div class="card border-0 bg-light mb-4">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                    <div>

                        <span class="badge bg-primary mb-2">
                            <?php
                            echo htmlspecialchars(
                                $selectedClass["subject_code"]
                            );
                            ?>
                        </span>

                        <h4 class="fw-bold mb-1">
                            <?php
                            echo htmlspecialchars(
                                $selectedClass["subject_name"]
                            );
                            ?>
                        </h4>

                        <small class="text-muted">
                            <?php
                            echo htmlspecialchars(
                                $selectedClass["department_name"]
                            );
                            ?>
                            · Semester
                            <?php echo (int)$selectedClass["semester"]; ?>
                        </small>

                    </div>


                    <div class="text-md-end">

                        <div class="mb-1">

                            <i class="bi bi-calendar3 text-primary me-1"></i>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $selectedClass["day_of_week"]
                                );
                                ?>
                            </strong>

                        </div>

                        <small class="text-muted">

                            <?php
                            echo date(
                                "h:i A",
                                strtotime(
                                    $selectedClass["start_time"]
                                )
                            );
                            ?>

                            -

                            <?php
                            echo date(
                                "h:i A",
                                strtotime(
                                    $selectedClass["end_time"]
                                )
                            );
                            ?>

                        </small>

                    </div>

                </div>


                <hr>


                <div class="row g-3">

                    <div class="col-md-4">

                        <small class="text-muted d-block">
                            <i class="bi bi-door-open me-1"></i>
                            Room
                        </small>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $selectedClass["room_no"] ?: "-"
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted d-block">
                            <i class="bi bi-people me-1"></i>
                            Students
                        </small>

                        <strong>
                            <?php echo count($students); ?>
                        </strong>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted d-block">
                            <i class="bi bi-calendar-check me-1"></i>
                            Attendance Date
                        </small>

                        <strong>
                            <?php echo date("d M Y"); ?>
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        <!-- Attendance Table -->
        <?php if (count($students) > 0) { ?>

            <form method="POST">

                <input
                    type="hidden"
                    name="class_id"
                    value="<?php echo (int)$classId; ?>"
                >


                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-people-fill text-primary me-2"></i>
                            Student Attendance
                        </h5>

                        <small class="text-muted">
                            Mark attendance status for each student.
                        </small>

                    </div>


                    <button
                        type="button"
                        class="btn btn-outline-success btn-sm"
                        id="markAllPresent"
                    >
                        <i class="bi bi-check2-all me-1"></i>
                        Mark All Present
                    </button>

                </div>


                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                            <tr>
                                <th>#</th>
                                <th>Admission No.</th>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th style="width:220px;">
                                    Attendance
                                </th>
                            </tr>

                        </thead>


                        <tbody>

                        <?php
                        $sr = 1;

                        foreach ($students as $student) {
                        ?>

                            <tr>

                                <td>
                                    <?php echo $sr++; ?>
                                </td>


                                <td>

                                    <span class="badge bg-light text-dark border">

                                        <?php
                                        echo htmlspecialchars(
                                            $student["admission_no"]
                                        );
                                        ?>

                                    </span>

                                </td>


                                <td>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $student["first_name"] .
                                            " " .
                                            $student["last_name"]
                                        );
                                        ?>
                                    </strong>

                                    <small class="d-block text-muted">
                                        <?php
                                        echo htmlspecialchars(
                                            $student["email"] ?: "-"
                                        );
                                        ?>
                                    </small>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $student["course"]
                                    );
                                    ?>

                                    <small class="d-block text-muted">
                                        Semester
                                        <?php
                                        echo (int)$student["semester"];
                                        ?>
                                    </small>

                                </td>


                                <td>

                                    <select
                                        name="attendance[<?php echo (int)$student["id"]; ?>]"
                                        class="form-select attendance-status"
                                        required
                                    >

                                        <option
                                            value="Present"
                                            <?php echo ($student["attendance_status"] === "Present") ? "selected" : ""; ?>
                                        >
                                            Present
                                        </option>

                                        <option
                                            value="Absent"
                                            <?php echo ($student["attendance_status"] === "Absent") ? "selected" : ""; ?>
                                        >
                                            Absent
                                        </option>

                                        <option
                                            value="Leave"
                                            <?php echo ($student["attendance_status"] === "Leave") ? "selected" : ""; ?>
                                        >
                                            Leave
                                        </option>

                                    </select>

                                </td>

                            </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>


                <div class="text-end mt-3">

                    <button
                        type="submit"
                        name="save_attendance"
                        class="btn btn-primary px-4"
                    >
                        <i class="bi bi-check-circle me-1"></i>
                        Save Attendance
                    </button>

                </div>

            </form>


            <script>
                const markAllBtn =
                    document.getElementById("markAllPresent");

                if (markAllBtn) {

                    markAllBtn.addEventListener(
                        "click",
                        function () {

                            document
                                .querySelectorAll(
                                    ".attendance-status"
                                )
                                .forEach(function (select) {

                                    select.value = "Present";

                                });

                        }
                    );

                }
            </script>


        <?php } else { ?>

            <div class="alert alert-warning">

                <i class="bi bi-exclamation-triangle me-1"></i>

                No students found for this class.

            </div>

        <?php } ?>


    </div>

<?php } ?>

                    

                </div>

            </div>

        </main>

    </div>

</div>

</body>

</html>