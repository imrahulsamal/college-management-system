<?php

session_start();

require_once "../config/database.php";

if (
    !isset($_SESSION["faculty_id"]) ||
    ($_SESSION["user_role"] ?? "") !== "faculty"
) {
    header("Location: ../index.php");
    exit;
}

$facultyId = (int) $_SESSION["faculty_id"];

$notices = $conn->query("
    SELECT *
    FROM notices
    WHERE status = 1
    AND audience IN ('All', 'Faculty')
    ORDER BY notice_date DESC, created_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notices | College CMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="../assets/css/admin.css?v=100">

</head>

<body>

    <?php include "../includes/faculty-sidebar.php"; ?>

    <div class="main-area">

        <?php include "../includes/faculty-header.php"; ?>

        <div class="container-fluid p-4">

            <div class="mb-4">
                <h3>Notices</h3>
                <p class="text-muted mb-0">
                    View latest college announcements and notices.
                </p>
            </div>

            <?php if ($notices && $notices->num_rows > 0): ?>

            <?php while ($notice = $notices->fetch_assoc()): ?>

            <div class="card border-0 shadow-sm mb-3 notice-card">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start gap-3">

                        <div class="d-flex gap-3">

                            <div class="notice-icon">
                                <i class="bi bi-megaphone-fill"></i>
                            </div>

                            <div>
                                <h5 class="mb-1 fw-semibold">
                                    <?php
                                    echo htmlspecialchars($notice["title"]);
                                    ?>
                                </h5>

                                <span class="faculty-notice-date">
                                    <i class="bi bi-calendar3 me-1"></i>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime($notice["notice_date"])
                                    );
                                    ?>
                                </span>

                                <p class="mb-0 text-secondary">
                                    <?php
                                    echo nl2br(
                                        htmlspecialchars($notice["description"])
                                    );
                                    ?>
                                </p>
                            </div>

                        </div>

                        <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                            <?php
                            echo htmlspecialchars($notice["audience"]);
                            ?>
                        </span>

                    </div>

                </div>
            </div>

        <?php endwhile; ?>

<?php else: ?>

    <div class="alert alert-light border">
        <i class="bi bi-info-circle me-2"></i>
        No notices available.
    </div>

<?php endif; ?>

        </div>

    </div>

</body>
</html>