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

$subjectId = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);

if ($subjectId <= 0) {
    header("Location: subjects.php");
    exit;
}

$message = "";
$messageType = "";

// Load departments
$departments = $conn->query("
    SELECT id, department_name
    FROM departments
    WHERE status = 1
    ORDER BY department_name ASC
");

// Load subject
$stmt = $conn->prepare("
    SELECT *
    FROM subjects
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $subjectId);
$stmt->execute();

$result = $stmt->get_result();
$subject = $result->fetch_assoc();

$stmt->close();

if (!$subject) {
    header("Location: subjects.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subject_code = trim($_POST["subject_code"] ?? "");
    $subject_name = trim($_POST["subject_name"] ?? "");
    $department_id = (int) ($_POST["department_id"] ?? 0);
    $semester = (int) ($_POST["semester"] ?? 0);
    $credits = (int) ($_POST["credits"] ?? 0);
    $subject_type = $_POST["subject_type"] ?? "Theory";
    $status = (int) ($_POST["status"] ?? 1);

    if (
        $subject_code === "" ||
        $subject_name === "" ||
        $department_id <= 0 ||
        $semester <= 0
    ) {

        $message = "Please fill all required fields.";
        $messageType = "danger";

    } else {

        $updateStmt = $conn->prepare("
            UPDATE subjects
            SET
                subject_code = ?,
                subject_name = ?,
                department_id = ?,
                semester = ?,
                credits = ?,
                subject_type = ?,
                status = ?
            WHERE id = ?
        ");

        $updateStmt->bind_param(
            "ssiiisii",
            $subject_code,
            $subject_name,
            $department_id,
            $semester,
            $credits,
            $subject_type,
            $status,
            $subjectId
        );

        if ($updateStmt->execute()) {

            $message = "Subject updated successfully.";
            $messageType = "success";

            // Refresh subject data
            $subject["subject_code"] = $subject_code;
            $subject["subject_name"] = $subject_name;
            $subject["department_id"] = $department_id;
            $subject["semester"] = $semester;
            $subject["credits"] = $credits;
            $subject["subject_type"] = $subject_type;
            $subject["status"] = $status;

        } else {

            $message = "Could not update subject. Subject code may already exist.";
            $messageType = "danger";
        }

        $updateStmt->close();
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

    <title>Edit Subject | College CMS</title>

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
                    <h2>Edit Subject</h2>
                    <p>Update academic subject information.</p>
                </div>

                <a
                    href="subjects.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Subjects
                </a>

            </div>

            <?php if ($message !== ""): ?>

                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>

            <div class="dashboard-card">

                <form method="POST">

                    <input
                        type="hidden"
                        name="id"
                        value="<?php echo (int)$subject["id"]; ?>"
                    >

                    <div class="row g-4">

                        <div class="col-md-6">

                            <label class="form-label">
                                Subject Code *
                            </label>

                            <input
                                type="text"
                                name="subject_code"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $subject["subject_code"]
                                    );
                                ?>"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Subject Name *
                            </label>

                            <input
                                type="text"
                                name="subject_name"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $subject["subject_name"]
                                    );
                                ?>"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Department *
                            </label>

                            <select
                                name="department_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Department
                                </option>

                                <?php if ($departments): ?>

                                    <?php while (
                                        $department = $departments->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$department["id"]; ?>"
                                            <?php
                                            if (
                                                (int)$subject["department_id"]
                                                ===
                                                (int)$department["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $department["department_name"]
                                            );
                                            ?>
                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>

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
                                            (int)$subject["semester"] === $i
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

                        <div class="col-md-6">

                            <label class="form-label">
                                Credits
                            </label>

                            <input
                                type="number"
                                name="credits"
                                class="form-control"
                                min="0"
                                max="10"
                                value="<?php
                                    echo (int)$subject["credits"];
                                ?>"
                            >

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Subject Type
                            </label>

                            <select
                                name="subject_type"
                                class="form-select"
                            >

                                <option
                                    value="Theory"
                                    <?php
                                    if (
                                        $subject["subject_type"] === "Theory"
                                    ) echo "selected";
                                    ?>
                                >
                                    Theory
                                </option>

                                <option
                                    value="Practical"
                                    <?php
                                    if (
                                        $subject["subject_type"] === "Practical"
                                    ) echo "selected";
                                    ?>
                                >
                                    Practical
                                </option>

                                <option
                                    value="Both"
                                    <?php
                                    if (
                                        $subject["subject_type"] === "Both"
                                    ) echo "selected";
                                    ?>
                                >
                                    Both
                                </option>

                            </select>

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
                                    if ((int)$subject["status"] === 1) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="0"
                                    <?php
                                    if ((int)$subject["status"] === 0) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>

                        <div class="col-12">

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-check-circle"></i>
                                Update Subject
                            </button>

                            <a
                                href="view-subject.php?id=<?php echo (int)$subject["id"]; ?>"
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