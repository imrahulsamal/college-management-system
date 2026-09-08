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

// Admin Profile Information
$adminId = (int) $_SESSION["user_id"];

$adminQuery = $conn->prepare("
    SELECT name, email, created_at
    FROM users
    WHERE id = ?
    AND role = 'admin'
    LIMIT 1
");

$adminQuery->bind_param("i", $adminId);
$adminQuery->execute();

$adminResult = $adminQuery->get_result();
$adminData = $adminResult->fetch_assoc();

$memberSince = !empty($adminData["created_at"])
    ? date("d M Y", strtotime($adminData["created_at"]))
    : "-";

$lastLogin = !empty($_SESSION["last_login"])
    ? date("d M Y, h:i A", strtotime($_SESSION["last_login"]))
    : "First Login";

// Total Students
$studentResult = $conn->query("SELECT COUNT(*) AS total FROM students");
$studentData = $studentResult->fetch_assoc();
$totalStudents = $studentData["total"];

// Total Faculty
$facultyResult = $conn->query("SELECT COUNT(*) AS total FROM faculty");
$facultyData = $facultyResult->fetch_assoc();
$totalFaculty = $facultyData["total"];

// Total Departments
$departmentResult = $conn->query("SELECT COUNT(*) AS total FROM departments");
$departmentData = $departmentResult->fetch_assoc();
$totalDepartments = $departmentData["total"];

// Total Subjects
$subjectResult = $conn->query("SELECT COUNT(*) AS total FROM subjects");
$subjectData = $subjectResult->fetch_assoc();
$totalSubjects = $subjectData["total"];

// Total Results
$resultCountQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM results
");

$resultCountData = $resultCountQuery->fetch_assoc();
$totalResults = $resultCountData["total"];

// Total Attendance Records
$attendanceCountQuery = $conn->query("
    SELECT COUNT(*) AS total
    FROM attendance
");

$attendanceCountData = $attendanceCountQuery->fetch_assoc();
$totalAttendance = $attendanceCountData["total"];


// Total Fees Collected
$feesCollectedQuery = $conn->query("
    SELECT COALESCE(SUM(paid_amount), 0) AS total
    FROM fees
");

$feesCollectedData = $feesCollectedQuery->fetch_assoc();
$totalFeesCollected = $feesCollectedData["total"];


// Total Fees Due
$feesDueQuery = $conn->query("
    SELECT COALESCE(SUM(due_amount), 0) AS total
    FROM fees
");

$feesDueData = $feesDueQuery->fetch_assoc();
$totalFeesDue = $feesDueData["total"];

// Recent Activities
$recentActivities = $conn->query("
    SELECT *
    FROM (
        SELECT
            'Student' AS activity_type,
            CONCAT(first_name, ' ', last_name) AS activity_name,
            'New student added' AS activity_text,
            created_at
        FROM students

        UNION ALL

        SELECT
            'Faculty' AS activity_type,
            CONCAT(first_name, ' ', last_name) AS activity_name,
            'New faculty added' AS activity_text,
            created_at
        FROM faculty

        UNION ALL

        SELECT
            'Result' AS activity_type,
            CONCAT(
                students.first_name,
                ' ',
                students.last_name
            ) AS activity_name,
            'Result added' AS activity_text,
            results.created_at
        FROM results
        INNER JOIN students
            ON results.student_id = students.id

        UNION ALL

        SELECT
            'Fee' AS activity_type,
            CONCAT(
                students.first_name,
                ' ',
                students.last_name
            ) AS activity_name,
            CONCAT(
                'Fee payment ₹',
                FORMAT(fees.paid_amount, 2)
            ) AS activity_text,
            fees.created_at
        FROM fees
        INNER JOIN students
            ON fees.student_id = students.id

        UNION ALL

        SELECT
            'Notice' AS activity_type,
            title AS activity_name,
            'New notice published' AS activity_text,
            created_at
        FROM notices

    ) AS activities

    ORDER BY created_at DESC

    LIMIT 3
");

// Upcoming Events
$upcomingEvents = $conn->query("
    SELECT *
    FROM events
    WHERE status = 1
    AND event_date >= CURDATE()
    ORDER BY event_date ASC, event_time ASC
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

    <title>Admin Dashboard</title>

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
        href="../assets/css/admin.css?v=122"
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
                    <h2>Dashboard Overview</h2>

                    <p>
                        Here's what's happening in your college today.
                    </p>
                </div>

                <a href="add-student.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    Add Student
                </a>

            </div>

            <!-- STATS -->

            <div class="row g-4">

                <div class="col-xl-3 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon students-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>

                        <div class="stat-content">
                            <span>Total Students</span>
                            <h3><?php echo htmlspecialchars($totalStudents); ?></h3>
                            <small>
                                <i class="bi bi-arrow-up"></i>
                                College students
                            </small>
                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon faculty-icon">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>

                        <div class="stat-content">
                            <span>Total Faculty</span>
                            <h3><?php echo $totalFaculty; ?></h3>
                            <small>Teaching staff</small>
                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon department-icon">
                            <i class="bi bi-building"></i>
                        </div>

                        <div class="stat-content">
                            <span>Departments</span>
                            <h3><?php echo $totalDepartments; ?></h3>
                            <small>Academic departments</small>
                        </div>

                    </div>

                </div>

                <div class="col-xl-3 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon subject-icon">
                            <i class="bi bi-journal-bookmark-fill"></i>
                        </div>

                        <div class="stat-content">
                            <span>Subjects</span>
                            <h3><?php echo $totalSubjects; ?></h3>
                            <small>Active subjects</small>
                        </div>

                    </div>

                </div>

            </div>

            <!-- MAIN GRID --> <!--Quick Actions-->

            <div class="row g-4 mt-1">

                <div class="col-xl-8 d-flex">

                    <div class="dashboard-card quick-actions-wrapper w-100 h-100">

                        <div class="card-heading">

                            <div class="d-flex align-items-center gap-3">

                                <div class="dashboard-heading-icon heading-blue">
                                    <i class="bi bi-lightning-charge-fill"></i>
                                </div>

                                <div>
                                    <h5>Quick Actions</h5>
                                    <p>Frequently used management options</p>
                                </div>

                            </div>

                        </div>

                        <div class="admin-action-grid">

                            <a href="add-student.php" class="admin-action-card">
                                <div class="admin-action-icon action-blue">
                                    <i class="bi bi-person-plus-fill"></i>
                                </div>

                                <div class="admin-action-content">
                                    <h6>Add Student</h6>
                                    <small>Register a new student</small>
                                </div>

                                <i class="bi bi-chevron-right admin-action-arrow"></i>
                            </a>


                            <a href="add-faculty.php" class="admin-action-card">
                                <div class="admin-action-icon action-green">
                                    <i class="bi bi-person-badge-fill"></i>
                                </div>

                                <div class="admin-action-content">
                                    <h6>Add Faculty</h6>
                                    <small>Register faculty member</small>
                                </div>

                                <i class="bi bi-chevron-right admin-action-arrow"></i>
                            </a>


                            <a href="attendance.php" class="admin-action-card">
                                <div class="admin-action-icon action-orange">
                                    <i class="bi bi-calendar-check"></i>
                                </div>

                                <div class="admin-action-content">
                                    <h6>Attendance</h6>
                                    <small>View attendance records</small>
                                </div>

                                <i class="bi bi-chevron-right admin-action-arrow"></i>
                            </a>


                            <a href="results.php" class="admin-action-card">
                                <div class="admin-action-icon action-purple">
                                    <i class="bi bi-bar-chart-fill"></i>
                                </div>

                                <div class="admin-action-content">
                                    <h6>Results</h6>
                                    <small>Manage exam results</small>
                                </div>

                                <i class="bi bi-chevron-right admin-action-arrow"></i>
                            </a>


                            <a href="fees.php" class="admin-action-card">
                                <div class="admin-action-icon action-green">
                                    <i class="bi bi-cash-stack"></i>
                                </div>

                                <div class="admin-action-content">
                                    <h6>Fees</h6>
                                    <small>Manage student fees</small>
                                </div>

                                <i class="bi bi-chevron-right admin-action-arrow"></i>
                            </a>


                            <a href="notices.php" class="admin-action-card">
                                <div class="admin-action-icon action-red">
                                    <i class="bi bi-megaphone-fill"></i>
                                </div>

                                <div class="admin-action-content">
                                    <h6>Notices</h6>
                                    <small>Manage announcements</small>
                                </div>

                                <i class="bi bi-chevron-right admin-action-arrow"></i>
                            </a>

                        </div>

                    </div>

                </div>

                <!-- ADMIN PROFILE -->
                <div class="col-xl-4 d-flex">

                    <div class="dashboard-card w-100 h-100 admin-profile-wrapper">

                        <div class="card-heading">

                            <div class="d-flex align-items-center gap-3">

                                <div class="dashboard-heading-icon heading-blue">
                                    <i class="bi bi-person-fill"></i>
                                </div>

                                <div>
                                    <h5>Admin Profile</h5>
                                    <p>Account information</p>
                                </div>

                            </div>

                        </div>

                        <div class="admin-profile-card">

                            <div class="large-avatar">
                                <?php
                                echo strtoupper(
                                    substr($_SESSION["user_name"], 0, 1)
                                );
                                ?>
                            </div>

                            <h5>
                                <?php
                                echo htmlspecialchars(
                                    $_SESSION["user_name"]
                                );
                                ?>
                            </h5>

                            <p>
                                <?php
                                echo htmlspecialchars(
                                    $_SESSION["user_email"]
                                );
                                ?>
                            </p>

                            <span class="admin-badge">
                                Administrator
                            </span>

                            <hr class="admin-profile-divider">

<div class="admin-profile-meta">

    <div class="profile-meta-item">
        <i class="bi bi-calendar3"></i>

        <div>
            <span>Member Since</span>
            <strong>
                <?php echo htmlspecialchars($memberSince); ?>
            </strong>
        </div>
    </div>

    <div class="profile-meta-item">
        <i class="bi bi-clock"></i>

        <div>
            <span>Last Login</span>
            <strong>
                <?php echo htmlspecialchars($lastLogin); ?>
            </strong>
        </div>
    </div>

</div>

                        </div>

                    </div>

                </div>

            </div>

            <div class="row g-4 mt-1">

                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">

                        <div class="stat-icon students-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>

                        <div class="stat-content">
                            <span>Attendance Records</span>

                            <h3>
                                <?php echo $totalAttendance; ?>
                            </h3>

                            <small>
                                Total attendance entries
                            </small>
                        </div>

                    </div>
                </div>


                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">

                        <div class="stat-icon faculty-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>

                        <div class="stat-content">
                            <span>Fees Collected</span>

                            <h3>
                                ₹<?php echo number_format(
                                    (float)$totalFeesCollected,
                                    2
                                ); ?>
                            </h3>

                            <small>
                                Total amount received
                            </small>
                        </div>

                    </div>
                </div>


                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">

                        <div class="stat-icon department-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>

                        <div class="stat-content">
                            <span>Fees Due</span>

                            <h3>
                                ₹<?php echo number_format(
                                    (float)$totalFeesDue,
                                    2
                                ); ?>
                            </h3>

                            <small>
                                Pending student fees
                            </small>
                        </div>

                    </div>
                </div>


                <div class="col-xl-3 col-md-6">
                    <div class="stat-card">

                        <div class="stat-icon subject-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>

                        <div class="stat-content">
                            <span>Results</span>

                            <h3>
                                <?php echo $totalResults; ?>
                            </h3>

                            <small>
                                Result records
                            </small>
                        </div>

                    </div>
                </div>

            </div>

            <!-- RECENT ACTIVITY -->

            <div class="row g-4 mt-1 align-items-stretch">

            <div class="col-lg-4 d-flex">

                <div class="dashboard-card w-100 h-100">

                <div class="card-heading">

                    <div>
                        <h5>Recent Activity</h5>
                        <p>
                            Latest actions inside the system
                        </p>
                    </div>

                </div>

                <?php if (
                        $recentActivities &&
                        $recentActivities->num_rows > 0
                    ): ?>

                        <div class="recent-activity-list">

                            <?php while (
                                $activity = $recentActivities->fetch_assoc()
                            ): ?>

                                <div class="recent-activity-item">

                                    <div class="activity-icon">

                                        <?php if (
                                            $activity["activity_type"] === "Student"
                                        ): ?>

                                            <i class="bi bi-person-plus-fill"></i>

                                        <?php elseif (
                                            $activity["activity_type"] === "Faculty"
                                        ): ?>

                                            <i class="bi bi-person-badge-fill"></i>

                                        <?php elseif (
                                            $activity["activity_type"] === "Result"
                                        ): ?>

                                            <i class="bi bi-bar-chart-fill"></i>

                                        <?php elseif (
                                            $activity["activity_type"] === "Fee"
                                        ): ?>

                                            <i class="bi bi-cash-coin"></i>

                                        <?php else: ?>

                                            <i class="bi bi-megaphone-fill"></i>

                                        <?php endif; ?>

                                    </div>


                                    <div class="activity-content">

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $activity["activity_name"]
                                            );
                                            ?>
                                        </strong>

                                        <span>
                                            <?php
                                            echo htmlspecialchars(
                                                $activity["activity_text"]
                                            );
                                            ?>
                                        </span>

                                    </div>


                                    <div class="activity-time">

                                        <?php
                                        echo date(
                                            "d M Y, h:i A",
                                            strtotime(
                                                $activity["created_at"]
                                            )
                                        );
                                        ?>

                                    </div>

                                </div>

                                    <?php endwhile; ?>

                                </div>

                            <?php else: ?>

                                <div class="text-center py-4 text-muted">
                                    No recent activity found.
                                </div>

                            <?php endif; ?>

                    </div>

            </div>

            <!-- UPCOMING EVENTS -->

            <div class="col-lg-4 d-flex">

                <div class="dashboard-card w-100 h-100">

                    <div class="card-heading d-flex justify-content-between align-items-start">

                        <div>
                            <h5>Upcoming Events</h5>
                            <p>Next important dates</p>
                        </div>

                        <a href="events.php" class="small text-decoration-none">
                            View All
                        </a>

                    </div>


                    <?php if (
                                $upcomingEvents &&
                                $upcomingEvents->num_rows > 0
                            ): ?>

                                <?php while (
                                    $event = $upcomingEvents->fetch_assoc()
                                ): ?>

                                    <a
                                        href="view-event.php?id=<?php echo (int)$event["id"]; ?>"
                                        class="upcoming-event-item upcoming-event-link"
                                    >

                                        <div
                                            class="event-icon <?php
                                            echo $event["event_type"] === "Meeting"
                                                ? "event-orange"
                                                : "event-blue";
                                            ?>"
                                        >
                                            <?php if ($event["event_type"] === "Meeting"): ?>

                                                <i class="bi bi-calendar-event-fill"></i>

                                            <?php elseif ($event["event_type"] === "Exam"): ?>

                                                <i class="bi bi-journal-text"></i>

                                            <?php elseif ($event["event_type"] === "Holiday"): ?>

                                                <i class="bi bi-calendar2-week"></i>

                                            <?php else: ?>

                                                <i class="bi bi-calendar-check-fill"></i>

                                            <?php endif; ?>
                                        </div>


                                        <div class="event-content">

                                            <strong>
                                                <?php echo htmlspecialchars($event["title"]); ?>
                                            </strong>

                                            <span>
                                                <?php
                                                echo htmlspecialchars(
                                                    $event["description"] ?? ""
                                                );
                                                ?>
                                            </span>

                                        </div>


                                        <div class="event-date">

                                            <strong>
                                                <?php
                                                echo date(
                                                    "d M Y",
                                                    strtotime($event["event_date"])
                                                );
                                                ?>
                                            </strong>

                                            <span>
                                                <?php
                                                echo !empty($event["event_time"])
                                                    ? date(
                                                        "h:i A",
                                                        strtotime($event["event_time"])
                                                    )
                                                    : "-";
                                                ?>
                                            </span>

                                        </div>

                                    </a>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <div class="text-center py-4 text-muted">
                                    No upcoming events.
                                </div>

                            <?php endif; ?>

                        </div>

                

            </div>

            <!-- SYSTEM STATUS -->

            <div class="col-lg-4 d-flex">

                <div class="dashboard-card w-100 h-100">

                    <div class="card-heading">

                        <div class="d-flex align-items-center gap-3">

                            <div class="system-status-heading-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>

                            <div>
                                <h5 class="mb-1">System Status</h5>
                                <p class="mb-0">Current system health</p>
                            </div>

                        </div>

                    </div>


                    <div class="system-status-list">

                        <!-- Database -->
                        <div class="system-status-item">

                            <div class="system-status-left">
                                <div class="status-small-icon">
                                    <i class="bi bi-database-fill-check"></i>
                                </div>

                                <strong>Database</strong>
                            </div>

                            <div class="status-online">
                                <span class="status-dot"></span>
                                Online
                            </div>

                        </div>


                        <!-- Web Server -->
                        <div class="system-status-item">

                            <div class="system-status-left">
                                <div class="status-small-icon">
                                    <i class="bi bi-hdd-network-fill"></i>
                                </div>

                                <strong>Web Server</strong>
                            </div>

                            <div class="status-online">
                                <span class="status-dot"></span>
                                Online
                            </div>

                        </div>


                        <!-- Last Backup -->
                        <div class="system-status-item">

                            <div class="system-status-left">
                                <div class="status-small-icon">
                                    <i class="bi bi-cloud-check-fill"></i>
                                </div>

                                <strong>Last Backup</strong>
                            </div>

                            <div class="status-date">
                                <span class="status-dot"></span>
                                <?php echo date("d M Y"); ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script src="../assets/js/admin.js"></script>

</body>
</html>