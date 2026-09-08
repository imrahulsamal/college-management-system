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

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: faculty.php");
    exit;
}

$id = (int) $_GET["id"];

$stmt = $conn->prepare("
    SELECT *
    FROM faculty
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: faculty.php");
    exit;
}

$faculty = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>View Faculty | College CMS</title>

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

                    <h2>Faculty Details</h2>

                    <p>
                        View complete faculty information.
                    </p>

                </div>

                <a
                    href="faculty.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Faculty
                </a>

            </div>


            <div class="dashboard-card">

                <div class="row g-4">

                    <div class="col-md-6">

                        <label class="text-muted small">
                            Employee Number
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["employee_no"]
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Full Name
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["first_name"] .
                                " " .
                                $faculty["last_name"]
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Email
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["email"] ?: "-"
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Phone
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["phone"] ?: "-"
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Gender
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["gender"] ?: "-"
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Date of Birth
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["date_of_birth"] ?: "-"
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Department
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["department"]
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Designation
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["designation"]
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Qualification
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["qualification"] ?: "-"
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Joining Date
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo htmlspecialchars(
                                $faculty["joining_date"] ?: "-"
                            );
                            ?>
                        </div>

                    </div>


                    <div class="col-md-6">

                        <label class="text-muted small">
                            Status
                        </label>

                        <div class="mt-2">

                            <?php if (
                                (int)$faculty["status"] === 1
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

                        </div>

                    </div>


                    <div class="col-12">

                        <label class="text-muted small">
                            Address
                        </label>

                        <div class="fw-semibold mt-1">
                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $faculty["address"] ?: "-"
                                )
                            );
                            ?>
                        </div>

                    </div>

                </div>


                <div class="mt-4">

                    <a
                        href="edit-faculty.php?id=<?php
                            echo (int)$faculty["id"];
                        ?>"
                        class="btn primary-action-btn"
                    >
                        <i class="bi bi-pencil"></i>
                        Edit Faculty
                    </a>

                    <a
                        href="faculty.php"
                        class="btn btn-light ms-2"
                    >
                        Back
                    </a>

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