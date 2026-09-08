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

$facultyUserId = (int) $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT id, password
    FROM users
    WHERE id = ?
    AND role = 'faculty'
    LIMIT 1
");

$stmt->bind_param("i", $facultyUserId);
$stmt->execute();

$result = $stmt->get_result();
$facultyUser = $result->fetch_assoc();

if (!$facultyUser) {
    header("Location: profile.php");
    exit;
}

$error = "";

$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
        $currentPassword === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {
        $error = "Please fill in all password fields.";
    }
    elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirm password do not match.";
    }
    elseif (strlen($newPassword) < 8) {
        $error = "New password must be at least 8 characters long.";
    }
    else {

        if (!password_verify($currentPassword, $facultyUser["password"])) {

            $error = "Current password is incorrect.";

        } else {

            $hashedPassword = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );

            $updateStmt = $conn->prepare("
                UPDATE users
                SET password = ?
                WHERE id = ?
                AND role = 'faculty'
            ");

            $updateStmt->bind_param(
                "si",
                $hashedPassword,
                $facultyUserId
            );

            if ($updateStmt->execute()) {

                header("Location: profile.php?password_changed=1");
                exit;

            } else {

                $error = "Unable to change password. Please try again.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Change Password | College CMS</title>

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
        href="../assets/css/admin.css?v=101"
    >

</head>

<body>

<div class="main-area">

    <?php include "../includes/faculty-sidebar.php"; ?>

    <main class="admin-main">

        <?php include "../includes/faculty-header.php"; ?>

        <main class="dashboard-content">

            <div class="page-heading">

                <div>
                    <h2>Change Password</h2>
                    <p>Update your Current Account password</p>
                </div>

                <a href="profile.php" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Profile
                </a>

            </div>

            <div class="dashboard-card mt-4">

                <div class="card-heading">

                    <div class="d-flex align-items-center gap-3">

                        <div class="dashboard-heading-icon heading-blue">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>

                        <div>
                            <h5>Password Security</h5>
                            <p>Choose a new password for your account</p>
                        </div>

                    </div>

                </div>

                <?php if (!empty($error)): ?>

                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Current Password</label>

                        <div class="input-group">
                            <input
                                type="password"
                                name="current_password"
                                id="currentPassword"
                                class="form-control"
                                required
                            >

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="togglePassword('currentPassword', this)"
                            >
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>


                    <div class="mb-3">
                        <label class="form-label">New Password</label>

                        <div class="input-group">
                            <input
                                type="password"
                                name="new_password"
                                id="newPassword"
                                class="form-control"
                                required
                            >

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="togglePassword('newPassword', this)"
                            >
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>


                    <div class="mb-4">
                        <label class="form-label">Confirm New Password</label>

                        <div class="input-group">
                            <input
                                type="password"
                                name="confirm_password"
                                id="confirmPassword"
                                class="form-control"
                                required
                            >

                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                onclick="togglePassword('confirmPassword', this)"
                            >
                                <i class="bi bi-eye-slash"></i>
                            </button>
                            
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-key-fill me-1"></i>
                        Change Password
                    </button>

                    <a href="profile.php" class="btn btn-light ms-2">
                        Cancel
                    </a>

                    </div>

                </form>

            </div>

        </main>

    </main>

</div>

<script>
function togglePassword(inputId, button) {

    const input = document.getElementById(inputId);
    const icon = button.querySelector("i");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }
}
</script>

</body>
</html>