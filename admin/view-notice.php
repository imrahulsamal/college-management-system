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

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>View Notice | College CMS</title>

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

                    <h2>Notice Details</h2>

                    <p>
                        View complete notice information.
                    </p>

                </div>

                <div>

                    <a
                        href="edit-notice.php?id=<?php echo (int)$notice["id"]; ?>"
                        class="btn btn-warning"
                    >
                        <i class="bi bi-pencil"></i>
                        Edit
                    </a>

                    <a
                        href="notices.php"
                        class="btn btn-outline-secondary ms-2"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>

                </div>

            </div>


            <div class="dashboard-card">

                <div class="row g-4">


                    <div class="col-md-8">

                        <label class="form-label text-muted">
                            Notice Title
                        </label>

                        <h4>
                            <?php
                            echo htmlspecialchars(
                                $notice["title"]
                            );
                            ?>
                        </h4>

                    </div>


                    <div class="col-md-4">

                        <label class="form-label text-muted">
                            Notice Date
                        </label>

                        <p class="fw-semibold mb-0">

                            <?php
                            echo date(
                                "d M Y",
                                strtotime(
                                    $notice["notice_date"]
                                )
                            );
                            ?>

                        </p>

                    </div>


                    <div class="col-md-12">

                        <hr>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label text-muted">
                            Audience
                        </label>

                        <p>

                            <span class="badge bg-light text-dark">

                                <?php
                                echo htmlspecialchars(
                                    $notice["audience"]
                                );
                                ?>

                            </span>

                        </p>

                    </div>


                    <div class="col-md-6">

                        <label class="form-label text-muted">
                            Status
                        </label>

                        <p>

                            <?php if (
                                (int)$notice["status"] === 1
                            ): ?>

                                <span
                                    class="student-status active-status"
                                >
                                    Active
                                </span>

                            <?php else: ?>

                                <span
                                    class="student-status inactive-status"
                                >
                                    Inactive
                                </span>

                            <?php endif; ?>

                        </p>

                    </div>


                    <div class="col-md-12">

                        <label class="form-label text-muted">
                            Description
                        </label>

                        <div
                            class="p-3 border rounded bg-light"
                            style="white-space: pre-wrap;"
                        ><?php
                        echo htmlspecialchars(
                            $notice["description"]
                        );
                        ?></div>

                    </div>


                    <div class="col-md-12">

                        <label class="form-label text-muted">
                            Created At
                        </label>

                        <p class="mb-0">

                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime(
                                    $notice["created_at"]
                                )
                            );
                            ?>

                        </p>

                    </div>


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

</body>

</html>