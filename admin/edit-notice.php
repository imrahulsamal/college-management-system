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

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($id <= 0) {
    header("Location: notices.php");
    exit;
}


/* FETCH NOTICE */

$stmt = $conn->prepare("
    SELECT *
    FROM notices
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$notice = $result->fetch_assoc();

$stmt->close();

if (!$notice) {
    header("Location: notices.php");
    exit;
}


/* DEFAULT VALUES */

$error = "";

$title = $notice["title"];
$description = $notice["description"];
$noticeDate = $notice["notice_date"];
$audience = $notice["audience"];
$status = (int)$notice["status"];


/* UPDATE NOTICE */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $noticeDate = trim($_POST["notice_date"] ?? "");
    $audience = trim($_POST["audience"] ?? "All");
    $status = isset($_POST["status"]) ? 1 : 0;

    if (
        $title === "" ||
        $description === "" ||
        $noticeDate === ""
    ) {

        $error = "Please fill all required fields.";

    } elseif (
        !in_array(
            $audience,
            ["All", "Students", "Faculty"],
            true
        )
    ) {

        $error = "Invalid audience selected.";

    } else {

        $updateStmt = $conn->prepare("
            UPDATE notices
            SET
                title = ?,
                description = ?,
                notice_date = ?,
                audience = ?,
                status = ?
            WHERE id = ?
        ");

        if (!$updateStmt) {
            die("Prepare failed: " . $conn->error);
        }

        $updateStmt->bind_param(
            "ssssii",
            $title,
            $description,
            $noticeDate,
            $audience,
            $status,
            $id
        );

        if ($updateStmt->execute()) {

            header(
                "Location: view-notice.php?id=" .
                $id .
                "&updated=1"
            );
            exit;

        } else {

            $error = "Failed to update notice.";

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

    <title>Edit Notice | College CMS</title>

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

                    <h2>Edit Notice</h2>

                    <p>
                        Update notice information.
                    </p>

                </div>

                <a
                    href="view-notice.php?id=<?php echo $id; ?>"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back
                </a>

            </div>


            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <i class="bi bi-exclamation-circle me-1"></i>

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <form method="POST">

                    <div class="row g-4">


                        <div class="col-md-8">

                            <label class="form-label">
                                Notice Title
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                value="<?php echo htmlspecialchars($title); ?>"
                                required
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label">
                                Notice Date
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="notice_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($noticeDate); ?>"
                                required
                            >

                        </div>


                        <div class="col-md-12">

                            <label class="form-label">
                                Description
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="6"
                                required
                            ><?php echo htmlspecialchars($description); ?></textarea>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Audience
                            </label>

                            <select
                                name="audience"
                                class="form-select"
                            >

                                <option
                                    value="All"
                                    <?php
                                    if ($audience === "All") {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Everyone
                                </option>

                                <option
                                    value="Students"
                                    <?php
                                    if ($audience === "Students") {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Students
                                </option>

                                <option
                                    value="Faculty"
                                    <?php
                                    if ($audience === "Faculty") {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Faculty
                                </option>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label d-block">
                                Status
                            </label>

                            <div class="form-check form-switch mt-2">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="status"
                                    id="status"
                                    <?php
                                    if ($status === 1) {
                                        echo "checked";
                                    }
                                    ?>
                                >

                                <label
                                    class="form-check-label"
                                    for="status"
                                >
                                    Active Notice
                                </label>

                            </div>

                        </div>


                        <div class="col-12">

                            <hr>

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-check-circle"></i>
                                Update Notice
                            </button>

                            <a
                                href="notices.php"
                                class="btn btn-outline-secondary ms-2"
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