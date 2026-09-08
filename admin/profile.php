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

$adminId = (int) $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT
        id,
        name,
        email,
        phone,
        date_of_birth,
        gender,
        address,
        role,
        status,
        created_at,
        last_login
    FROM users
    WHERE id = ?
    AND role = 'admin'
    LIMIT 1
");

$stmt->bind_param("i", $adminId);
$stmt->execute();

$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    header("Location: dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Profile | College Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="../assets/css/admin.css?v=22">
</head>

<body>

<div class="admin-layout">

    <?php include "../includes/admin-sidebar.php"; ?>

    <main class="main-area">

        <?php include "../includes/admin-header.php"; ?>

        <main class="dashboard-content">

            <div class="page-heading">
                <div>
                    <h2>Admin Profile</h2>
                    <p>View and manage your account information</p>
                </div>

                <div class="d-flex gap-2">

                    <a href="change-password.php" class="btn btn-warning text-white">
                        <i class="bi bi-shield-lock me-1"></i>
                        Change Password
                    </a>

                    <a href="edit-profile.php" class="btn btn-primary">
                        <i class="bi bi-pencil-square me-1"></i>
                        Edit Profile
                    </a>

                </div>

            </div>

            <?php if (isset($_GET["updated"]) && $_GET["updated"] === "1"): ?>

                <div class="alert alert-success mt-3">
                    <i class="bi bi-check-circle-fill me-1"></i>
                    Profile updated successfully.
                </div>

            <?php endif; ?>

            <?php if (
                isset($_GET["password_changed"]) &&
                $_GET["password_changed"] === "1"
            ): ?>

                <div class="alert alert-success mt-3">
                    <i class="bi bi-shield-check me-1"></i>
                    Password changed successfully.
                </div>

            <?php endif; ?>

            <div class="dashboard-card mt-4">

                <div class="admin-profile-top">

                    <div class="admin-profile-avatar">
                        <?php
                        echo strtoupper(
                            substr($admin["name"], 0, 1)
                        );
                        ?>
                    </div>

                    <div class="admin-profile-top-info">

                        <h4>
                            <?php echo htmlspecialchars($admin["name"]); ?>
                        </h4>

                        <p>
                            Administrator
                            <span class="mx-1">•</span>
                            College Management System   <!-- isse change karna hai-->
                        </p>

                        <span class="admin-badge">
                            Administrator
                        </span>

                    </div>

                </div>

                <hr class="admin-profile-section-divider">

                    <div class="admin-personal-section">

                        <div class="admin-section-title">

                            <i class="bi bi-person-fill"></i>

                            <div>
                                <h5>Personal Information</h5>
                                <p>Your administrator account information</p>
                            </div>

                        </div>

                        <!-- PHONE NUMBER -->
                        <div class="row g-3 mt-1">

                            <div class="col-md-6">
                                <div class="profile-info-box">

                                    <div class="profile-info-icon">
                                        <i class="bi bi-telephone-fill"></i>
                                    </div>

                                    <div>
                                        <span>Phone Number</span>
                                        <strong>
                                            <?php
                                            echo !empty($admin["phone"])
                                                ? htmlspecialchars($admin["phone"])
                                                : "Not Added";
                                            ?>
                                        </strong>
                                    </div>

                                </div>
                            </div>

                            <!-- EMAIL ADDRESS -->
                            <div class="col-md-6">
                                <div class="profile-info-box">

                                    <div class="profile-info-icon">
                                        <i class="bi bi-envelope-fill"></i>
                                    </div>

                                    <div>
                                        <span>Email Address</span>
                                        <strong>
                                            <?php echo htmlspecialchars($admin["email"]); ?>
                                        </strong>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <!-- DOB -->
                        <div class="row g-3 mt-1">

                            <div class="col-md-6">
                                <div class="profile-info-box">

                                    <div class="profile-info-icon">
                                        <i class="bi bi-calendar-event-fill"></i>
                                    </div>

                                    <div>
                                        <span>Date of Birth</span>
                                        <strong>
                                            <?php
                                            echo !empty($admin["date_of_birth"])
                                                ? date("d M Y", strtotime($admin["date_of_birth"]))
                                                : "Not Added";
                                            ?>
                                        </strong>
                                    </div>

                                </div>
                            </div>

                            <!-- GENDER -->
                            <div class="col-md-6">
                                <div class="profile-info-box">

                                    <div class="profile-info-icon">
                                        <i class="bi bi-person-vcard-fill"></i>
                                    </div>

                                    <div>
                                        <span>Gender</span>
                                        <strong>
                                            <?php
                                            echo !empty($admin["gender"])
                                                ? htmlspecialchars($admin["gender"])
                                                : "Not Added";
                                            ?>
                                        </strong>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- ROLE -->
                        <div class="row g-3 mt-1">

                            <div class="col-md-6">
                                <div class="profile-info-box">

                                    <div class="profile-info-icon">
                                        <i class="bi bi-shield-check"></i>
                                    </div>

                                    <div>
                                        <span>Role</span>
                                        <strong>Administrator</strong>
                                    </div>

                                </div>
                            </div>

                            <!-- ACCOUNT STATUS -->
                            <div class="col-md-6">
                                <div class="profile-info-box">

                                    <div class="profile-info-icon">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>

                                    <div>
                                        <span >Account Status</span>

                                        <strong>
                                            <?php if ((int)$admin["status"] === 1): ?>

                                                <span class="profile-status-badge active">
                                                    Active
                                                </span>

                                            <?php else: ?>

                                                <span class="profile-status-badge inactive">
                                                    Inactive
                                                </span>

                                            <?php endif; ?>
                                        </strong>
                                    </div>

                                </div>

                            </div>

                        </div>

                        <div class="row g-3 mt-1">

                            <div class="col-12">
                                <div class="profile-info-box">

                                    <div class="profile-info-icon">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </div>

                                    <div>
                                        <span>Address</span>

                                        <strong>
                                            <?php
                                            echo !empty($admin["address"])
                                                ? htmlspecialchars($admin["address"])
                                                : "Not Added";
                                            ?>
                                        </strong>
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>
                
                <!--MEMBER SINCE  -->
                <hr class="admin-profile-divider">

                <div class="row g-3 mt-1">

                    <div class="col-md-6">
                        <div class="profile-info-box">

                            <div class="profile-info-icon">
                                <i class="bi bi-calendar3"></i>
                            </div>

                            <div>
                                <span>Member Since</span>
                                <strong>
                                    <?php
                                    echo !empty($admin["created_at"])
                                        ? date("d M Y", strtotime($admin["created_at"]))
                                        : "-";
                                    ?>
                                </strong>
                            </div>

                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="profile-info-box">

                            <div class="profile-info-icon">
                                <i class="bi bi-clock-fill"></i>
                            </div>

                            <div>
                                <span>Last Login</span>
                                <strong>
                                    <?php
                                    echo !empty($admin["last_login"])
                                        ? date("d M Y, h:i A", strtotime($admin["last_login"]))
                                        : "First Login";
                                    ?>
                                </strong>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

        </main>

    </main>

</div>

<script src="../assets/js/admin.js"></script>

</body>
</html>