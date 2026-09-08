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

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    header("Location: timetable.php");
    exit;
}


/* FETCH EXISTING ENTRY */

$stmt = $conn->prepare("
    SELECT *
    FROM timetable
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$timetable = $result->fetch_assoc();

$stmt->close();

if (!$timetable) {
    header("Location: timetable.php");
    exit;
}


$error = "";

$departmentId = (int)$timetable["department_id"];
$semester = (int)$timetable["semester"];
$subjectId = (int)$timetable["subject_id"];
$facultyId = (int)$timetable["faculty_id"];
$dayOfWeek = $timetable["day_of_week"];
$startTime = $timetable["start_time"];
$endTime = $timetable["end_time"];
$roomNo = $timetable["room_no"] ?? "";
$status = (int)$timetable["status"];


$validDays = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
];


/* UPDATE */

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


        /* SUBJECT VALIDATION */

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

        $subjectValid =
            $subjectCheck->get_result()->num_rows === 1;

        $subjectCheck->close();


        if (!$subjectValid) {

            $error =
                "Selected subject does not match the department and semester.";

        } else {


            /* FACULTY CONFLICT */

            $facultyConflict = $conn->prepare("
                SELECT id
                FROM timetable
                WHERE
                    faculty_id = ?
                    AND day_of_week = ?
                    AND id != ?
                    AND status = 1
                    AND start_time < ?
                    AND end_time > ?
                LIMIT 1
            ");

            $facultyConflict->bind_param(
                "isiss",
                $facultyId,
                $dayOfWeek,
                $id,
                $endTime,
                $startTime
            );

            $facultyConflict->execute();

            $hasFacultyConflict =
                $facultyConflict->get_result()->num_rows > 0;

            $facultyConflict->close();


            if ($hasFacultyConflict) {

                $error =
                    "This faculty member already has another class during this time.";

            } else {


                /* CLASS CONFLICT */

                $classConflict = $conn->prepare("
                    SELECT id
                    FROM timetable
                    WHERE
                        department_id = ?
                        AND semester = ?
                        AND day_of_week = ?
                        AND id != ?
                        AND status = 1
                        AND start_time < ?
                        AND end_time > ?
                    LIMIT 1
                ");

                $classConflict->bind_param(
                    "iisiss",
                    $departmentId,
                    $semester,
                    $dayOfWeek,
                    $id,
                    $endTime,
                    $startTime
                );

                $classConflict->execute();

                $hasClassConflict =
                    $classConflict->get_result()->num_rows > 0;

                $classConflict->close();


                if ($hasClassConflict) {

                    $error =
                        "This department and semester already has a class during this time.";

                } else {


                    /* ROOM CONFLICT */

                    $hasRoomConflict = false;

                    if ($roomNo !== "") {

                        $roomConflict = $conn->prepare("
                            SELECT id
                            FROM timetable
                            WHERE
                                room_no = ?
                                AND day_of_week = ?
                                AND id != ?
                                AND status = 1
                                AND start_time < ?
                                AND end_time > ?
                            LIMIT 1
                        ");

                        $roomConflict->bind_param(
                            "ssiss",
                            $roomNo,
                            $dayOfWeek,
                            $id,
                            $endTime,
                            $startTime
                        );

                        $roomConflict->execute();

                        $hasRoomConflict =
                            $roomConflict->get_result()->num_rows > 0;

                        $roomConflict->close();
                    }


                    if ($hasRoomConflict) {

                        $error =
                            "This room is already occupied during the selected time.";

                    } else {


                        /* UPDATE RECORD */

                        $updateStmt = $conn->prepare("
                            UPDATE timetable
                            SET
                                department_id = ?,
                                semester = ?,
                                subject_id = ?,
                                faculty_id = ?,
                                day_of_week = ?,
                                start_time = ?,
                                end_time = ?,
                                room_no = ?,
                                status = ?
                            WHERE id = ?
                        ");

                        $updateStmt->bind_param(
                            "iiiissssii",
                            $departmentId,
                            $semester,
                            $subjectId,
                            $facultyId,
                            $dayOfWeek,
                            $startTime,
                            $endTime,
                            $roomNo,
                            $status,
                            $id
                        );

                        if ($updateStmt->execute()) {

                            header(
                                "Location: view-timetable.php?id=" .
                                $id .
                                "&updated=1"
                            );
                            exit;

                        } else {

                            $error =
                                "Failed to update timetable entry.";

                        }

                        $updateStmt->close();
                    }
                }
            }
        }
    }
}


/* LOAD FORM DATA */

$departments = $conn->query("
    SELECT id, department_name
    FROM departments
    WHERE status = 1
    ORDER BY department_name ASC
");

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

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Schedule | College CMS</title>

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

                    <h2>Edit Schedule</h2>

                    <p>
                        Update timetable information.
                    </p>

                </div>

                <a
                    href="view-timetable.php?id=<?php echo $id; ?>"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back
                </a>

            </div>


            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <i class="bi bi-exclamation-circle me-1"></i>

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <form method="POST">

                    <div class="row g-4">


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

                                <?php while (
                                    $department = $departments->fetch_assoc()
                                ): ?>

                                    <option
                                        value="<?php echo (int)$department["id"]; ?>"
                                        <?php
                                        if (
                                            $departmentId ===
                                            (int)$department["id"]
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

                            </select>

                        </div>


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

                                <?php for ($i = 1; $i <= 8; $i++): ?>

                                    <option
                                        value="<?php echo $i; ?>"
                                        <?php
                                        if ($semester === $i) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Semester <?php echo $i; ?>
                                    </option>

                                <?php endfor; ?>

                            </select>

                        </div>


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

                                <?php while (
                                    $subject = $subjects->fetch_assoc()
                                ): ?>

                                    <option
                                        value="<?php echo (int)$subject["id"]; ?>"
                                        data-department="<?php echo (int)$subject["department_id"]; ?>"
                                        data-semester="<?php echo (int)$subject["semester"]; ?>"
                                        <?php
                                        if (
                                            $subjectId ===
                                            (int)$subject["id"]
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

                            </select>

                            <small class="text-muted">
                                Subject automatically filters by department and semester.
                            </small>

                        </div>


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

                                <?php while (
                                    $faculty = $facultyList->fetch_assoc()
                                ): ?>

                                    <option
                                        value="<?php echo (int)$faculty["id"]; ?>"
                                        <?php
                                        if (
                                            $facultyId ===
                                            (int)$faculty["id"]
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
                                            !empty($faculty["department"])
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

                            </select>

                        </div>


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

                                <?php foreach ($validDays as $day): ?>

                                    <option
                                        value="<?php echo $day; ?>"
                                        <?php
                                        if ($dayOfWeek === $day) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        <?php echo $day; ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Start Time
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="time"
                                name="start_time"
                                class="form-control"
                                value="<?php echo htmlspecialchars(substr($startTime, 0, 5)); ?>"
                                required
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                End Time
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="time"
                                name="end_time"
                                class="form-control"
                                value="<?php echo htmlspecialchars(substr($endTime, 0, 5)); ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Room Number
                            </label>

                            <input
                                type="text"
                                name="room_no"
                                class="form-control"
                                value="<?php echo htmlspecialchars($roomNo); ?>"
                            >

                        </div>


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
                                Update Schedule
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

        const departmentMatches =
            department === "" ||
            option.dataset.department === department;

        const semesterMatches =
            semester === "" ||
            option.dataset.semester === semester;

        option.hidden =
            !(departmentMatches && semesterMatches);

    });

    const selected =
        subjectSelect.options[
            subjectSelect.selectedIndex
        ];

    if (
        selected &&
        selected.value !== "" &&
        selected.hidden
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