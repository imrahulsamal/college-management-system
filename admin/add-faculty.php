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

    $employee_no = trim($_POST["employee_no"] ?? "");
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $gender = $_POST["gender"] ?? "";
    $date_of_birth = $_POST["date_of_birth"] ?? "";
    $department = trim($_POST["department"] ?? "");
    $designation = trim($_POST["designation"] ?? "");
    $qualification = trim($_POST["qualification"] ?? "");
    $joining_date = $_POST["joining_date"] ?? "";
    $address = trim($_POST["address"] ?? "");

    if (
        $employee_no === "" ||
        $first_name === "" ||
        $last_name === "" ||
        $department === "" ||
        $designation === ""
    ) {
        $message = "Please fill all required fields.";
        $messageType = "danger";
    } else {

        // Optional date fields ko NULL banayenge
        $date_of_birth = $date_of_birth !== "" ? $date_of_birth : null;
        $joining_date = $joining_date !== "" ? $joining_date : null;

        $stmt = $conn->prepare("
            INSERT INTO faculty
            (
                employee_no,
                first_name,
                last_name,
                email,
                phone,
                gender,
                date_of_birth,
                department,
                designation,
                qualification,
                joining_date,
                address
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssssssssss",
            $employee_no,
            $first_name,
            $last_name,
            $email,
            $phone,
            $gender,
            $date_of_birth,
            $department,
            $designation,
            $qualification,
            $joining_date,
            $address
        );

        if ($stmt->execute()) {

            $message = "Faculty member added successfully.";
            $messageType = "success";

            // Form clear after successful insert
            $_POST = [];

        } else {

            $message = "Could not add faculty member. Employee number or email may already exist.";
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

    <title>Add Faculty | College CMS</title>

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
                    <h2>Add Faculty</h2>

                    <p>
                        Add a new faculty member to the college.
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


            <?php if ($message !== ""): ?>

                <div class="alert alert-<?php echo $messageType; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <form method="POST">

                    <div class="row g-4">


                        <!-- Employee Number -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Employee Number *
                            </label>

                            <input
                                type="text"
                                name="employee_no"
                                class="form-control"
                                placeholder="e.g. FAC001"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["employee_no"] ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- Email -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="faculty@college.com"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["email"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <!-- First Name -->

                        <div class="col-md-6">

                            <label class="form-label">
                                First Name *
                            </label>

                            <input
                                type="text"
                                name="first_name"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["first_name"] ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- Last Name -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Last Name *
                            </label>

                            <input
                                type="text"
                                name="last_name"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["last_name"] ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- Phone -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Phone
                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["phone"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <!-- Gender -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Gender
                            </label>

                            <select
                                name="gender"
                                class="form-select"
                            >

                                <option value="">
                                    Select Gender
                                </option>

                                <option
                                    value="Male"
                                    <?php
                                    if (
                                        ($_POST["gender"] ?? "") === "Male"
                                    ) echo "selected";
                                    ?>
                                >
                                    Male
                                </option>

                                <option
                                    value="Female"
                                    <?php
                                    if (
                                        ($_POST["gender"] ?? "") === "Female"
                                    ) echo "selected";
                                    ?>
                                >
                                    Female
                                </option>

                                <option
                                    value="Other"
                                    <?php
                                    if (
                                        ($_POST["gender"] ?? "") === "Other"
                                    ) echo "selected";
                                    ?>
                                >
                                    Other
                                </option>

                            </select>

                        </div>


                        <!-- Date of Birth -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Date of Birth
                            </label>

                            <input
                                type="date"
                                name="date_of_birth"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["date_of_birth"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <!-- Joining Date -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Joining Date
                            </label>

                            <input
                                type="date"
                                name="joining_date"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["joining_date"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <!-- Department -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Department *
                            </label>

                            <input
                                type="text"
                                name="department"
                                class="form-control"
                                placeholder="e.g. Computer Science"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["department"] ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- Designation -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Designation *
                            </label>

                            <input
                                type="text"
                                name="designation"
                                class="form-control"
                                placeholder="e.g. Assistant Professor"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["designation"] ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- Qualification -->

                        <div class="col-12">

                            <label class="form-label">
                                Qualification
                            </label>

                            <input
                                type="text"
                                name="qualification"
                                class="form-control"
                                placeholder="e.g. M.Tech, Ph.D"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["qualification"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <!-- Address -->

                        <div class="col-12">

                            <label class="form-label">
                                Address
                            </label>

                            <textarea
                                name="address"
                                class="form-control"
                                rows="4"
                                placeholder="Enter address"
                            ><?php
                                echo htmlspecialchars(
                                    $_POST["address"] ?? ""
                                );
                            ?></textarea>

                        </div>


                        <!-- Buttons -->

                        <div class="col-12">

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-person-plus-fill"></i>
                                Add Faculty
                            </button>

                            <a
                                href="faculty.php"
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