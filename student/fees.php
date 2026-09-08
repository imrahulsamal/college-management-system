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


/* Student Fee Records */

$stmt = $conn->prepare("
    SELECT *
    FROM fees
    WHERE student_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$fees = $stmt->get_result();


/* Fee Summary */

$summaryStmt = $conn->prepare("
    SELECT
        COALESCE(SUM(total_amount), 0) AS total_fee,
        COALESCE(SUM(paid_amount), 0) AS total_paid,
        COALESCE(SUM(due_amount), 0) AS total_due
    FROM fees
    WHERE student_id = ?
");

$summaryStmt->bind_param("i", $studentId);
$summaryStmt->execute();

$summary = $summaryStmt->get_result()->fetch_assoc();

$totalFee = $summary["total_fee"] ?? 0;
$totalPaid = $summary["total_paid"] ?? 0;
$totalDue = $summary["total_due"] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Fees | College CMS</title>

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
        href="../assets/css/admin.css?v=8"
    >

</head>

<body class="student-panel">

<?php include "../includes/student-sidebar.php"; ?>

<div class="main-area">

    <?php include "../includes/student-header.php"; ?>

    <main class="content-area">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h3>My Fees</h3>

                <p class="text-muted mb-0">
                    View your fee details and payment status.
                </p>

            </div>

            <a
                href="pay-fee.php"
                class="btn btn-success"
            >
                <i class="bi bi-credit-card me-1"></i>
                Pay Fees
            </a>

        </div>


        <!-- Fee Summary -->

        <div class="row g-4 mb-4">

            <div class="col-lg-4 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon students-icon">
                        <i class="bi bi-wallet-fill"></i>
                    </div>

                    <div class="stat-content">

                        <span>Total Fee</span>

                        <h3>
                            ₹<?php echo number_format($totalFee, 2); ?>
                        </h3>

                        <p>Total assigned fee</p>

                    </div>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon faculty-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>

                    <div class="stat-content">

                        <span>Paid Amount</span>

                        <h3>
                            ₹<?php echo number_format($totalPaid, 2); ?>
                        </h3>

                        <p>Total paid amount</p>

                    </div>

                </div>

            </div>


            <div class="col-lg-4 col-md-6">

                <div class="stat-card">

                    <div class="stat-icon department-icon">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>

                    <div class="stat-content">

                        <span>Due Amount</span>

                        <h3>
                            ₹<?php echo number_format($totalDue, 2); ?>
                        </h3>

                        <p>Outstanding balance</p>

                    </div>

                </div>

            </div>

        </div>


        <!-- Fee Records -->

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <h5 class="mb-4">

                    <i class="bi bi-receipt me-2"></i>

                    Fee History

                </h5>


                <?php if ($fees->num_rows > 0): ?>

                    <div class="table student-fees-table">

                        <table class="table align-middle">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Fee Type</th>
                                    <th>Total</th>
                                    <th>Paid</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                    <th>Method</th>
                                    <th>Payment Date</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php $count = 1; ?>

                            <?php while (
                                $row = $fees->fetch_assoc()
                            ): ?>

                                <tr>

                                    <td>
                                        <?php echo $count++; ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $row["fee_type"]
                                            );
                                            ?>
                                        </strong>

                                    </td>

                                    <td>
                                        ₹<?php
                                        echo number_format(
                                            $row["total_amount"],
                                            2
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        ₹<?php
                                        echo number_format(
                                            $row["paid_amount"],
                                            2
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        ₹<?php
                                        echo number_format(
                                            $row["due_amount"],
                                            2
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <?php if (
                                            $row["payment_status"] === "Paid"
                                        ): ?>

                                            <span class="badge bg-success">
                                                Paid
                                            </span>

                                        <?php elseif (
                                            $row["payment_status"] === "Partial"
                                        ): ?>

                                            <span class="badge bg-warning text-dark">
                                                Partial
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-danger">
                                                Unpaid
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["payment_method"] ?: "-"
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <?php if (
                                            !empty($row["payment_date"])
                                        ): ?>

                                            <?php
                                            echo date(
                                                "d M Y",
                                                strtotime(
                                                    $row["payment_date"]
                                                )
                                            );
                                            ?>

                                        <?php else: ?>

                                            -

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
                            class="bi bi-wallet2"
                            style="font-size: 32px;"
                        ></i>

                        <p class="mt-3 mb-0">
                            No fee records available.
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