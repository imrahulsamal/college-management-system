<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "student"
) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";


/* Student Notices */

$stmt = $conn->prepare("
    SELECT *
    FROM notices
    WHERE status = 1
      AND audience IN ('All', 'Students')
    ORDER BY notice_date DESC, created_at DESC
");

$stmt->execute();

$notices = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Notices | College CMS</title>

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
        href="../assets/css/admin.css?v=17"
    >

</head>

<body class="student-panel">

<?php include "../includes/student-sidebar.php"; ?>

<div class="main-area">

    <?php include "../includes/student-header.php"; ?>

    <main class="content-area">

        <div class="mb-4">

            <h3>Notices</h3>

            <p class="text-muted mb-0">
                View important college announcements.
            </p>

        </div>


        <?php if ($notices->num_rows > 0): ?>

            <div class="row g-4">

                <?php while (
                    $notice = $notices->fetch_assoc()
                ): ?>

                    <div class="col-lg-6">

                        <div class="card border-0 shadow-sm h-100 student-notice-card">

                            <div class="card-body p-4">

                                <div
                                    class="d-flex justify-content-between align-items-start mb-3"
                                >

                                    <div>

                                        <h5 class="mb-1">

                                            <?php
                                            echo htmlspecialchars(
                                                $notice["title"]
                                            );
                                            ?>

                                        </h5>

                                        <small class="student-notice-date">

                                            <i class="bi bi-calendar3 me-1"></i>

                                            <?php
                                            echo date(
                                                "d M Y",
                                                strtotime(
                                                    $notice["notice_date"]
                                                )
                                            );
                                            ?>

                                        </small>

                                    </div>


                                    <?php if (
                                        $notice["audience"] === "Students"
                                    ): ?>

                                        <span class="badge bg-primary">
                                            Students
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            All
                                        </span>

                                    <?php endif; ?>

                                </div>


                                <p class="text-muted mb-0">

                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $notice["description"]
                                        )
                                    );
                                    ?>

                                </p>

                            </div>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>


        <?php else: ?>

            <div class="card border-0 shadow-sm">

                <div class="card-body text-center py-5">

                    <i
                        class="bi bi-megaphone text-muted"
                        style="font-size: 42px;"
                    ></i>

                    <h5 class="mt-3">
                        No Notices Available
                    </h5>

                    <p class="text-muted mb-0">
                        There are currently no notices for students.
                    </p>

                </div>

            </div>

        <?php endif; ?>

    </main>

</div>

<script src="../assets/js/admin.js"></script>

</body>
</html>