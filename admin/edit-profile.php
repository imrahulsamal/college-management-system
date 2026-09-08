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
    header("Location: profile.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $dateOfBirth = trim($_POST["date_of_birth"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if ($name === "" || $email === "") {

        $error = "Name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (
        $phone !== "" &&
        !preg_match("/^[0-9+\-\s]{7,20}$/", $phone)
    ) {

        $error = "Please enter a valid phone number.";

    } elseif (
        $gender !== "" &&
        !in_array($gender, ["Male", "Female", "Other"], true)
    ) {

        $error = "Please select a valid gender.";

    } elseif (
        $dateOfBirth !== "" &&
        strtotime($dateOfBirth) > time()
    ) {

        $error = "Date of birth cannot be in the future.";

    } else {

        $emailCheck = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ");

        $emailCheck->bind_param("si", $email, $adminId);
        $emailCheck->execute();

        $emailResult = $emailCheck->get_result();

        if ($emailResult->num_rows > 0) {

            $error = "This email address is already in use.";

        } else {

            $updateStmt = $conn->prepare("
                UPDATE users
                SET
                    name = ?,
                    email = ?,
                    phone = ?,
                    date_of_birth = NULLIF(?, ''),
                    gender = NULLIF(?, ''),
                    address = ?
                WHERE id = ?
                AND role = 'admin'
            ");

            $updateStmt->bind_param(
                "ssssssi",
                $name,
                $email,
                $phone,
                $dateOfBirth,
                $gender,
                $address,
                $adminId
            );

            if ($updateStmt->execute()) {

                $_SESSION["user_name"] = $name;
                $_SESSION["user_email"] = $email;

                header("Location: profile.php?updated=1");
                exit;

            } else {

                $error = "Unable to update profile. Please try again.";
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

    <title>Edit Profile | College Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="../assets/css/admin.css?v=20">
</head>

<body>

<div class="admin-layout">

    <?php include "../includes/admin-sidebar.php"; ?>

    <div class="main-area">

        <?php include "../includes/admin-header.php"; ?>

        <main class="dashboard-content">

            <div class="page-heading">
                <div>
                    <h2>Edit Profile</h2>
                    <p>Update your administrator account information</p>
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
                            <i class="bi bi-pencil-square"></i>
                        </div>

                        <div>
                            <h5>Personal Information</h5>
                            <p>Update your name and email address</p>
                        </div>

                    </div>
                </div>

                <?php if (!empty($error)): ?>

                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>

                <form method="POST">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($admin["name"]); ?>"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?php echo htmlspecialchars($admin["email"]); ?>"
                                required
                            >
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>

                            <input
                                type="tel"
                                name="phone"
                                class="form-control"
                                value="<?php echo htmlspecialchars($admin["phone"] ?? ""); ?>"
                                placeholder="Enter phone number"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>

                            <input
                                type="date"
                                name="date_of_birth"
                                class="form-control"
                                value="<?php echo htmlspecialchars($admin["date_of_birth"] ?? ""); ?>"
                            >
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">Gender</label>

                            <select name="gender" class="form-select">

                                <option value="">Select Gender</option>

                                <option value="Male"
                                    <?php echo ($admin["gender"] ?? "") === "Male" ? "selected" : ""; ?>>
                                    Male
                                </option>

                                <option value="Female"
                                    <?php echo ($admin["gender"] ?? "") === "Female" ? "selected" : ""; ?>>
                                    Female
                                </option>

                                <option value="Other"
                                    <?php echo ($admin["gender"] ?? "") === "Other" ? "selected" : ""; ?>>
                                    Other
                                </option>

                            </select>
                        </div>


                        <div class="col-12">
                            <label class="form-label">Address</label>

                            <textarea
                                name="address"
                                class="form-control"
                                rows="3"
                                placeholder="Enter address"
                            ><?php echo htmlspecialchars($admin["address"] ?? ""); ?></textarea>
                        </div>

                    </div>

                    <!-- SUBMIT BUTTON -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>
                            Save Changes
                        </button>

                        <a href="profile.php" class="btn btn-light ms-2">
                            Cancel
                        </a>
                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

<script src="../assets/js/admin.js"></script>

</body>
</html>