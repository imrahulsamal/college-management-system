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

$feeId = (int) ($_GET["id"] ?? 0);

if ($feeId <= 0) {
    header("Location: fees.php");
    exit;
}


/* LOAD FEE DETAILS */

$stmt = $conn->prepare("
    SELECT
        fees.*,
        students.admission_no,
        students.first_name,
        students.last_name,
        students.email,
        students.phone,
        students.course,
        students.semester
    FROM fees
    INNER JOIN students
        ON fees.student_id = students.id
    WHERE fees.id = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $feeId);
$stmt->execute();

$result = $stmt->get_result();
$fee = $result->fetch_assoc();

$stmt->close();

if (!$fee) {
    header("Location: fees.php");
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

    <title>View Fee | College CMS</title>

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

                    <h2>Fee Details</h2>

                    <p>
                        View complete student fee information.
                    </p>

                </div>


                <div>

                    <a
                        href="edit-fee.php?id=<?php echo $feeId; ?>"
                        class="btn primary-action-btn"
                    >
                        <i class="bi bi-pencil"></i>
                        Edit Fee
                    </a>

                    <a
                        href="fees.php"
                        class="btn btn-outline-secondary ms-2"
                    >
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>

                </div>

            </div>


            <!-- STUDENT DETAILS -->

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
                                $fee["first_name"]
                                . " "
                                . $fee["last_name"]
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
                                $fee["admission_no"]
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
                                $fee["course"]
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Semester
                        </small>

                        <div class="fw-semibold mt-1">

                            Semester
                            <?php
                            echo (int)$fee["semester"];
                            ?>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <small class="text-muted">
                            Email
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo !empty($fee["email"])
                                ? htmlspecialchars($fee["email"])
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
                            echo !empty($fee["phone"])
                                ? htmlspecialchars($fee["phone"])
                                : "-";
                            ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- FEE DETAILS -->

            <div class="dashboard-card">

                <h5 class="mb-4">
                    <i class="bi bi-wallet2 me-2"></i>
                    Payment Information
                </h5>


                <div class="row g-4">


                    <!-- FEE TYPE -->

                    <div class="col-md-4">

                        <small class="text-muted">
                            Fee Type
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo htmlspecialchars(
                                $fee["fee_type"]
                            );
                            ?>

                        </div>

                    </div>


                    <!-- TOTAL -->

                    <div class="col-md-4">

                        <small class="text-muted">
                            Total Amount
                        </small>

                        <div class="fw-semibold mt-1">

                            ₹<?php
                            echo number_format(
                                (float)$fee["total_amount"],
                                2
                            );
                            ?>

                        </div>

                    </div>


                    <!-- PAID -->

                    <div class="col-md-4">

                        <small class="text-muted">
                            Paid Amount
                        </small>

                        <div class="fw-semibold mt-1">

                            ₹<?php
                            echo number_format(
                                (float)$fee["paid_amount"],
                                2
                            );
                            ?>

                        </div>

                    </div>


                    <!-- DUE -->

                    <div class="col-md-4">

                        <small class="text-muted">
                            Due Amount
                        </small>

                        <div class="fw-semibold mt-1">

                            ₹<?php
                            echo number_format(
                                (float)$fee["due_amount"],
                                2
                            );
                            ?>

                        </div>

                    </div>


                    <!-- STATUS -->

                    <div class="col-md-4">

                        <small class="text-muted d-block mb-2">
                            Payment Status
                        </small>


                        <?php if (
                            $fee["payment_status"] === "Paid"
                        ): ?>

                            <span class="student-status active-status">
                                Paid
                            </span>


                        <?php elseif (
                            $fee["payment_status"] === "Partial"
                        ): ?>

                            <span class="badge bg-warning text-dark">
                                Partial
                            </span>


                        <?php else: ?>

                            <span class="student-status inactive-status">
                                Unpaid
                            </span>

                        <?php endif; ?>

                    </div>


                    <!-- PAYMENT METHOD -->

                    <div class="col-md-4">

                        <small class="text-muted">
                            Payment Method
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo !empty($fee["payment_method"])
                                ? htmlspecialchars($fee["payment_method"])
                                : "-";
                            ?>

                        </div>

                    </div>


                    <!-- PAYMENT DATE -->

                    <div class="col-md-4">

                        <small class="text-muted">
                            Payment Date
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php

                            if (!empty($fee["payment_date"])) {

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $fee["payment_date"]
                                    )
                                );

                            } else {

                                echo "-";
                            }

                            ?>

                        </div>

                    </div>


                    <!-- CREATED -->

                    <div class="col-md-4">

                        <small class="text-muted">
                            Record Created
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo date(
                                "d M Y, h:i A",
                                strtotime(
                                    $fee["created_at"]
                                )
                            );
                            ?>

                        </div>

                    </div>


                    <!-- REMARKS -->

                    <div class="col-12">

                        <small class="text-muted">
                            Remarks
                        </small>

                        <div class="fw-semibold mt-1">

                            <?php
                            echo !empty($fee["remarks"])
                                ? nl2br(
                                    htmlspecialchars(
                                        $fee["remarks"]
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