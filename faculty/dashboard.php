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

$totalMySubjects = 0;
$totalTodayClasses = 0;
$totalAttendance = 0;
$totalResults = 0;

if ($facultyId) {

    $subjectQuery = $conn->prepare("
        SELECT COUNT(DISTINCT subject_id) AS total
        FROM timetable
        WHERE faculty_id = ?
        AND status = 1
    ");

    $subjectQuery->bind_param("i", $facultyId);
    $subjectQuery->execute();

    $subjectResult = $subjectQuery->get_result()->fetch_assoc();
    $totalMySubjects = $subjectResult["total"] ?? 0;


    $today = date("l");

    $nextClass = null;

$currentTime = date("H:i:s");

$nextClassQuery = $conn->prepare("
    SELECT
        timetable.*,
        subjects.subject_code,
        subjects.subject_name,
        departments.department_name
    FROM timetable
    INNER JOIN subjects
        ON timetable.subject_id = subjects.id
    INNER JOIN departments
        ON timetable.department_id = departments.id
    WHERE timetable.faculty_id = ?
    AND timetable.day_of_week = ?
    AND timetable.start_time > ?
    AND timetable.status = 1
    ORDER BY timetable.start_time ASC
    LIMIT 1
");

$nextClassQuery->bind_param(
    "iss",
    $facultyId,
    $today,
    $currentTime
);

$nextClassQuery->execute();

$nextClass = $nextClassQuery
    ->get_result()
    ->fetch_assoc();

    $classQuery = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM timetable
        WHERE faculty_id = ?
        AND day_of_week = ?
        AND status = 1
    ");

    $classQuery->bind_param("is", $facultyId, $today);
    $classQuery->execute();

    $classResult = $classQuery->get_result()->fetch_assoc();
    $totalTodayClasses = $classResult["total"] ?? 0;
}

if ($facultyId) {

    $attendanceQuery = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM attendance
        INNER JOIN timetable
            ON attendance.subject_id = timetable.subject_id
        WHERE timetable.faculty_id = ?
    ");

    $attendanceQuery->bind_param("i", $facultyId);
    $attendanceQuery->execute();

    $attendanceResult = $attendanceQuery->get_result()->fetch_assoc();
    $totalAttendance = $attendanceResult["total"] ?? 0;


    $resultQuery = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM results
        INNER JOIN timetable
            ON results.subject_id = timetable.subject_id
        WHERE timetable.faculty_id = ?
    ");

    $resultQuery->bind_param("i", $facultyId);
    $resultQuery->execute();

    $resultResult = $resultQuery->get_result()->fetch_assoc();
    $totalResults = $resultResult["total"] ?? 0;
}

$facultyNotices = $conn->query("
    SELECT *
    FROM notices
    WHERE status = 1
    AND audience IN ('All', 'Faculty')
    ORDER BY notice_date DESC, created_at DESC
    LIMIT 3
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

    <title>Faculty Dashboard | College CMS</title>

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
        href="../assets/css/admin.css?v=105"
    >

</head>

<body>

<div class="admin-layout">

    <?php include "../includes/faculty-sidebar.php"; ?>

    <div class="main-area">

        <?php include "../includes/faculty-header.php"; ?>

        <main class="dashboard-content faculty-dashboard">

            <div class="page-heading">

                <div>

                    <h2>Dashboard Overview</h2>

                </div>

            </div>

            <div class="row g-4 mb-4 faculty-stats-row">

                <div class="col-md-6 col-xl-3">
                    <div class="stat-card faculty-dashboard-card">
                        <div class="stat-icon students-icon">
                            <i class="bi bi-book-fill"></i>
                        </div>

                        <div class="stat-content">
                            <span>My Subjects</span>
                            <h3><?php echo $totalMySubjects; ?></h3>
                            <span>Assigned Subjects</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="stat-card faculty-dashboard-card">
                        <div class="stat-icon faculty-icon">
                            <i class="bi bi-calendar-check-fill"></i>
                        </div>

                        <div class="stat-content">
                            <span>Today's Classes</span>
                            <h3><?php echo $totalTodayClasses; ?></h3>
                            <span>Scheduled today</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="stat-card faculty-dashboard-card">
                        <div class="stat-icon department-icon">
                            <i class="bi bi-check2-square"></i>
                        </div>

                        <div class="stat-content">
                            <span>Attendance</span>
                            <h3><?php echo $totalAttendance; ?></h3>
                            <span>Total Records</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="stat-card faculty-dashboard-card">
                        <div class="stat-icon subject-icon">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>

                        <div class="stat-content">
                            <span>Results</span>
                            <h3><?php echo $totalResults; ?></h3>
                            <span>Published</span>
                        </div>
                    </div>
                </div>

            </div>


            <div class="row g-4 mb-4">

                <!-- Quick Actions -->
                <div class="col-lg-8">

                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">

                            <div class="d-flex align-items-center mb-4">

                                <div class="stat-icon students-icon me-3">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                </div>

                                <div>
                                    <h5 class="mb-1 fw-bold">
                                        Quick Actions
                                    </h5>

                                    <small class="text-muted">
                                        Manage your faculty activities
                                    </small>
                                </div>

                            </div>


                            <div class="row g-3">

                                <!-- Timetable -->
                                <div class="col-md-6">
                                    <a
                                        href="timetable.php"
                                        class="faculty-action-card"
                                    >
                                        <div class="faculty-action-icon action-blue">
                                            <i class="bi bi-calendar3"></i>
                                        </div>

                                        <div class="faculty-action-content">
                                            <h6>My Timetable</h6>
                                            <small>View your class schedule</small>
                                        </div>

                                        <i class="bi bi-chevron-right faculty-action-arrow"></i>
                                    </a>
                                </div>


                                <!-- Attendance -->
                                <div class="col-md-6">
                                    <a
                                        href="attendance.php"
                                        class="faculty-action-card"
                                    >
                                        <div class="faculty-action-icon action-green">
                                            <i class="bi bi-check-circle"></i>
                                        </div>

                                        <div class="faculty-action-content">
                                            <h6>Manage Attendance</h6>
                                            <small>Mark and view attendance</small>
                                        </div>

                                        <i class="bi bi-chevron-right faculty-action-arrow"></i>
                                    </a>
                                </div>


                                <!-- Results -->
                                <div class="col-md-6">
                                    <a
                                        href="results.php"
                                        class="faculty-action-card"
                                    >
                                        <div class="faculty-action-icon action-purple">
                                            <i class="bi bi-bar-chart-fill"></i>
                                        </div>

                                        <div class="faculty-action-content">
                                            <h6>Manage Results</h6>
                                            <small>Add and view exam results</small>
                                        </div>

                                        <i class="bi bi-chevron-right faculty-action-arrow"></i>
                                    </a>
                                </div>


                                <!-- Notices -->
                                <div class="col-md-6">
                                    <a
                                        href="notices.php"
                                        class="faculty-action-card"
                                    >
                                        <div class="faculty-action-icon action-red">
                                            <i class="bi bi-megaphone-fill"></i>
                                        </div>

                                        <div class="faculty-action-content">
                                            <h6>View Notices</h6>
                                            <small>Check latest announcements</small>
                                        </div>

                                        <i class="bi bi-chevron-right faculty-action-arrow"></i>
                                    </a>
                                </div>

                            </div>

                        </div>
                    </div>

                </div>


                <!-- Today's Schedule -->
                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body p-4">

                            <div class="d-flex align-items-center mb-4">

                                <div class="stat-icon faculty-icon me-3">
                                    <i class="bi bi-calendar-check-fill"></i>
                                </div>

                                <div>
                                    <h5 class="mb-1 fw-bold">
                                        Today's Schedule
                                    </h5>

                                    <span class="schedule-date-badge">
                                        <?php echo date("l, d M Y"); ?>
                                    </span>
                                </div>

                            </div>


                            <div
                                class="p-3 rounded-3 mb-3"
                                style="background:#f8fafc;"
                            >

                                <small class="text-muted d-block mb-1">
                                    Today's Classes
                                </small>

                                <h2 class="fw-bold mb-0">
                                    <?php echo (int)$totalTodayClasses; ?>
                                </h2>

                            </div>


                            <div class="d-flex justify-content-between mb-2">

                                <span class="text-muted">
                                    My Subjects
                                </span>

                                <strong>
                                    <?php echo (int)$totalMySubjects; ?>
                                </strong>

                            </div>


                            <div class="d-flex justify-content-between">

                                <span class="text-muted">
                                    Results Published
                                </span>

                                <strong>
                                    <?php echo (int)$totalResults; ?>
                                </strong>

                            </div>


                            <a
                                href="timetable.php"
                                class="btn btn-primary w-100 mt-4"
                            >
                                <i class="bi bi-calendar3 me-1"></i>
                                View Timetable
                                <i class="bi bi-chevron-right float-end mt-1"></i>
                            </a>

                        </div>

                    </div>

                </div>

            </div>

            <div class="row g-4 mt-1">

    <!-- Next Class -->
    <div class="col-lg-5">

        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">

                <div class="d-flex align-items-center mb-4">

                    <div
                        class="rounded-3 d-flex align-items-center justify-content-center me-3"
                        style="
                            width:48px;
                            height:48px;
                            background:#e8f8ef;
                            color:#16a34a;
                        "
                    >
                        <i class="bi bi-calendar2-check-fill fs-5"></i>
                    </div>

                    <div>
                        <h5 class="mb-1 fw-bold">Next Class</h5>
                        <small class="text-muted">
                            Your upcoming class today
                        </small>
                    </div>

                </div>


                <?php if ($nextClass): ?>

                    <h5 class="fw-bold mb-1">
                        <?php
                        echo htmlspecialchars(
                            $nextClass["subject_name"]
                        );
                        ?>
                    </h5>

                    <span class="badge bg-primary mb-3">
                        <?php
                        echo htmlspecialchars(
                            $nextClass["subject_code"]
                        );
                        ?>
                    </span>


                    <div class="border-top pt-3">

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">
                                <i class="bi bi-clock me-2"></i>
                                Time
                            </span>

                            <strong>
                                <?php
                                echo date(
                                    "h:i A",
                                    strtotime($nextClass["start_time"])
                                );
                                ?>

                                -

                                <?php
                                echo date(
                                    "h:i A",
                                    strtotime($nextClass["end_time"])
                                );
                                ?>
                            </strong>
                        </div>


                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">
                                <i class="bi bi-door-open me-2"></i>
                                Room
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $nextClass["room_no"] ?: "-"
                                );
                                ?>
                            </strong>
                        </div>


                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">
                                <i class="bi bi-building me-2"></i>
                                Department
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $nextClass["department_name"]
                                );
                                ?>
                            </strong>
                        </div>


                        <div class="d-flex justify-content-between">
                            <span class="text-muted">
                                <i class="bi bi-mortarboard me-2"></i>
                                Semester
                            </span>

                            <strong>
                                Semester
                                <?php
                                echo (int)$nextClass["semester"];
                                ?>
                            </strong>
                        </div>

                    </div>


                <?php else: ?>

                    <div class="text-center py-4">

                        <i
                            class="bi bi-calendar-check fs-1 text-success"
                        ></i>

                        <h6 class="mt-3 mb-1">
                            No More Classes Today
                        </h6>

                        <small class="text-muted">
                            Your schedule for today is complete.
                        </small>

                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>


    <!-- Faculty Notices -->
    <div class="col-lg-7">

        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">

                <div class="d-flex align-items-center mb-4">

                    <div
                        class="rounded-3 d-flex align-items-center justify-content-center me-3"
                        style="
                            width:48px;
                            height:48px;
                            background:#fff0f0;
                            color:#dc3545;
                        "
                    >
                        <i class="bi bi-megaphone-fill fs-5"></i>
                    </div>

                    <div>
                        <h5 class="mb-1 fw-bold">
                            Faculty Notices
                        </h5>

                        <small class="text-muted">
                            Latest college announcements
                        </small>
                    </div>

                </div>


                <?php if (
                    $facultyNotices &&
                    $facultyNotices->num_rows > 0
                ): ?>

                    <?php while (
                        $notice = $facultyNotices->fetch_assoc()
                    ): ?>

                        <div class="border rounded-3 p-3 mb-3 faculty-notice-item">

                            <div
                                class="d-flex justify-content-between align-items-start"
                            >

                                <div>

                                    <h6 class="fw-bold mb-1">
                                        <?php
                                        echo htmlspecialchars(
                                            $notice["title"]
                                        );
                                        ?>
                                    </h6>

                                    <small class="text-muted">
                                        <i class="bi bi-calendar3 me-1"></i>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $notice["notice_date"]
                                            )
                                        );
                                        ?>
                                    </small>

                                </div>


                                <span
                                    class="badge <?php
                                    echo $notice["audience"] === "Faculty"
                                        ? "bg-primary"
                                        : "bg-secondary";
                                    ?>"
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $notice["audience"]
                                    );
                                    ?>
                                </span>

                            </div>


                            <p class="text-muted mt-2 mb-0">
                                <?php
                                echo htmlspecialchars(
                                    mb_strimwidth(
                                        $notice["description"],
                                        0,
                                        120,
                                        "..."
                                    )
                                );
                                ?>
                            </p>

                        </div>

                    <?php endwhile; ?>


                    <a
                        href="notices.php"
                        class="btn btn-light border w-100"
                    >
                        <i class="bi bi-megaphone me-1"></i>
                        View All Notices
                    </a>


                <?php else: ?>

                    <div class="text-center py-4">

                        <i
                            class="bi bi-megaphone fs-1 text-muted"
                        ></i>

                        <p class="text-muted mt-2 mb-0">
                            No notices available.
                        </p>

                    </div>

                <?php endif; ?>

            </div>
        </div>

    </div>

</div>

        </main>

    </div>
    

</div>

</body>

</html>