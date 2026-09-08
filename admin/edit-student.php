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

/* -------------------------
   STUDENT ID CHECK
------------------------- */

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: students.php");
    exit;
}

$id = (int) $_GET["id"];


/* -------------------------
   GET STUDENT
------------------------- */

$stmt = $conn->prepare(
    "SELECT * FROM students WHERE id = ? LIMIT 1"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: students.php");
    exit;
}

$student = $result->fetch_assoc();

$message = "";
$messageType = "";


/* -------------------------
   UPDATE STUDENT
------------------------- */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $admission_no = trim($_POST["admission_no"] ?? "");
    $first_name = trim($_POST["first_name"] ?? "");
    $last_name = trim($_POST["last_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $gender = $_POST["gender"] ?? "";
    $date_of_birth = $_POST["date_of_birth"] ?? "";
    $course = trim($_POST["course"] ?? "");
    $semester = (int) ($_POST["semester"] ?? 0);
    $address = trim($_POST["address"] ?? "");
    $status = (int) ($_POST["status"] ?? 1);

    if (
        $admission_no === "" ||
        $first_name === "" ||
        $last_name === "" ||
        $course === "" ||
        $semester < 1 ||
        $semester > 8
    ) {

        $message = "Please fill all required fields.";
        $messageType = "danger";

    } else {

        $update = $conn->prepare("
            UPDATE students
            SET
                admission_no = ?,
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                gender = ?,
                date_of_birth = ?,
                course = ?,
                semester = ?,
                address = ?,
                status = ?
            WHERE id = ?
        ");

        $update->bind_param(
            "ssssssssisii",
            $admission_no,
            $first_name,
            $last_name,
            $email,
            $phone,
            $gender,
            $date_of_birth,
            $course,
            $semester,
            $address,
            $status,
            $id
        );

        if ($update->execute()) {

            $message = "Student updated successfully.";
            $messageType = "success";

            // Get latest updated data
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();

        } else {

            $message =
                "Could not update student. Admission number or email may already exist.";

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

    <title>Edit Student | College CMS</title>

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

                    <h2>Edit Student</h2>

                    <p>
                        Update student information and account status.
                    </p>

                </div>

                <a
                    href="students.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Students
                </a>

            </div>


            <?php if ($message !== ""): ?>

                <div
                    class="alert alert-<?php echo $messageType; ?>"
                >
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <form method="POST">

                    <div class="row g-4">


                        <!-- Admission Number -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Admission Number *
                            </label>

                            <input
                                type="text"
                                name="admission_no"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $student["admission_no"]
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
                                value="<?php
                                    echo htmlspecialchars(
                                        $student["email"] ?? ""
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
                                        $student["first_name"]
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
                                        $student["last_name"]
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
                                        $student["phone"] ?? ""
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
                                    if ($student["gender"] === "Male")
                                        echo "selected";
                                    ?>
                                >
                                    Male
                                </option>

                                <option
                                    value="Female"
                                    <?php
                                    if ($student["gender"] === "Female")
                                        echo "selected";
                                    ?>
                                >
                                    Female
                                </option>

                                <option
                                    value="Other"
                                    <?php
                                    if ($student["gender"] === "Other")
                                        echo "selected";
                                    ?>
                                >
                                    Other
                                </option>

                            </select>

                        </div>


                        <!-- DOB -->

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
                                        $student["date_of_birth"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <!-- Course -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Course *
                            </label>

                            <input
                                type="text"
                                name="course"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $student["course"]
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- Semester -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Semester *
                            </label>

                            <select
                                name="semester"
                                class="form-select"
                                required
                            >

                                <?php for ($i = 1; $i <= 8; $i++): ?>

                                    <option
                                        value="<?php echo $i; ?>"
                                        <?php
                                        if (
                                            (int)$student["semester"] === $i
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        Semester <?php echo $i; ?>
                                    </option>

                                <?php endfor; ?>

                            </select>

                        </div>


                        <!-- Status -->

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
                                    if ((int)$student["status"] === 1)
                                        echo "selected";
                                    ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="0"
                                    <?php
                                    if ((int)$student["status"] === 0)
                                        echo "selected";
                                    ?>
                                >
                                    Inactive
                                </option>

                            </select>

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
                            ><?php
                                echo htmlspecialchars(
                                    $student["address"] ?? ""
                                );
                            ?></textarea>

                        </div>


                        <!-- Buttons -->

                        <div class="col-12">

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-check-lg"></i>
                                Update Student
                            </button>

                            <a
                                href="students.php"
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