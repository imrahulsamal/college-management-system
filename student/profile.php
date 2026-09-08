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


/* Student Profile */

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

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Profile | College CMS</title>

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
        href="../assets/css/admin.css?v=11"
    >

</head>

<body class="student-panel">

<?php include "../includes/student-sidebar.php"; ?>

<div class="main-area">

    <?php include "../includes/student-header.php"; ?>

    <main class="content-area">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h3 class="mb-1">My Profile</h3>
                <p class="text-muted mb-0">
                    View your student information.
                </p>
            </div>

            <a href="change-password.php"
            class="btn btn-warning text-white px-3">

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


        <div class="card border-0 shadow-sm overflow-hidden">

            <!-- Profile Header -->
            <div class="card-body p-4 border-bottom">

                <div class="d-flex align-items-center">

                    <div
                        class="d-flex align-items-center justify-content-center rounded-3 me-3"
                        style="
                            width: 64px;
                            height: 64px;
                            background: #e8f1ff;
                            color: #2563eb;
                            font-size: 26px;
                            font-weight: 700;
                        "
                    >
                        <?php
                        echo strtoupper(
                            substr($student["first_name"], 0, 1)
                        );
                        ?>
                    </div>

                    <div>

                        <h4 class="mb-1 fw-bold">
                            <?php
                            echo htmlspecialchars(
                                $student["first_name"] . " " .
                                $student["last_name"]
                            );
                            ?>
                        </h4>

                        <div class="text-muted mb-2">
                            <?php
                            echo htmlspecialchars(
                                $student["admission_no"]
                            );
                            ?>
                            &nbsp;•&nbsp;
                            <?php
                            echo htmlspecialchars(
                                $student["course"]
                            );
                            ?>
                            &nbsp;•&nbsp;
                            Semester
                            <?php echo (int)$student["semester"]; ?>
                        </div>

                        <?php if ((int)$student["status"] === 1): ?>

                            <span class="badge bg-success">
                                Active Student
                            </span>

                        <?php else: ?>

                            <span class="badge bg-danger">
                                Inactive Student
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <!-- Profile Information -->
            <div class="card-body p-4">

                <h6 class="fw-bold mb-4">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>
                    Personal Information
                </h6>


                <div class="row g-4">

                    <!-- Admission -->
                    <div class="col-md-6">

                        <div class="profile-info-item">

                            <div class="profile-info-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>

                            <div>
                                <span>Admission Number</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $student["admission_no"]
                                    );
                                    ?>
                                </strong>
                            </div>

                        </div>

                    </div>


                    <!-- Email -->
                    <div class="col-md-6">

                        <div class="profile-info-item">

                            <div class="profile-info-icon">
                                <i class="bi bi-envelope"></i>
                            </div>

                            <div>
                                <span>Email Address</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $student["email"] ?: "-"
                                    );
                                    ?>
                                </strong>
                            </div>

                        </div>

                    </div>


                    <!-- Phone -->
                    <div class="col-md-6">

                        <div class="profile-info-item">

                            <div class="profile-info-icon">
                                <i class="bi bi-telephone"></i>
                            </div>

                            <div>
                                <span>Phone Number</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $student["phone"] ?: "-"
                                    );
                                    ?>
                                </strong>
                            </div>

                        </div>

                    </div>


                    <!-- DOB -->
                    <div class="col-md-6">

                        <div class="profile-info-item">

                            <div class="profile-info-icon">
                                <i class="bi bi-calendar3"></i>
                            </div>

                            <div>
                                <span>Date of Birth</span>

                                <strong>
                                    <?php
                                    echo !empty($student["date_of_birth"])
                                        ? date(
                                            "d M Y",
                                            strtotime(
                                                $student["date_of_birth"]
                                            )
                                        )
                                        : "-";
                                    ?>
                                </strong>
                            </div>

                        </div>

                    </div>


                    <!-- Gender -->
                    <div class="col-md-6">

                        <div class="profile-info-item">

                            <div class="profile-info-icon">
                                <i class="bi bi-person"></i>
                            </div>

                            <div>
                                <span>Gender</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $student["gender"] ?: "-"
                                    );
                                    ?>
                                </strong>
                            </div>

                        </div>

                    </div>


                    <!-- Course -->
                    <div class="col-md-6">

                        <div class="profile-info-item">

                            <div class="profile-info-icon">
                                <i class="bi bi-mortarboard"></i>
                            </div>

                            <div>
                                <span>Course</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $student["course"]
                                    );
                                    ?>
                                    -
                                    Semester
                                    <?php echo (int)$student["semester"]; ?>
                                </strong>
                            </div>

                        </div>

                    </div>


                    <!-- Address -->
                    <div class="col-12">

                        <div class="profile-info-item">

                            <div class="profile-info-icon">
                                <i class="bi bi-geo-alt"></i>
                            </div>

                            <div>
                                <span>Address</span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $student["address"] ?: "-"
                                    );
                                    ?>
                                </strong>
                            </div>

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