<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["user_role"] !== "student"
) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$studentId = $_SESSION["student_id"] ?? 0;

/* Student details */
$stmt = $conn->prepare("
    SELECT *
    FROM students
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    header("Location: ../logout.php");
    exit;
}

/* Attendance count */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM attendance
    WHERE student_id = ?
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$attendanceData = $stmt->get_result()->fetch_assoc();
$totalAttendance = $attendanceData["total"] ?? 0;


/* Results count */
$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM results
    WHERE student_id = ?
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$resultData = $stmt->get_result()->fetch_assoc();
$totalResults = $resultData["total"] ?? 0;


/* Fees due */
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(due_amount), 0) AS total
    FROM fees
    WHERE student_id = ?
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$feeData = $stmt->get_result()->fetch_assoc();
$totalFeesDue = $feeData["total"] ?? 0;

/* Latest Notices for Students */
$stmt = $conn->prepare("
    SELECT id, title, description, notice_date, created_at
    FROM notices
    WHERE status = 1
    AND audience IN ('Students', 'All')
    ORDER BY notice_date DESC, id DESC
    LIMIT 4
");

$stmt->execute();

$latestNotices = $stmt->get_result();

/* Fee Summary */
$stmt = $conn->prepare("
    SELECT
        COALESCE(SUM(total_amount), 0) AS total_fees,
        COALESCE(SUM(paid_amount), 0) AS paid_fees,
        COALESCE(SUM(due_amount), 0) AS outstanding_fees
    FROM fees
    WHERE student_id = ?
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$feeSummary = $stmt->get_result()->fetch_assoc();

$totalFees = $feeSummary["total_fees"] ?? 0;
$paidFees = $feeSummary["paid_fees"] ?? 0;
$outstandingFees = $feeSummary["outstanding_fees"] ?? 0;

/* Upcoming Classes Today */

$semester = (int)$student["semester"];
$course = trim($student["course"]);
$today = date("l");

$todayClassStmt = $conn->prepare("
    SELECT
        timetable.*,
        subjects.subject_code,
        subjects.subject_name,
        CONCAT(
            faculty.first_name,
            ' ',
            faculty.last_name
        ) AS faculty_name

    FROM timetable

    INNER JOIN subjects
        ON timetable.subject_id = subjects.id

    INNER JOIN departments
        ON timetable.department_id = departments.id

    INNER JOIN faculty
        ON timetable.faculty_id = faculty.id

    WHERE timetable.status = 1
      AND timetable.semester = ?
      AND departments.department_name = ?
      AND timetable.day_of_week = ?

    ORDER BY timetable.start_time ASC
");

$todayClassStmt->bind_param(
    "iss",
    $semester,
    $course,
    $today
);

$todayClassStmt->execute();

$todayClasses = $todayClassStmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Student Dashboard | College CMS</title>

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
        href="../assets/css/admin.css?v=16"
    >

</head>

<body class="student-panel">

<?php include "../includes/student-sidebar.php"; ?>

<div class="main-area">

    <?php include "../includes/student-header.php"; ?>

    <main class="content-area">

        <div class="page-heading">

                <div>

                    <h2>Dashboard Overview</h2>
                    <p>Your own Controls</p>

                </div>

            </div>


        <div class="row g-4 student-stats-row">

            <!-- Course -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card student-dashboard-card">

                    <div class="stat-icon students-icon">
                        <i class="bi bi-book-fill"></i>
                    </div>

                    <div class="stat-content">
                        <span>Course</span>

                        <h3>
                            <?php echo htmlspecialchars($student["course"]); ?>
                        </h3>

                        <p>Your course</p>
                    </div>

                </div>
            </div>


            <!-- Semester -->
            <div class="col-xl-3 col-md-6">
                <div class="stat-card student-dashboard-card">

                    <div class="stat-icon faculty-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>

                    <div class="stat-content">
                        <span>Semester</span>

                        <h3>
                            <?php echo (int)$student["semester"]; ?>
                        </h3>

                        <p>Current semester</p>
                    </div>

                </div>
            </div>


            <!-- Attendance -->
            <div class="col-xl-3 col-md-6">
                <a href="attendance.php"
                class="text-decoration-none text-dark">

                    <div class="stat-card student-dashboard-card">

                        <div class="stat-icon department-icon">
                            <i class="bi bi-check2-square"></i>
                        </div>

                        <div class="stat-content">
                            <span>Attendance</span>

                            <h3><?php echo $totalAttendance; ?></h3>

                            <p>View attendance</p>
                        </div>

                    </div>

                </a>
            </div>


            <!-- Results -->
            <div class="col-xl-3 col-md-6">
                <a href="results.php"
                class="text-decoration-none text-dark">

                    <div class="stat-card student-dashboard-card">

                        <div class="stat-icon subject-icon">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>

                        <div class="stat-content">
                            <span>Results</span>

                            <h3><?php echo $totalResults; ?></h3>

                            <p>View results</p>
                        </div>

                    </div>

                </a>
            </div>

        </div>


       <div class="row g-4 mt-2">

    <!-- Quick Access -->
<div class="col-lg-8">

    <div class="card border-0 shadow-sm h-100">

        <div class="card-body p-4">

            <div class="d-flex align-items-center gap-3 mb-3">

                <div class="student-section-icon section-blue">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>

                <div>
                    <h5 class="mb-1">Quick Access</h5>
                    <small class="text-muted">
                        Access important student services
                    </small>
                </div>

            </div>

            <div class="row g-3">

                <!-- Timetable -->
                <div class="col-md-4">
                    <a href="timetable.php"
                       class="student-quick-action text-decoration-none">

                        <div class="student-quick-icon quick-blue">
                            <i class="bi bi-calendar3"></i>
                        </div>

                        <div class="student-quick-text">
                            <h6>My Timetable</h6>
                            <p>View class schedule</p>
                        </div>

                        <i class="bi bi-chevron-right student-quick-arrow"></i>
                    </a>
                </div>

                <!-- Attendance -->
                <div class="col-md-4">
                    <a href="attendance.php"
                       class="student-quick-action text-decoration-none">

                        <div class="student-quick-icon quick-green">
                            <i class="bi bi-check-circle"></i>
                        </div>

                        <div class="student-quick-text">
                            <h6>Attendance</h6>
                            <p>Check attendance</p>
                        </div>

                        <i class="bi bi-chevron-right student-quick-arrow"></i>
                    </a>
                </div>

                <!-- Results -->
                <div class="col-md-4">
                    <a href="results.php"
                       class="student-quick-action text-decoration-none">

                        <div class="student-quick-icon quick-purple">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>

                        <div class="student-quick-text">
                            <h6>Results</h6>
                            <p>View exam results</p>
                        </div>

                        <i class="bi bi-chevron-right student-quick-arrow"></i>
                    </a>
                </div>

                <!-- Fees -->
                <div class="col-md-4">
                    <a href="fees.php"
                       class="student-quick-action text-decoration-none">

                        <div class="student-quick-icon quick-orange">
                            <i class="bi bi-wallet2"></i>
                        </div>

                        <div class="student-quick-text">
                            <h6>Fees</h6>
                            <p>View / Pay fees</p>
                        </div>

                        <i class="bi bi-chevron-right student-quick-arrow"></i>
                    </a>
                </div>

                <!-- Notices -->
                <div class="col-md-4">
                    <a href="notices.php"
                       class="student-quick-action text-decoration-none">

                        <div class="student-quick-icon quick-blue">
                            <i class="bi bi-megaphone-fill"></i>
                        </div>

                        <div class="student-quick-text">
                            <h6>Notices</h6>
                            <p>Latest announcements</p>
                        </div>

                        <i class="bi bi-chevron-right student-quick-arrow"></i>
                    </a>
                </div>

                <!-- Profile -->
                <div class="col-md-4">
                    <a href="profile.php"
                       class="student-quick-action text-decoration-none">

                        <div class="student-quick-icon quick-cyan">
                            <i class="bi bi-person"></i>
                        </div>

                        <div class="student-quick-text">
                            <h6>My Profile</h6>
                            <p>Manage profile</p>
                        </div>

                        <i class="bi bi-chevron-right student-quick-arrow"></i>
                    </a>
                </div>

            </div>

        </div>

    </div>

</div>


    <!-- Latest Notices -->
<div class="col-lg-4">

    <div class="card border-0 shadow-sm h-100">

        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div class="d-flex align-items-center gap-3">

                    <div class="student-section-icon section-orange">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>

                    <h5 class="mb-0">Latest Notices</h5>

                </div>

                <a href="notices.php"
                class="text-decoration-none small">
                    View all
                </a>

            </div>

            <?php if ($latestNotices->num_rows > 0): ?>

                <?php
                $noticeColors = [
                    "primary",
                    "success",
                    "warning",
                    "danger"
                ];

                $noticeIndex = 0;
                ?>

                <?php while ($notice = $latestNotices->fetch_assoc()): ?>

                    <div class="student-notice-item">

                        <span class="student-notice-dot bg-<?php
                            echo $noticeColors[$noticeIndex % 4];
                        ?>"></span>

                        <div class="student-notice-content">

                            <h6>
                                <?php
                                echo htmlspecialchars($notice["title"]);
                                ?>
                            </h6>

                            <p>
                                <?php
                                echo htmlspecialchars(
                                    mb_strimwidth(
                                        $notice["description"],
                                        0,
                                        35,
                                        "..."
                                    )
                                );
                                ?>
                            </p>

                        </div>

                        <span class="student-notice-date">
                            <?php
                            echo date(
                                "d M",
                                strtotime($notice["notice_date"])
                            );
                            ?>
                        </span>

                    </div>

                    <?php $noticeIndex++; ?>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="text-center text-muted py-4">
                    <i class="bi bi-megaphone fs-3"></i>
                    <p class="mb-0 mt-2">
                        No notices available.
                    </p>
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<!-- Fee Summary -->
<div class="row g-4 mt-2">

    <div class="col-lg-8">

        <div class="card border-0 shadow-sm h-100 student-fee-summary">

            <div class="card-body p-4">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div class="d-flex align-items-center gap-3">

                        <div class="student-section-icon section-green">
                            <i class="bi bi-wallet2"></i>
                        </div>

                        <h5 class="mb-0">Fee Summary</h5>

                    </div>

                    <a href="fees.php"
                    class="text-decoration-none small">
                        View Details
                    </a>

                </div>

                <hr class="mt-0">

                <div class="row text-center">

                    <!-- Total Fees -->
                    <div class="col-md-4 student-fee-column">

                        <span class="text-muted">
                            Total Fees
                        </span>

                        <h3 class="mt-2 mb-0">
                            ₹<?php echo number_format($totalFees, 2); ?>
                        </h3>

                    </div>

                    <!-- Paid Fees -->
                    <div class="col-md-4 student-fee-column">

                        <span class="text-muted">
                            Paid Fees
                        </span>

                        <h3 class="mt-2 mb-0 text-success">
                            ₹<?php echo number_format($paidFees, 2); ?>
                        </h3>

                    </div>

                    <!-- Outstanding -->
                    <div class="col-md-4">

                        <span class="text-muted">
                            Outstanding
                        </span>

                        <h3 class="mt-2 mb-0 text-danger">
                            ₹<?php echo number_format($outstandingFees, 2); ?>
                        </h3>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Upcoming Classes Today -->
<div class="col-lg-4">

    <div class="card border-0 shadow-sm h-100 student-today-classes">

        <div class="card-body p-4">

           <div class="d-flex justify-content-between align-items-center mb-3">

                <div class="d-flex align-items-center gap-3">

                    <div class="student-section-icon section-purple">
                        <i class="bi bi-calendar-week-fill"></i>
                    </div>

                    <h5 class="mb-0">
                        Upcoming Classes Today
                    </h5>

                </div>

            </div>

            <?php if ($todayClasses->num_rows > 0): ?>

                <?php while ($class = $todayClasses->fetch_assoc()): ?>

                    <div class="student-class-item">

                        <div class="student-class-info">

                            <h6>
                                <?php
                                echo htmlspecialchars(
                                    $class["subject_name"]
                                );
                                ?>

                                <?php if (!empty($class["subject_code"])): ?>
                                    (<?php
                                    echo htmlspecialchars(
                                        $class["subject_code"]
                                    );
                                    ?>)
                                <?php endif; ?>
                            </h6>

                            <p>
                                <?php
                                echo htmlspecialchars(
                                    $class["faculty_name"]
                                );
                                ?>

                                <?php if (!empty($class["room_no"])): ?>
                                    · Room
                                    <?php
                                    echo htmlspecialchars(
                                        $class["room_no"]
                                    );
                                    ?>
                                <?php endif; ?>
                            </p>

                        </div>

                        <span class="student-class-time">
                            <?php
                            echo date(
                                "h:i A",
                                strtotime($class["start_time"])
                            );
                            ?>
                        </span>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="text-center text-muted py-4">

                    <i class="bi bi-calendar-check fs-3"></i>

                    <p class="mb-0 mt-2">
                        No classes scheduled today.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</div>

</div>

    </main>

</div>

<script src="../assets/js/admin.js"></script>

</body>
</html>