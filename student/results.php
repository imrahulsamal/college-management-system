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

$studentId = $_SESSION["student_id"] ?? 0;

if (!$studentId) {
    header("Location: ../logout.php");
    exit;
}


/* Student Results */

$stmt = $conn->prepare("
    SELECT
        results.*,
        subjects.subject_code,
        subjects.subject_name
    FROM results
    INNER JOIN subjects
        ON results.subject_id = subjects.id
    WHERE results.student_id = ?
    ORDER BY
        results.semester DESC,
        results.created_at DESC
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$results = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Results | College CMS</title>

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
        href="../assets/css/admin.css?v=9"
    >

</head>

<body class="student-panel">

<?php include "../includes/student-sidebar.php"; ?>

<div class="main-area">

    <?php include "../includes/student-header.php"; ?>

    <main class="content-area">

        <div class="mb-4">

            <h3>My Results</h3>

            <p class="text-muted mb-0">
                View your examination results and grades.
            </p>

        </div>


        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <h5 class="mb-4">

                    <i class="bi bi-bar-chart-fill me-2"></i>

                    Examination Results

                </h5>


                <?php if ($results->num_rows > 0): ?>

                    <div class="table student-results-table">

                        <table class="table align-middle">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Semester</th>
                                    <th>Exam</th>
                                    <th>Marks</th>
                                    <th>Percentage</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php $count = 1; ?>

                            <?php while (
                                $row = $results->fetch_assoc()
                            ): ?>

                                <?php

                                $percentage =
                                    $row["total_marks"] > 0
                                    ? round(
                                        (
                                            $row["obtained_marks"] /
                                            $row["total_marks"]
                                        ) * 100,
                                        1
                                    )
                                    : 0;

                                ?>

                                <tr>

                                    <td>
                                        <?php echo $count++; ?>
                                    </td>


                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $row["subject_name"]
                                            );
                                            ?>

                                        </strong>

                                        <small class="d-block text-muted">

                                            <?php
                                            echo htmlspecialchars(
                                                $row["subject_code"]
                                            );
                                            ?>

                                        </small>

                                    </td>


                                    <td>

                                        Semester
                                        <?php
                                        echo (int)$row["semester"];
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["exam_type"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <strong>
                                            <?php
                                            echo $row["obtained_marks"];
                                            ?>
                                        </strong>

                                        /

                                        <?php
                                        echo $row["total_marks"];
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo $percentage;
                                        ?>%

                                    </td>


                                    <td>

                                        <span class="badge bg-primary">

                                            <?php
                                            echo htmlspecialchars(
                                                $row["grade"] ?: "-"
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?php if (
                                            $row["result_status"] === "Pass"
                                        ): ?>

                                            <span class="badge bg-success">
                                                Pass
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-danger">
                                                Fail
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5 text-muted">

                        <i
                            class="bi bi-bar-chart"
                            style="font-size: 32px;"
                        ></i>

                        <p class="mt-3 mb-0">
                            No examination results available.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

<script src="../assets/js/admin.js"></script>

</body>
</html>