<?php

session_start();

require_once "../config/database.php";

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header("Location: ../index.php");
    exit;
}

$currentPage = "events.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $eventDate = $_POST["event_date"] ?? "";
    $eventTime = $_POST["event_time"] ?? null;
    $eventType = trim($_POST["event_type"] ?? "General");
    $status = isset($_POST["status"]) ? 1 : 0;

    if ($title === "" || $eventDate === "") {

        $error = "Title and event date are required.";

    } else {

        $insertQuery = $conn->prepare("
            INSERT INTO events
            (
                title,
                description,
                event_date,
                event_time,
                event_type,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $insertQuery->bind_param(
            "sssssi",
            $title,
            $description,
            $eventDate,
            $eventTime,
            $eventType,
            $status
        );

        if ($insertQuery->execute()) {

            header("Location: events.php?added=1");
            exit;

        } else {

            $error = "Unable to add event.";
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

    <title>Add Event | College CMS</title>

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
        href="../assets/css/admin.css?v=112"
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
                    <h2>Add Event</h2>

                    <p>
                        Create a new college event.
                    </p>
                </div>

                <a
                    href="events.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Events
                </a>

            </div>


            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <div class="dashboard-card event-form-card">

                <div class="p-4">

                    <form method="POST">

                        <div class="row g-3">

                            <div class="col-md-8">

                                <label class="form-label">
                                    Event Title
                                </label>

                                <input
                                    type="text"
                                    name="title"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <div class="col-md-4">

                                <label class="form-label">
                                    Event Type
                                </label>

                                <select
                                    name="event_type"
                                    class="form-select"
                                >
                                    <option value="General">General</option>
                                    <option value="Meeting">Meeting</option>
                                    <option value="Exam">Exam</option>
                                    <option value="Holiday">Holiday</option>
                                    <option value="Academic">Academic</option>
                                </select>

                            </div>


                            <div class="col-md-6">

                                <label class="form-label">
                                    Event Date
                                </label>

                                <input
                                    type="date"
                                    name="event_date"
                                    class="form-control"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label class="form-label">
                                    Event Time
                                </label>

                                <input
                                    type="time"
                                    name="event_time"
                                    class="form-control"
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
                                ></textarea>

                            </div>


                            <div class="col-12">

                                <div class="form-check">

                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="status"
                                        id="status"
                                        checked
                                    >

                                    <label
                                        class="form-check-label"
                                        for="status"
                                    >
                                        Active Event
                                    </label>

                                </div>

                            </div>

                        </div>


                        <div class="d-flex gap-2 mt-4">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-check-circle me-1"></i>
                                Save Event
                            </button>

                            <a
                                href="events.php"
                                class="btn btn-light border"
                            >
                                Cancel
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </main>

    </div>

</div>

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>

<script src="../assets/js/admin.js"></script>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>
</html>