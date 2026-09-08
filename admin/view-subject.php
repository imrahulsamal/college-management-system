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

$subjectId = (int) ($_GET["id"] ?? 0);

if ($subjectId <= 0) {
    header("Location: subjects.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT
        subjects.*,
        departments.department_name,
        departments.department_code
    FROM subjects
    INNER JOIN departments
        ON subjects.department_id = departments.id
    WHERE subjects.id = ?
    LIMIT 1
");

$stmt->bind_param("i", $subjectId);
$stmt->execute();

$result = $stmt->get_result();
$subject = $result->fetch_assoc();

$stmt->close();

if (!$subject) {
    header("Location: subjects.php");
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

    <title>View Subject | College CMS</title>

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

                    <h2>Subject Details</h2>

                    <p>
                        View complete academic subject information.
                    </p>

                </div>

                <div>

                    <a
                        href="edit-subject.php?id=<?php echo (int)$subject["id"]; ?>"
                        class="btn primary-action-btn"
                    >
                        <i class="bi bi-pencil"></i>
                        Edit Subject
                    </a>

                    <a
                        href="subjects.php"
                        class="btn btn-outline-secondary ms-2"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="row g-4">

                    <div class="col-md-6">

                        <label class="text-muted small">
                            Subject Name
                        </label>

                        <h5 class="mt-1">
                            <?php
                            echo htmlspecialchars(
                                $subject["subject_name"]
                            );
                            ?>
                        </h5>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Subject Code
                        </label>

                        <h5 class="mt-1">
                            <?php
                            echo htmlspecialchars(
                                $subject["subject_code"]
                            );
                            ?>
                        </h5>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Department
                        </label>

                        <p class="mt-1 mb-0">

                            <?php
                            echo htmlspecialchars(
                                $subject["department_name"]
                            );
                            ?>

                            <span class="text-muted">
                                (
                                <?php
                                echo htmlspecialchars(
                                    $subject["department_code"]
                                );
                                ?>
                                )
                            </span>

                        </p>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Semester
                        </label>

                        <p class="mt-1 mb-0">
                            Semester <?php echo (int)$subject["semester"]; ?>
                        </p>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Credits
                        </label>

                        <p class="mt-1 mb-0">
                            <?php echo (int)$subject["credits"]; ?>
                        </p>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Subject Type
                        </label>

                        <p class="mt-1 mb-0">
                            <?php
                            echo htmlspecialchars(
                                $subject["subject_type"]
                            );
                            ?>
                        </p>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Status
                        </label>

                        <div class="mt-2">

                            <?php if (
                                (int)$subject["status"] === 1
                            ): ?>

                                <span class="student-status active-status">
                                    Active
                                </span>

                            <?php else: ?>

                                <span class="student-status inactive-status">
                                    Inactive
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Created At
                        </label>

                        <p class="mt-1 mb-0">

                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime($subject["created_at"])
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