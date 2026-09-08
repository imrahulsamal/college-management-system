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

$eventId = (int) ($_GET["id"] ?? 0);

if ($eventId <= 0) {
    header("Location: events.php");
    exit;
}

$eventQuery = $conn->prepare("
    SELECT *
    FROM events
    WHERE id = ?
    LIMIT 1
");

$eventQuery->bind_param("i", $eventId);
$eventQuery->execute();

$eventResult = $eventQuery->get_result();

if ($eventResult->num_rows !== 1) {
    header("Location: events.php");
    exit;
}

$event = $eventResult->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>View Event | College CMS</title>

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
        href="../assets/css/admin.css?v=114"
    >

</head>

<body>

<div class="admin-layout">

    <?php include "../includes/admin-sidebar.php"; ?>

    <div class="main-area">

        <?php include "../includes/admin-header.php"; ?>

        <div class="dashboard-content">

            <!-- PAGE HEADING -->

            <div
                class="d-flex justify-content-between
                align-items-center mb-4"
            >

                <div>

                    <h2 class="mb-1">
                        Event Details
                    </h2>

                    <p class="text-muted mb-0">
                        View complete event information.
                    </p>

                </div>

                <a
                    href="events.php"
                    class="btn btn-light border"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Events
                </a>

            </div>


            <!-- EVENT CARD -->

            <div class="dashboard-card event-veiw-card">

                <div class="p-4">

                    <div
                        class="d-flex justify-content-between
                        align-items-start mb-4"
                    >

                        <div>

                            <h3 class="mb-2">
                                <?php
                                echo htmlspecialchars(
                                    $event["title"]
                                );
                                ?>
                            </h3>

                            <span class="badge bg-primary-subtle text-primary">
                                <?php
                                echo htmlspecialchars(
                                    $event["event_type"]
                                );
                                ?>
                            </span>

                        </div>


                        <?php if ((int)$event["status"] === 1): ?>

                            <span
                                class="badge bg-success-subtle
                                text-success px-3 py-2"
                            >
                                <i class="bi bi-check-circle me-1"></i>
                                Active
                            </span>

                        <?php else: ?>

                            <span
                                class="badge bg-danger-subtle
                                text-danger px-3 py-2"
                            >
                                <i class="bi bi-x-circle me-1"></i>
                                Inactive
                            </span>

                        <?php endif; ?>

                    </div>


                    <hr>


                    <div class="row g-4 mt-1">

                        <!-- DATE -->

                        <div class="col-md-6">

                            <div class="d-flex align-items-center gap-3">

                                <div class="event-detail-icon">
                                    <i class="bi bi-calendar-event"></i>
                                </div>

                                <div>

                                    <small class="text-muted">
                                        Event Date
                                    </small>

                                    <h6 class="mb-0 mt-1">

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $event["event_date"]
                                            )
                                        );
                                        ?>

                                    </h6>

                                </div>

                            </div>

                        </div>


                        <!-- TIME -->

                        <div class="col-md-6">

                            <div class="d-flex align-items-center gap-3">

                                <div class="event-detail-icon">
                                    <i class="bi bi-clock"></i>
                                </div>

                                <div>

                                    <small class="text-muted">
                                        Event Time
                                    </small>

                                    <h6 class="mb-0 mt-1">

                                        <?php
                                        echo !empty(
                                            $event["event_time"]
                                        )
                                            ? date(
                                                "h:i A",
                                                strtotime(
                                                    $event["event_time"]
                                                )
                                            )
                                            : "Not specified";
                                        ?>

                                    </h6>

                                </div>

                            </div>

                        </div>


                        <!-- TYPE -->

                        <div class="col-md-6">

                            <div class="d-flex align-items-center gap-3">

                                <div class="event-detail-icon">
                                    <i class="bi bi-tag"></i>
                                </div>

                                <div>

                                    <small class="text-muted">
                                        Event Type
                                    </small>

                                    <h6 class="mb-0 mt-1">

                                        <?php
                                        echo htmlspecialchars(
                                            $event["event_type"]
                                        );
                                        ?>

                                    </h6>

                                </div>

                            </div>

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-6">

                            <div class="d-flex align-items-center gap-3">

                                <div class="event-detail-icon">
                                    <i class="bi bi-activity"></i>
                                </div>

                                <div>

                                    <small class="text-muted">
                                        Status
                                    </small>

                                    <h6 class="mb-0 mt-1">

                                        <?php
                                        echo (int)$event["status"] === 1
                                            ? "Active"
                                            : "Inactive";
                                        ?>

                                    </h6>

                                </div>

                            </div>

                        </div>

                    </div>


                    <hr class="my-4">


                    <!-- DESCRIPTION -->

                    <div>

                        <h5 class="mb-2">
                            <i class="bi bi-card-text me-2"></i>
                            Description
                        </h5>

                        <p class="text-muted mb-0">

                            <?php
                            if (!empty($event["description"])) {

                                echo nl2br(
                                    htmlspecialchars(
                                        $event["description"]
                                    )
                                );

                            } else {

                                echo "No description available.";
                            }
                            ?>

                        </p>

                    </div>


                    <hr class="my-4">


                    <!-- ACTION -->

                    <div class="d-flex gap-2">

                        <a
                            href="edit-event.php?id=<?php
                            echo (int)$event["id"];
                            ?>"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-pencil-square me-1"></i>
                            Edit Event
                        </a>

                        <a
                            href="events.php"
                            class="btn btn-light border"
                        >
                            Back
                        </a>

                    </div>

                </div>

            </div>

        </div>

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