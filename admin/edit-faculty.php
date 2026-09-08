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
    $status = (int) ($_POST["status"] ?? 1);

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

        $date_of_birth = $date_of_birth !== "" ? $date_of_birth : null;
        $joining_date = $joining_date !== "" ? $joining_date : null;

        $update = $conn->prepare("
            UPDATE faculty
            SET
                employee_no = ?,
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                gender = ?,
                date_of_birth = ?,
                department = ?,
                designation = ?,
                qualification = ?,
                joining_date = ?,
                address = ?,
                status = ?
            WHERE id = ?
        ");

        $update->bind_param(
            "ssssssssssssii",
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
            $address,
            $status,
            $id
        );

        if ($update->execute()) {

            $message = "Faculty member updated successfully.";
            $messageType = "success";

            $stmt->execute();
            $result = $stmt->get_result();
            $faculty = $result->fetch_assoc();

        } else {

            $message =
                "Could not update faculty member. Employee number or email may already exist.";

            $messageType = "danger";
        }
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

    <title>Edit Faculty | College CMS</title>

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
                    <h2>Edit Faculty</h2>
                    <p>Update faculty member information.</p>
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


                        <div class="col-md-6">

                            <label class="form-label">
                                Employee Number *
                            </label>

                            <input
                                type="text"
                                name="employee_no"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $faculty["employee_no"]
                                    );
                                ?>"
                                required
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
                                value="<?php
                                    echo htmlspecialchars(
                                        $faculty["email"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


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
                                        $faculty["first_name"]
                                    );
                                ?>"
                                required
                            >

                        </div>


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
                                        $faculty["last_name"]
                                    );
                                ?>"
                                required
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
                                value="<?php
                                    echo htmlspecialchars(
                                        $faculty["phone"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


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
                                    if ($faculty["gender"] === "Male")
                                        echo "selected";
                                    ?>
                                >
                                    Male
                                </option>

                                <option
                                    value="Female"
                                    <?php
                                    if ($faculty["gender"] === "Female")
                                        echo "selected";
                                    ?>
                                >
                                    Female
                                </option>

                                <option
                                    value="Other"
                                    <?php
                                    if ($faculty["gender"] === "Other")
                                        echo "selected";
                                    ?>
                                >
                                    Other
                                </option>

                            </select>

                        </div>


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
                                        $faculty["date_of_birth"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


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
                                        $faculty["joining_date"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Department *
                            </label>

                            <input
                                type="text"
                                name="department"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $faculty["department"]
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Designation *
                            </label>

                            <input
                                type="text"
                                name="designation"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $faculty["designation"]
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Qualification
                            </label>

                            <input
                                type="text"
                                name="qualification"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $faculty["qualification"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="status"
                                class="form-select"
                            >

                                <option
                                    value="1"
                                    <?php
                                    if ((int)$faculty["status"] === 1)
                                        echo "selected";
                                    ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="0"
                                    <?php
                                    if ((int)$faculty["status"] === 0)
                                        echo "selected";
                                    ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>


                        <div class="col-12">

                            <label class="form-label">
                                Address
                            </label>

                            <textarea
                                name="address"
                                class="form-control"
                                rows="4"
                            ><?php
                                echo htmlspecialchars(
                                    $faculty["address"] ?? ""
                                );
                            ?></textarea>

                        </div>


                        <div class="col-12">

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-check-lg"></i>
                                Update Faculty
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