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

// Load active students
$students = $conn->query("
    SELECT id, admission_no, first_name, last_name
    FROM students
    WHERE status = 1
    ORDER BY first_name ASC, last_name ASC
");

// Load active subjects
$subjects = $conn->query("
    SELECT id, subject_code, subject_name
    FROM subjects
    WHERE status = 1
    ORDER BY subject_name ASC
");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int) ($_POST["student_id"] ?? 0);
    $subject_id = (int) ($_POST["subject_id"] ?? 0);
    $attendance_date = trim($_POST["attendance_date"] ?? "");
    $status = $_POST["status"] ?? "Present";
    $remarks = trim($_POST["remarks"] ?? "");

    if (
        $student_id <= 0 ||
        $subject_id <= 0 ||
        $attendance_date === "" ||
        !in_array($status, ["Present", "Absent", "Leave"], true)
    ) {

        $message = "Please fill all required fields.";
        $messageType = "danger";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO attendance
            (
                student_id,
                subject_id,
                attendance_date,
                status,
                remarks
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iisss",
            $student_id,
            $subject_id,
            $attendance_date,
            $status,
            $remarks
        );

        if ($stmt->execute()) {

            $message = "Attendance added successfully.";
            $messageType = "success";

            $_POST = [];

        } else {

            if ($stmt->errno === 1062) {
                $message = "Attendance for this student, subject and date already exists.";
            } else {
                $message = "Could not add attendance.";
            }

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

    <title>Add Attendance | College CMS</title>

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
                    <h2>Add Attendance</h2>
                    <p>Record student attendance for a subject.</p>
                </div>

                <a
                    href="attendance.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Attendance
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
                                Student *
                            </label>

                            <select
                                name="student_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Student
                                </option>

                                <?php if ($students): ?>

                                    <?php while (
                                        $student = $students->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$student["id"]; ?>"
                                            <?php
                                            if (
                                                (int)($_POST["student_id"] ?? 0)
                                                ===
                                                (int)$student["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $student["admission_no"]
                                                . " - "
                                                . $student["first_name"]
                                                . " "
                                                . $student["last_name"]
                                            );
                                            ?>
                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Subject *
                            </label>

                            <select
                                name="subject_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Subject
                                </option>

                                <?php if ($subjects): ?>

                                    <?php while (
                                        $subject = $subjects->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$subject["id"]; ?>"
                                            <?php
                                            if (
                                                (int)($_POST["subject_id"] ?? 0)
                                                ===
                                                (int)$subject["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $subject["subject_code"]
                                                . " - "
                                                . $subject["subject_name"]
                                            );
                                            ?>
                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Attendance Date *
                            </label>

                            <input
                                type="date"
                                name="attendance_date"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["attendance_date"]
                                        ?? date("Y-m-d")
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Status *
                            </label>

                            <select
                                name="status"
                                class="form-select"
                                required
                            >

                                <option
                                    value="Present"
                                    <?php
                                    if (
                                        ($_POST["status"] ?? "Present")
                                        === "Present"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Present
                                </option>

                                <option
                                    value="Absent"
                                    <?php
                                    if (
                                        ($_POST["status"] ?? "")
                                        === "Absent"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Absent
                                </option>

                                <option
                                    value="Leave"
                                    <?php
                                    if (
                                        ($_POST["status"] ?? "")
                                        === "Leave"
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Leave
                                </option>

                            </select>

                        </div>


                        <div class="col-12">

                            <label class="form-label">
                                Remarks
                            </label>

                            <textarea
                                name="remarks"
                                class="form-control"
                                rows="4"
                                placeholder="Optional remarks..."
                            ><?php
                                echo htmlspecialchars(
                                    $_POST["remarks"] ?? ""
                                );
                            ?></textarea>

                        </div>


                        <div class="col-12">

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-calendar-check"></i>
                                Save Attendance
                            </button>

                            <a
                                href="attendance.php"
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