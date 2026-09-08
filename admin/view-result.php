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

$resultId = (int) ($_GET["id"] ?? 0);

if ($resultId <= 0) {
    header("Location: results.php");
    exit;
}


/* LOAD RESULT DETAILS */

$stmt = $conn->prepare("
    SELECT
        results.*,
        students.admission_no,
        students.first_name,
        students.last_name,
        students.email,
        students.phone,
        students.course,
        subjects.subject_code,
        subjects.subject_name
    FROM results
    INNER JOIN students
        ON results.student_id = students.id
    INNER JOIN subjects
        ON results.subject_id = subjects.id
    WHERE results.id = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $resultId);
$stmt->execute();

$queryResult = $stmt->get_result();
$row = $queryResult->fetch_assoc();

$stmt->close();

if (!$row) {
    header("Location: results.php");
    exit;
}


/* CALCULATE PERCENTAGE */

$percentage = 0;

if ((float)$row["total_marks"] > 0) {
    $percentage =
        ((float)$row["obtained_marks"] /
        (float)$row["total_marks"]) * 100;
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

    <title>View Result | College CMS</title>

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


            <!-- PAGE HEADING -->

            <div class="page-heading">

                <div>

                    <h2>Result Details</h2>

                    <p>
                        View complete student examination result.
                    </p>

                </div>

                <div>

                    <a
                        href="edit-result.php?id=<?php echo $resultId; ?>"
                        class="btn primary-action-btn"
                    >
                        <i class="bi bi-pencil"></i>
                        Edit Result
                    </a>

                    <a
                        href="results.php"
                        class="btn btn-outline-secondary ms-2"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>

                </div>

            </div>


            <!-- STUDENT INFORMATION -->

            <div class="dashboard-card mb-4">

                <h5 class="mb-4">

                    <i class="bi bi-person-circle me-2"></i>

                    Student Information

                </h5>


                <div class="row g-4">

                    <div class="col-md-4">

                        <small class="text-muted">
                            Student Name
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo htmlspecialchars(
                                $row["first_name"]
                                . " "
                                . $row["last_name"]
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Admission No.
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo htmlspecialchars(
                                $row["admission_no"]
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Course
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo htmlspecialchars(
                                $row["course"]
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Email
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo !empty($row["email"])
                                ? htmlspecialchars($row["email"])
                                : "-";
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Phone
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo !empty($row["phone"])
                                ? htmlspecialchars($row["phone"])
                                : "-";
                            ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- RESULT INFORMATION -->

            <div class="dashboard-card">

                <h5 class="mb-4">

                    <i class="bi bi-bar-chart me-2"></i>

                    Result Information

                </h5>


                <div class="row g-4">


                    <div class="col-md-4">

                        <small class="text-muted">
                            Subject
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo htmlspecialchars(
                                $row["subject_name"]
                            );
                            ?>

                        </div>

                        <small class="text-muted">

                            <?php
                            echo htmlspecialchars(
                                $row["subject_code"]
                            );
                            ?>

                        </small>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Semester
                        </small>

                        <div class="fw-semibold mt-1">

                            Semester
                            <?php echo (int)$row["semester"]; ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Exam Type
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo htmlspecialchars(
                                $row["exam_type"]
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Total Marks
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo number_format(
                                (float)$row["total_marks"],
                                2
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Obtained Marks
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo number_format(
                                (float)$row["obtained_marks"],
                                2
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Percentage
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo number_format(
                                $percentage,
                                2
                            );
                            ?>%

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Grade
                        </small>

                        <div class="mt-2">

                            <span class="badge bg-secondary">

                                <?php
                                echo htmlspecialchars(
                                    $row["grade"]
                                );
                                ?>

                            </span>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted d-block mb-2">
                            Result Status
                        </small>

                        <?php if (
                            $row["result_status"] === "Pass"
                        ): ?>

                            <span
                                class="student-status active-status"
                            >
                                Pass
                            </span>

                        <?php else: ?>

                            <span
                                class="student-status inactive-status"
                            >
                                Fail
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Created At
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime(
                                    $row["created_at"]
                                )
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-12">

                        <small class="text-muted">
                            Remarks
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo !empty($row["remarks"])
                                ? nl2br(
                                    htmlspecialchars(
                                        $row["remarks"]
                                    )
                                )
                                : "-";
                            ?>

                        </div>

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