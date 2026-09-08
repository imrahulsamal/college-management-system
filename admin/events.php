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

$events = $conn->query("
    SELECT *
    FROM events
    ORDER BY event_date ASC, event_time ASC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Events | College CMS</title>

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
        href="../assets/css/admin.css?v=115"
    >

</head>

<body>

<div class="admin-layout">

    <?php include "../includes/admin-sidebar.php"; ?>

    <main class="main-area">

        <?php include "../includes/admin-header.php"; ?>

        <div class="dashboard-content">

            <div class="page-heading">

                <div>
                    <h2>Events</h2>
                    <p>Manage upcoming college events.</p>
                </div>

                <a
                    href="add-event.php"
                    class="btn btn-primary"
                >
                    <i class="bi bi-plus-lg me-1"></i>
                    Add Event
                </a>

            </div>

            <?php if (isset($_GET["deleted"]) && $_GET["deleted"] == 1): ?>

                <div
                    class="alert alert-success alert-dismissible fade show mb-4"
                    role="alert"
                >
                    <i class="bi bi-check-circle-fill me-2"></i>

                    <strong>Success!</strong>
                    Event deleted successfully.

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>
                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <div class="p-0">

                    <div class="table-responsive">

                        <table class="table align-middle mb-0 events-table">

                            <thead class="table-light">

                                <tr>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php if (
                                $events &&
                                $events->num_rows > 0
                            ): ?>

                                <?php while (
                                    $event = $events->fetch_assoc()
                                ): ?>

                                    <tr>

                                        <td>
                                            <strong>
                                                <?php
                                                echo htmlspecialchars(
                                                    $event["title"]
                                                );
                                                ?>
                                            </strong>

                                            <small
                                                class="d-block text-muted"
                                            >
                                                <?php
                                                echo htmlspecialchars(
                                                    $event["description"] ?? ""
                                                );
                                                ?>
                                            </small>
                                        </td>

                                        <td>
                                            <?php
                                            echo date(
                                                "d M Y",
                                                strtotime(
                                                    $event["event_date"]
                                                )
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo !empty($event["event_time"])
                                                ? date(
                                                    "h:i A",
                                                    strtotime(
                                                        $event["event_time"]
                                                    )
                                                )
                                                : "-";
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $event["event_type"]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php if (
                                                (int)$event["status"] === 1
                                            ): ?>

                                                <span
                                                    class="badge bg-success-subtle text-success"
                                                >
                                                    Active
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="badge bg-danger-subtle text-danger"
                                                >
                                                    Inactive
                                                </span>

                                            <?php endif; ?>
                                        </td>

                                        <td class="text-end">

                                            <a
                                                href="view-event.php?id=<?php echo (int)$event["id"]; ?>"
                                                class="btn btn-sm btn-outline-primary"
                                                title="View"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </a>    

                                            <a
                                                href="edit-event.php?id=<?php echo (int)$event["id"]; ?>"
                                                class="btn btn-sm btn-outline-warning"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <form
                                                action="delete-event.php"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this event?');"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?php echo (int)$event["id"]; ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td
                                        colspan="6"
                                        class="text-center py-5 text-muted"
                                    >
                                        No events found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </main>

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