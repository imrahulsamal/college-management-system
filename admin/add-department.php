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

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $department_code = trim($_POST["department_code"] ?? "");
    $department_name = trim($_POST["department_name"] ?? "");
    $hod_name = trim($_POST["hod_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if (
        $department_code === "" ||
        $department_name === ""
    ) {
        $message = "Please fill all required fields.";
        $messageType = "danger";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO departments
            (
                department_code,
                department_name,
                hod_name,
                phone,
                email,
                description
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssss",
            $department_code,
            $department_name,
            $hod_name,
            $phone,
            $email,
            $description
        );

        if ($stmt->execute()) {

            $message = "Department added successfully.";
            $messageType = "success";

            $_POST = [];

        } else {

            $message = "Could not add department. Department code or name may already exist.";
            $messageType = "danger";
        }

        $stmt->close();
    }
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

    <title>Add Department | College CMS</title>

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

                    <h2>Add Department</h2>

                    <p>
                        Add a new college department.
                    </p>

                </div>

                <a
                    href="departments.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Departments
                </a>

            </div>


            <?php if ($message !== ""): ?>

                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <form method="POST">

                    <div class="row g-4">


                        <div class="col-md-6">

                            <label class="form-label">
                                Department Code *
                            </label>

                            <input
                                type="text"
                                name="department_code"
                                class="form-control"
                                placeholder="e.g. CSE"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["department_code"] ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Department Name *
                            </label>

                            <input
                                type="text"
                                name="department_name"
                                class="form-control"
                                placeholder="e.g. Computer Science"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["department_name"] ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                HOD Name
                            </label>

                            <input
                                type="text"
                                name="hod_name"
                                class="form-control"
                                placeholder="Head of Department"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["hod_name"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="department@college.com"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["email"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Phone
                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                placeholder="Enter phone number"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["phone"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <div class="col-12">

                            <label class="form-label">
                                Description
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                                placeholder="Enter department description"
                            ><?php
                                echo htmlspecialchars(
                                    $_POST["description"] ?? ""
                                );
                            ?></textarea>

                        </div>


                        <div class="col-12">

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-building-add"></i>
                                Add Department
                            </button>

                            <a
                                href="departments.php"
                                class="btn btn-light ms-2"
                            >
                                Cancel
                            </a>

                        </div>

                    </div>

                </form>

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