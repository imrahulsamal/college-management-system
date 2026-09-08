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

$message = "";
$error = "";


/* Get Student */

$studentStmt = $conn->prepare("
    SELECT *
    FROM students
    WHERE id = ?
    LIMIT 1
");

$studentStmt->bind_param("i", $studentId);
$studentStmt->execute();

$student = $studentStmt->get_result()->fetch_assoc();

if (!$student) {
    header("Location: ../logout.php");
    exit;
}


/* Get Pending Fees */

$feeStmt = $conn->prepare("
    SELECT *
    FROM fees
    WHERE student_id = ?
      AND due_amount > 0
    ORDER BY created_at DESC
");

$feeStmt->bind_param("i", $studentId);
$feeStmt->execute();

$pendingFees = $feeStmt->get_result();


/* Process Payment */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $feeId = (int)($_POST["fee_id"] ?? 0);
    $amount = (float)($_POST["amount"] ?? 0);
    $paymentMethod = trim(
        $_POST["payment_method"] ?? ""
    );

    if (
        $feeId <= 0 ||
        $amount <= 0 ||
        $paymentMethod === ""
    ) {

        $error = "Please fill all payment details.";

    } else {

        /*
         * Important:
         * Fee must belong to logged-in student.
         */

        $checkStmt = $conn->prepare("
            SELECT *
            FROM fees
            WHERE id = ?
              AND student_id = ?
            LIMIT 1
        ");

        $checkStmt->bind_param(
            "ii",
            $feeId,
            $studentId
        );

        $checkStmt->execute();

        $fee = $checkStmt
            ->get_result()
            ->fetch_assoc();


        if (!$fee) {

            $error = "Invalid fee record.";

        } elseif ($amount > $fee["due_amount"]) {

            $error =
                "Payment amount cannot be greater than due amount.";

        } else {

            $newPaid =
                (float)$fee["paid_amount"] + $amount;

            $newDue =
                (float)$fee["total_amount"] - $newPaid;

            if ($newDue <= 0) {

                $newDue = 0;
                $paymentStatus = "Paid";

            } else {

                $paymentStatus = "Partial";
            }


            $updateStmt = $conn->prepare("
                UPDATE fees
                SET
                    paid_amount = ?,
                    due_amount = ?,
                    payment_status = ?,
                    payment_method = ?,
                    payment_date = CURDATE()
                WHERE id = ?
                  AND student_id = ?
            ");

            $updateStmt->bind_param(
                "ddssii",
                $newPaid,
                $newDue,
                $paymentStatus,
                $paymentMethod,
                $feeId,
                $studentId
            );


            if ($updateStmt->execute()) {

                $message =
                    "Payment recorded successfully.";

            } else {

                $error =
                    "Unable to record payment.";
            }
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

    <title>Pay Fees | College CMS</title>

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

        <div class="mb-4">

            <h3>Pay Fees</h3>

            <p class="text-muted mb-0">
                Submit payment against your pending college fees.
            </p>

        </div>


        <?php if ($message !== ""): ?>

            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>

                <?php
                echo htmlspecialchars($message);
                ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>

                <?php
                echo htmlspecialchars($error);
                ?>
            </div>

        <?php endif; ?>


        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

                <h5 class="mb-4">

                    <i class="bi bi-credit-card me-2"></i>

                    Payment Details

                </h5>


                <?php if ($pendingFees->num_rows > 0): ?>

                    <form method="POST">

                        <div class="row g-4">


                            <!-- Fee -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    Select Fee
                                </label>

                                <select
                                    name="fee_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Select Pending Fee
                                    </option>

                                    <?php while (
                                        $fee = $pendingFees->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$fee["id"]; ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $fee["fee_type"]
                                            );
                                            ?>

                                            -

                                            Due ₹<?php
                                            echo number_format(
                                                $fee["due_amount"],
                                                2
                                            );
                                            ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>


                            <!-- Amount -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    Payment Amount (₹)
                                </label>

                                <input
                                    type="number"
                                    name="amount"
                                    class="form-control"
                                    min="0.01"
                                    step="0.01"
                                    placeholder="Enter amount"
                                    required
                                >

                            </div>


                            <!-- Method -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    Payment Method
                                </label>

                                <select
                                    name="payment_method"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Select Method
                                    </option>

                                    <option value="UPI">
                                        UPI
                                    </option>

                                    <option value="Card">
                                        Card
                                    </option>

                                    <option value="Bank Transfer">
                                        Bank Transfer
                                    </option>

                                    <option value="Cash">
                                        Cash
                                    </option>

                                    <option value="Other">
                                        Other
                                    </option>

                                </select>

                            </div>


                            <!-- Student -->

                            <div class="col-md-6">

                                <label class="form-label">
                                    Student
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $student["first_name"] .
                                        " " .
                                        $student["last_name"]
                                    );
                                    ?>"
                                    readonly
                                >

                            </div>


                            <div class="col-12">

                                <div class="alert alert-warning mb-0">

                                    <i class="bi bi-info-circle me-2"></i>

                                    This currently records the payment
                                    inside the College CMS. It does not
                                    charge a real card, bank account or UPI.

                                </div>

                            </div>


                            <div class="col-12">

                                <button
                                    type="submit"
                                    class="btn btn-success"
                                >

                                    <i class="bi bi-credit-card me-1"></i>

                                    Submit Payment

                                </button>

                                <a
                                    href="fees.php"
                                    class="btn btn-light ms-2"
                                >
                                    Back to Fees
                                </a>

                            </div>

                        </div>

                    </form>


                <?php else: ?>

                    <div class="text-center py-5">

                        <i
                            class="bi bi-check-circle text-success"
                            style="font-size: 42px;"
                        ></i>

                        <h5 class="mt-3">
                            No Pending Fees
                        </h5>

                        <p class="text-muted">
                            You currently have no outstanding fee amount.
                        </p>

                        <a
                            href="fees.php"
                            class="btn btn-primary"
                        >
                            View Fee History
                        </a>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

<script src="../assets/js/admin.js"></script>

</body>
</html>