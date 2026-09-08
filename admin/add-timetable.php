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

$error = "";

$departmentId = "";
$semester = "";
$subjectId = "";
$facultyId = "";
$dayOfWeek = "";
$startTime = "";
$endTime = "";
$roomNo = "";
$status = 1;


/* LOAD DEPARTMENTS */

$departments = $conn->query("
    SELECT id, department_name
    FROM departments
    WHERE status = 1
    ORDER BY department_name ASC
");


/* LOAD SUBJECTS */

$subjects = $conn->query("
    SELECT
        id,
        subject_code,
        subject_name,
        department_id,
        semester
    FROM subjects
    WHERE status = 1
    ORDER BY subject_name ASC
");


/* LOAD FACULTY */

$facultyList = $conn->query("
    SELECT
        id,
        first_name,
        last_name,
        department
    FROM faculty
    WHERE status = 1
    ORDER BY first_name ASC, last_name ASC
");


$validDays = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
];


/* SAVE TIMETABLE */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $departmentId = (int)($_POST["department_id"] ?? 0);
    $semester = (int)($_POST["semester"] ?? 0);
    $subjectId = (int)($_POST["subject_id"] ?? 0);
    $facultyId = (int)($_POST["faculty_id"] ?? 0);

    $dayOfWeek = trim($_POST["day_of_week"] ?? "");
    $startTime = trim($_POST["start_time"] ?? "");
    $endTime = trim($_POST["end_time"] ?? "");
    $roomNo = trim($_POST["room_no"] ?? "");

    $status = isset($_POST["status"]) ? 1 : 0;


    if (
        $departmentId <= 0 ||
        $semester <= 0 ||
        $subjectId <= 0 ||
        $facultyId <= 0 ||
        $dayOfWeek === "" ||
        $startTime === "" ||
        $endTime === ""
    ) {

        $error = "Please fill all required fields.";

    } elseif ($semester < 1 || $semester > 8) {

        $error = "Invalid semester selected.";

    } elseif (!in_array($dayOfWeek, $validDays, true)) {

        $error = "Invalid day selected.";

    } elseif ($startTime >= $endTime) {

        $error = "End time must be later than start time.";

    } else {


        /* VERIFY SUBJECT MATCHES DEPARTMENT + SEMESTER */

        $subjectCheck = $conn->prepare("
            SELECT id
            FROM subjects
            WHERE
                id = ?
                AND department_id = ?
                AND semester = ?
                AND status = 1
            LIMIT 1
        ");

        $subjectCheck->bind_param(
            "iii",
            $subjectId,
            $departmentId,
            $semester
        );

        $subjectCheck->execute();

        $subjectResult = $subjectCheck->get_result();

        $subjectValid = $subjectResult->num_rows === 1;

        $subjectCheck->close();


        if (!$subjectValid) {

            $error =
                "Selected subject does not match the department and semester.";

        } else {


            /*
             * CHECK FACULTY TIME CONFLICT
             */

            $facultyConflict = $conn->prepare("
                SELECT id
                FROM timetable
                WHERE
                    faculty_id = ?
                    AND day_of_week = ?
                    AND status = 1
                    AND start_time < ?
                    AND end_time > ?
                LIMIT 1
            ");

            $facultyConflict->bind_param(
                "isss",
                $facultyId,
                $dayOfWeek,
                $endTime,
                $startTime
            );

            $facultyConflict->execute();

            $facultyConflictResult =
                $facultyConflict->get_result();

            $hasFacultyConflict =
                $facultyConflictResult->num_rows > 0;

            $facultyConflict->close();


            if ($hasFacultyConflict) {

                $error =
                    "This faculty member already has another class during this time.";

            } else {


                /*
                 * CHECK SAME CLASS / SEMESTER TIME CONFLICT
                 */

                $classConflict = $conn->prepare("
                    SELECT id
                    FROM timetable
                    WHERE
                        department_id = ?
                        AND semester = ?
                        AND day_of_week = ?
                        AND status = 1
                        AND start_time < ?
                        AND end_time > ?
                    LIMIT 1
                ");

                $classConflict->bind_param(
                    "iisss",
                    $departmentId,
                    $semester,
                    $dayOfWeek,
                    $endTime,
                    $startTime
                );

                $classConflict->execute();

                $classConflictResult =
                    $classConflict->get_result();

                $hasClassConflict =
                    $classConflictResult->num_rows > 0;

                $classConflict->close();


                if ($hasClassConflict) {

                    $error =
                        "This department and semester already has a class during this time.";

                } else {


                    /*
                     * CHECK ROOM CONFLICT
                     */

                    $hasRoomConflict = false;

                    if ($roomNo !== "") {

                        $roomConflict = $conn->prepare("
                            SELECT id
                            FROM timetable
                            WHERE
                                room_no = ?
                                AND day_of_week = ?
                                AND status = 1
                                AND start_time < ?
                                AND end_time > ?
                            LIMIT 1
                        ");

                        $roomConflict->bind_param(
                            "ssss",
                            $roomNo,
                            $dayOfWeek,
                            $endTime,
                            $startTime
                        );

                        $roomConflict->execute();

                        $roomConflictResult =
                            $roomConflict->get_result();

                        $hasRoomConflict =
                            $roomConflictResult->num_rows > 0;

                        $roomConflict->close();
                    }


                    if ($hasRoomConflict) {

                        $error =
                            "This room is already occupied during the selected time.";

                    } else {


                        /* INSERT */

                        $stmt = $conn->prepare("
                            INSERT INTO timetable (
                                department_id,
                                semester,
                                subject_id,
                                faculty_id,
                                day_of_week,
                                start_time,
                                end_time,
                                room_no,
                                status
                            )
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");

                        if (!$stmt) {
                            die(
                                "Prepare failed: " .
                                $conn->error
                            );
                        }

                        $stmt->bind_param(
                            "iiiissssi",
                            $departmentId,
                            $semester,
                            $subjectId,
                            $facultyId,
                            $dayOfWeek,
                            $startTime,
                            $endTime,
                            $roomNo,
                            $status
                        );

                        if ($stmt->execute()) {

                            header(
                                "Location: timetable.php?added=1"
                            );
                            exit;

                        } else {

                            $error =
                                "Failed to add timetable entry.";

                        }

                        $stmt->close();
                    }
                }
            }
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

    <title>Add Schedule | College CMS</title>

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

                    <h2>Add Schedule</h2>

                    <p>
                        Create a new class timetable entry.
                    </p>

                </div>

                <a
                    href="timetable.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back
                </a>

            </div>


            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <i class="bi bi-exclamation-circle me-1"></i>

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <form method="POST">

                    <div class="row g-4">


                        <!-- DEPARTMENT -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Department
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="department_id"
                                id="department_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Department
                                </option>

                                <?php if ($departments): ?>

                                    <?php while (
                                        $department =
                                            $departments->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$department["id"]; ?>"
                                            <?php
                                            if (
                                                (string)$departmentId ===
                                                (string)$department["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $department["department_name"]
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
                                Semester
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="semester"
                                id="semester"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Semester
                                </option>

                                <?php for (
                                    $i = 1;
                                    $i <= 8;
                                    $i++
                                ): ?>

                                    <option
                                        value="<?php echo $i; ?>"
                                        <?php
                                        if (
                                            (string)$semester ===
                                            (string)$i
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


                        <!-- SUBJECT -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Subject
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="subject_id"
                                id="subject_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Subject
                                </option>

                                <?php if ($subjects): ?>

                                    <?php while (
                                        $subject =
                                            $subjects->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$subject["id"]; ?>"
                                            data-department="<?php echo (int)$subject["department_id"]; ?>"
                                            data-semester="<?php echo (int)$subject["semester"]; ?>"
                                            <?php
                                            if (
                                                (string)$subjectId ===
                                                (string)$subject["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $subject["subject_code"] .
                                                " - " .
                                                $subject["subject_name"]
                                            );
                                            ?>

                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                            <small class="text-muted">
                                Subject list will match selected department and semester.
                            </small>

                        </div>


                        <!-- FACULTY -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Faculty
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="faculty_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Faculty
                                </option>

                                <?php if ($facultyList): ?>

                                    <?php while (
                                        $faculty =
                                            $facultyList->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$faculty["id"]; ?>"
                                            <?php
                                            if (
                                                (string)$facultyId ===
                                                (string)$faculty["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $faculty["first_name"] .
                                                " " .
                                                $faculty["last_name"]
                                            );
                                            ?>

                                            <?php if (
                                                !empty(
                                                    $faculty["department"]
                                                )
                                            ): ?>

                                                -
                                                <?php
                                                echo htmlspecialchars(
                                                    $faculty["department"]
                                                );
                                                ?>

                                            <?php endif; ?>

                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <!-- DAY -->

                        <div class="col-md-4">

                            <label class="form-label">
                                Day
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="day_of_week"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Day
                                </option>

                                <?php foreach (
                                    $validDays as $day
                                ): ?>

                                    <option
                                        value="<?php echo $day; ?>"
                                        <?php
                                        if (
                                            $dayOfWeek === $day
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        <?php echo $day; ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- START TIME -->

                        <div class="col-md-4">

                            <label class="form-label">
                                Start Time
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="time"
                                name="start_time"
                                class="form-control"
                                value="<?php echo htmlspecialchars($startTime); ?>"
                                required
                            >

                        </div>


                        <!-- END TIME -->

                        <div class="col-md-4">

                            <label class="form-label">
                                End Time
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="time"
                                name="end_time"
                                class="form-control"
                                value="<?php echo htmlspecialchars($endTime); ?>"
                                required
                            >

                        </div>


                        <!-- ROOM -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Room Number
                            </label>

                            <input
                                type="text"
                                name="room_no"
                                class="form-control"
                                placeholder="Example: Room 101"
                                value="<?php echo htmlspecialchars($roomNo); ?>"
                            >

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-6">

                            <label class="form-label d-block">
                                Status
                            </label>

                            <div class="form-check form-switch mt-2">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="status"
                                    id="status"
                                    <?php
                                    if ($status === 1) {
                                        echo "checked";
                                    }
                                    ?>
                                >

                                <label
                                    class="form-check-label"
                                    for="status"
                                >
                                    Active Schedule
                                </label>

                            </div>

                        </div>


                        <div class="col-12">

                            <hr>

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-check-circle"></i>
                                Save Schedule
                            </button>

                            <a
                                href="timetable.php"
                                class="btn btn-outline-secondary ms-2"
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

const departmentSelect =
    document.getElementById("department_id");

const semesterSelect =
    document.getElementById("semester");

const subjectSelect =
    document.getElementById("subject_id");


function filterSubjects() {

    const department =
        departmentSelect.value;

    const semester =
        semesterSelect.value;


    Array.from(
        subjectSelect.options
    ).forEach(function(option) {

        if (option.value === "") {
            option.hidden = false;
            return;
        }

        const subjectDepartment =
            option.dataset.department;

        const subjectSemester =
            option.dataset.semester;

        const departmentMatches =
            department === "" ||
            subjectDepartment === department;

        const semesterMatches =
            semester === "" ||
            subjectSemester === semester;

        option.hidden =
            !(departmentMatches && semesterMatches);

    });


    const selectedOption =
        subjectSelect.options[
            subjectSelect.selectedIndex
        ];

    if (
        selectedOption &&
        selectedOption.value !== "" &&
        selectedOption.hidden
    ) {
        subjectSelect.value = "";
    }

}


departmentSelect.addEventListener(
    "change",
    filterSubjects
);

semesterSelect.addEventListener(
    "change",
    filterSubjects
);

filterSubjects();

</script>

</body>

</html>