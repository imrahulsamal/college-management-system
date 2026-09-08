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

if (!$facultyId) {
    header("Location: ../index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT *
    FROM faculty
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $facultyId);
$stmt->execute();

$faculty = $stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Faculty Profile | College CMS</title>

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
        href="../assets/css/admin.css?v=102"
    >
</head>

<body>

    <?php include "../includes/faculty-sidebar.php"; ?>

    <div class="main-area">

        <?php include "../includes/faculty-header.php"; ?>

        <div class="container-fluid p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h3 class="mb-1">My Profile</h3>
                    <p class="text-muted mb-0">
                        View your faculty information.
                    </p>
                </div>

                <a href="change-password.php"
                class="btn btn-warning text-white">
                    <i class="bi bi-shield-lock-fill me-1"></i>
                    Change Password
                </a>  

            </div>

            <?php if (isset($_GET["password_changed"])): ?>

                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Password changed successfully.

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>
                </div>

            <?php endif; ?>

            <div class="card border-0 shadow-sm faculty-profile-card">
                <div class="card-body p-4">

                    <div class="d-flex align-items-center gap-3 mb-4">

                        <div class="profile-avatar">
                            <?php
                            echo strtoupper(
                                substr($faculty["first_name"], 0, 1)
                            );
                            ?>
                        </div>

                        <div>
                            <h4 class="mb-1">
                                <?php
                                echo htmlspecialchars(
                                    $faculty["first_name"] . " " .
                                    $faculty["last_name"]
                                );
                                ?>
                            </h4>

                            <span class="badge bg-primary">
                                <?php
                                echo htmlspecialchars(
                                    $faculty["designation"]
                                );
                                ?>
                            </span>
                        </div>

                    </div>

                    <hr>

                    <div class="row g-3 mt-2">

                        <div class="col-md-6">
                            <div class="profile-info-box">
                                <div class="profile-info-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div>
                                    <small>Employee No.</small>
                                    <p>
                                        <?php echo htmlspecialchars($faculty["employee_no"]); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="profile-info-box">
                                <div class="profile-info-icon">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div>
                                    <small>Department</small>
                                    <p>
                                        <?php echo htmlspecialchars($faculty["department"]); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="profile-info-box">
                                <div class="profile-info-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div>
                                    <small>Email</small>
                                    <p>
                                        <?php echo htmlspecialchars($faculty["email"] ?? "-"); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="profile-info-box">
                                <div class="profile-info-icon">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <div>
                                    <small>Phone</small>
                                    <p>
                                        <?php echo htmlspecialchars($faculty["phone"] ?? "-"); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="profile-info-box">
                                <div class="profile-info-icon">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <small>Gender</small>
                                    <p>
                                        <?php echo htmlspecialchars($faculty["gender"] ?? "-"); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="profile-info-box">
                                <div class="profile-info-icon">
                                    <i class="bi bi-mortarboard"></i>
                                </div>
                                <div>
                                    <small>Qualification</small>
                                    <p>
                                        <?php echo htmlspecialchars($faculty["qualification"] ?? "-"); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="profile-info-box">
                                <div class="profile-info-icon">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div>
                                    <small>Joining Date</small>
                                    <p>
                                        <?php
                                        echo !empty($faculty["joining_date"])
                                            ? date(
                                                "d M Y",
                                                strtotime($faculty["joining_date"])
                                            )
                                            : "-";
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="profile-info-box">
                                <div class="profile-info-icon">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div>
                                    <small>Status</small>
                                    <p>
                                        <?php if ((int)$faculty["status"] === 1): ?>
                                            <span class="badge bg-success-subtle text-success">
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger">
                                                Inactive
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="profile-info-box">
                                <div class="profile-info-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div>
                                    <small>Address</small>
                                    <p>
                                        <?php echo htmlspecialchars($faculty["address"] ?? "-"); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script src="../assets/js/admin.js"></script>

</body>
</html>