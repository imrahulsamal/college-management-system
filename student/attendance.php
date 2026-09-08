<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "student"
) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$studentId = $_SESSION["student_id"] ?? 0;

if (!$studentId) {
    header("Location: ../logout.php");
    exit;
}


/* Attendance Records */

$stmt = $conn->prepare("
    SELECT
        attendance.*,
        subjects.subject_code,
        subjects.subject_name
    FROM attendance
    INNER JOIN subjects
        ON attendance.subject_id = subjects.id
    WHERE attendance.student_id = ?
    ORDER BY attendance.attendance_date DESC
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$attendance = $stmt->get_result();


/* Attendance Summary */

$summaryStmt = $conn->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Present') AS present,
        SUM(status = 'Absent') AS absent,
        SUM(status = 'Leave') AS leave_count
    FROM attendance
    WHERE student_id = ?
");

$summaryStmt->bind_param("i", $studentId);
$summaryStmt->execute();

$summary = $summaryStmt->get_result()->fetch_assoc();

$totalRecords = (int)($summary["total"] ?? 0);
$totalPresent = (int)($summary["present"] ?? 0);
$totalAbsent = (int)($summary["absent"] ?? 0);
$totalLeave = (int)($summary["leave_count"] ?? 0);

$attendancePercentage = $totalRecords > 0
    ? round(($totalPresent / $totalRecords) * 100, 1)
    : 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Attendance | College CMS</title>

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
        href="../assets/css/admin.css?v=8"
    >

</head>

<body class="student-panel">

<?php include "../includes/student-sidebar.php"; ?>

<div class="main-area">

    <?php include "../includes/student-header.php"; ?>

    <main class="content-area">

        <div class="mb-4">

            <h3>My Attendance</h3>

            <p class="text-muted mb-0">
                View your attendance records and summary.
            </p>

        </div>


        <!-- Summary Cards -->

        <div class="row g-4 mb-4">

            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon students-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>

                    <div class="stat-content">

                        <span>Total Records</span>

                        <h3>
                            <?php echo $totalRecords; ?>
                        </h3>

                        <p>Total attendance</p>

                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon faculty-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <div class="stat-content">

                        <span>Present</span>

                        <h3>
                            <?php echo $totalPresent; ?>
                        </h3>

                        <p>Present classes</p>

                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon department-icon">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>

                    <div class="stat-content">

                        <span>Absent</span>

                        <h3>
                            <?php echo $totalAbsent; ?>
                        </h3>

                        <p>Absent classes</p>

                    </div>

                </div>

            </div>


            <div class="col-lg-3 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon subject-icon">
                        <i class="bi bi-percent"></i>
                    </div>

                    <div class="stat-content">

                        <span>Attendance</span>

                        <h3>
                            <?php echo $attendancePercentage; ?>%
                        </h3>

                        <p>
                            Leave: <?php echo $totalLeave; ?>
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!-- Attendance History -->

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <h5 class="mb-4">

                    <i class="bi bi-list-check me-2"></i>

                    Attendance History

                </h5>


                <?php if ($attendance->num_rows > 0): ?>

                    <div class="table attendance-history">

                        <table class="table align-middle">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php $count = 1; ?>

                            <?php while (
                                $row = $attendance->fetch_assoc()
                            ): ?>

                                <tr>

                                    <td>
                                        <?php echo $count++; ?>
                                    </td>

                                    <td>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $row["attendance_date"]
                                            )
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $row["subject_name"]
                                            );
                                            ?>

                                        </strong>

                                        <small class="d-block text-muted">

                                            <?php
                                            echo htmlspecialchars(
                                                $row["subject_code"]
                                            );
                                            ?>

                                        </small>

                                    </td>

                                    <td>

                                        <?php if (
                                            $row["status"] === "Present"
                                        ): ?>

                                            <span class="badge bg-success">
                                                Present
                                            </span>

                                        <?php elseif (
                                            $row["status"] === "Absent"
                                        ): ?>

                                            <span class="badge bg-danger">
                                                Absent
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-warning text-dark">
                                                Leave
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["remarks"] ?: "-"
                                        );
                                        ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5 text-muted">

                        <i
                            class="bi bi-calendar-x"
                            style="font-size: 32px;"
                        ></i>

                        <p class="mt-3 mb-0">
                            No attendance records available.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

<script src="../assets/js/admin.js"></script>

</body>
</html>