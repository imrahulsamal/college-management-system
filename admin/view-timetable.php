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

$stmt = $conn->prepare("
    SELECT
        timetable.*,
        departments.department_name,
        departments.department_code,
        subjects.subject_code,
        subjects.subject_name,
        subjects.subject_type,
        subjects.credits,
        faculty.employee_no,
        faculty.first_name AS faculty_first_name,
        faculty.last_name AS faculty_last_name,
        faculty.designation
    FROM timetable

    INNER JOIN departments
        ON timetable.department_id = departments.id

    INNER JOIN subjects
        ON timetable.subject_id = subjects.id

    INNER JOIN faculty
        ON timetable.faculty_id = faculty.id

    WHERE timetable.id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

$timetable = $result->fetch_assoc();

$stmt->close();

if (!$timetable) {
    header("Location: timetable.php");
    exit;
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

    <title>View Schedule | College CMS</title>

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

                    <h2>Schedule Details</h2>

                    <p>
                        View complete timetable information.
                    </p>

                </div>

                <div>

                    <a
                        href="edit-timetable.php?id=<?php echo (int)$timetable["id"]; ?>"
                        class="btn btn-warning"
                    >
                        <i class="bi bi-pencil"></i>
                        Edit
                    </a>

                    <a
                        href="timetable.php"
                        class="btn btn-outline-secondary ms-2"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>

                </div>

            </div>


            <?php if (
                isset($_GET["updated"]) &&
                $_GET["updated"] === "1"
            ): ?>

                <div class="alert alert-success">

                    <i class="bi bi-check-circle me-1"></i>
                    Schedule updated successfully.

                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <div class="row g-4">


                    <div class="col-md-4">

                        <label class="form-label text-muted">
                            Day
                        </label>

                        <p class="fw-semibold mb-0">

                            <?php
                            echo htmlspecialchars(
                                $timetable["day_of_week"]
                            );
                            ?>

                        </p>

                    </div>


                    <div class="col-md-4">

                        <label class="form-label text-muted">
                            Start Time
                        </label>

                        <p class="fw-semibold mb-0">

                            <?php
                            echo date(
                                "h:i A",
                                strtotime(
                                    $timetable["start_time"]
                                )
                            );
                            ?>

                        </p>

                    </div>


                    <div class="col-md-4">

                        <label class="form-label text-muted">
                            End Time
                        </label>

                        <p class="fw-semibold mb-0">

                            <?php
                            echo date(
                                "h:i A",
                                strtotime(
                                    $timetable["end_time"]
                                )
                            );
                            ?>

                        </p>

                    </div>


                    <div class="col-12">
                        <hr>
                    </div>


                    <div class="col-md-6">

                        <label class="form-label text-muted">
                            Department
                        </label>

                        <p class="fw-semibold mb-0">

                            <?php
                            echo htmlspecialchars(
                                $timetable["department_name"]
                            );
                            ?>

                            <small class="text-muted">

                                (
                                <?php
                                echo htmlspecialchars(
                                    $timetable["department_code"]
                                );
                                ?>
                                )

                            </small>

                        </p>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label text-muted">
                            Semester
                        </label>

                        <p class="fw-semibold mb-0">

                            Semester
                            <?php
                            echo (int)$timetable["semester"];
                            ?>

                        </p>

                    </div>


                    <div class="col-12">
                        <hr>
                    </div>


                    <div class="col-md-6">

                        <label class="form-label text-muted">
                            Subject
                        </label>

                        <h5 class="mb-1">

                            <?php
                            echo htmlspecialchars(
                                $timetable["subject_name"]
                            );
                            ?>

                        </h5>

                        <p class="text-muted mb-0">

                            <?php
                            echo htmlspecialchars(
                                $timetable["subject_code"]
                            );
                            ?>

                            ·

                            <?php
                            echo htmlspecialchars(
                                $timetable["subject_type"]
                            );
                            ?>

                            ·

                            <?php
                            echo (int)$timetable["credits"];
                            ?>
                            Credits

                        </p>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label text-muted">
                            Faculty
                        </label>

                        <h5 class="mb-1">

                            <?php
                            echo htmlspecialchars(
                                $timetable["faculty_first_name"] .
                                " " .
                                $timetable["faculty_last_name"]
                            );
                            ?>

                        </h5>

                        <p class="text-muted mb-0">

                            <?php
                            echo htmlspecialchars(
                                $timetable["employee_no"]
                            );
                            ?>

                            <?php if (
                                !empty($timetable["designation"])
                            ): ?>

                                ·
                                <?php
                                echo htmlspecialchars(
                                    $timetable["designation"]
                                );
                                ?>

                            <?php endif; ?>

                        </p>

                    </div>


                    <div class="col-12">
                        <hr>
                    </div>


                    <div class="col-md-6">

                        <label class="form-label text-muted">
                            Room
                        </label>

                        <p class="fw-semibold mb-0">

                            <?php
                            echo htmlspecialchars(
                                $timetable["room_no"] ?: "-"
                            );
                            ?>

                        </p>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label text-muted">
                            Status
                        </label>

                        <p class="mb-0">

                            <?php if (
                                (int)$timetable["status"] === 1
                            ): ?>

                                <span
                                    class="student-status active-status"
                                >
                                    Active
                                </span>

                            <?php else: ?>

                                <span
                                    class="student-status inactive-status"
                                >
                                    Inactive
                                </span>

                            <?php endif; ?>

                        </p>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label text-muted">
                            Created At
                        </label>

                        <p class="mb-0">

                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime(
                                    $timetable["created_at"]
                                )
                            );
                            ?>

                        </p>

                    </div>


                </div>

            </div>

        </main>

    </div>

</div>


<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>

<script src="../assets/js/admin.js"></script>

</body>

</html>