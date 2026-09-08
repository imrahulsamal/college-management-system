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

$message = "";
$messageType = "";

// Load active students
$students = $conn->query("
    SELECT id, admission_no, first_name, last_name
    FROM students
    WHERE status = 1
    ORDER BY first_name ASC, last_name ASC
");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int) ($_POST["student_id"] ?? 0);
    $fee_type = trim($_POST["fee_type"] ?? "");
    $total_amount = (float) ($_POST["total_amount"] ?? 0);
    $paid_amount = (float) ($_POST["paid_amount"] ?? 0);

    $payment_method = trim($_POST["payment_method"] ?? "");
    $payment_date = trim($_POST["payment_date"] ?? "");
    $remarks = trim($_POST["remarks"] ?? "");

    if (
        $student_id <= 0 ||
        $fee_type === "" ||
        $total_amount <= 0 ||
        $paid_amount < 0
    ) {

        $message = "Please fill all required fields correctly.";
        $messageType = "danger";

    } elseif ($paid_amount > $total_amount) {

        $message = "Paid amount cannot be greater than total amount.";
        $messageType = "danger";

    } else {

        // Calculate due amount
        $due_amount = $total_amount - $paid_amount;

        // Calculate payment status
        if ($paid_amount <= 0) {

            $payment_status = "Unpaid";

        } elseif ($paid_amount < $total_amount) {

            $payment_status = "Partial";

        } else {

            $payment_status = "Paid";
        }


        // If unpaid, payment details should be empty
        if ($payment_status === "Unpaid") {
            $payment_method = "";
            $payment_date = "";
        }


        // Convert empty values to NULL
        $payment_method_db =
            $payment_method !== ""
                ? $payment_method
                : null;

        $payment_date_db =
            $payment_date !== ""
                ? $payment_date
                : null;


        $stmt = $conn->prepare("
            INSERT INTO fees
            (
                student_id,
                fee_type,
                total_amount,
                paid_amount,
                due_amount,
                payment_status,
                payment_method,
                payment_date,
                remarks
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "isdddssss",
            $student_id,
            $fee_type,
            $total_amount,
            $paid_amount,
            $due_amount,
            $payment_status,
            $payment_method_db,
            $payment_date_db,
            $remarks
        );

        if ($stmt->execute()) {

            $message = "Fee record added successfully.";
            $messageType = "success";

            $_POST = [];

        } else {

            $message = "Could not add fee record.";
            $messageType = "danger";
        }

        $stmt->close();
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

    <title>Add Fee | College CMS</title>

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
                    <h2>Add Fee</h2>
                    <p>Add a new student fee record.</p>
                </div>

                <a
                    href="fees.php"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Fees
                </a>

            </div>


            <?php if ($message !== ""): ?>

                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <form method="POST">

                    <div class="row g-4">


                        <!-- STUDENT -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Student *
                            </label>

                            <select
                                name="student_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Student
                                </option>

                                <?php if ($students): ?>

                                    <?php while (
                                        $student = $students->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?php echo (int)$student["id"]; ?>"
                                            <?php
                                            if (
                                                (int)($_POST["student_id"] ?? 0)
                                                ===
                                                (int)$student["id"]
                                            ) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $student["admission_no"]
                                                . " - "
                                                . $student["first_name"]
                                                . " "
                                                . $student["last_name"]
                                            );
                                            ?>
                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <!-- FEE TYPE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Fee Type *
                            </label>

                            <select
                                name="fee_type"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Fee Type
                                </option>

                                <?php
                                $feeTypes = [
                                    "Tuition Fee",
                                    "Admission Fee",
                                    "Exam Fee",
                                    "Library Fee",
                                    "Lab Fee",
                                    "Transport Fee",
                                    "Hostel Fee",
                                    "Other"
                                ];

                                foreach ($feeTypes as $type):
                                ?>

                                    <option
                                        value="<?php echo htmlspecialchars($type); ?>"
                                        <?php
                                        if (
                                            ($_POST["fee_type"] ?? "")
                                            === $type
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- TOTAL AMOUNT -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Total Amount *
                            </label>

                            <input
                                type="number"
                                name="total_amount"
                                id="total_amount"
                                class="form-control"
                                min="0"
                                step="0.01"
                                placeholder="e.g. 25000"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["total_amount"] ?? ""
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- PAID AMOUNT -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Paid Amount *
                            </label>

                            <input
                                type="number"
                                name="paid_amount"
                                id="paid_amount"
                                class="form-control"
                                min="0"
                                step="0.01"
                                placeholder="e.g. 10000"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["paid_amount"] ?? "0"
                                    );
                                ?>"
                                required
                            >

                        </div>


                        <!-- AUTO DUE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Due Amount
                            </label>

                            <input
                                type="text"
                                id="due_amount"
                                class="form-control"
                                value="0.00"
                                readonly
                            >

                        </div>


                        <!-- AUTO STATUS -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Payment Status
                            </label>

                            <input
                                type="text"
                                id="payment_status"
                                class="form-control"
                                value="Unpaid"
                                readonly
                            >

                        </div>


                        <!-- PAYMENT METHOD -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Payment Method
                            </label>

                            <select
                                name="payment_method"
                                class="form-select"
                            >

                                <option value="">
                                    Select Payment Method
                                </option>

                                <?php
                                $methods = [
                                    "Cash",
                                    "Card",
                                    "UPI",
                                    "Bank Transfer",
                                    "Other"
                                ];

                                foreach ($methods as $method):
                                ?>

                                    <option
                                        value="<?php echo $method; ?>"
                                        <?php
                                        if (
                                            ($_POST["payment_method"] ?? "")
                                            === $method
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        <?php echo $method; ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- PAYMENT DATE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Payment Date
                            </label>

                            <input
                                type="date"
                                name="payment_date"
                                class="form-control"
                                value="<?php
                                    echo htmlspecialchars(
                                        $_POST["payment_date"] ?? ""
                                    );
                                ?>"
                            >

                        </div>


                        <!-- REMARKS -->

                        <div class="col-12">

                            <label class="form-label">
                                Remarks
                            </label>

                            <textarea
                                name="remarks"
                                class="form-control"
                                rows="4"
                                placeholder="Optional remarks..."
                            ><?php
                                echo htmlspecialchars(
                                    $_POST["remarks"] ?? ""
                                );
                            ?></textarea>

                        </div>


                        <!-- BUTTON -->

                        <div class="col-12">

                            <button
                                type="submit"
                                class="btn primary-action-btn"
                            >
                                <i class="bi bi-check-circle"></i>
                                Save Fee
                            </button>

                            <a
                                href="fees.php"
                                class="btn btn-light ms-2"
                            >
                                Cancel
                            </a>

                        </div>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>


<script src="../assets/js/admin.js"></script>

<script>

const totalInput = document.getElementById("total_amount");
const paidInput = document.getElementById("paid_amount");

const dueInput = document.getElementById("due_amount");
const statusInput = document.getElementById("payment_status");


function calculateFee() {

    const total = parseFloat(totalInput.value) || 0;
    const paid = parseFloat(paidInput.value) || 0;

    let due = total - paid;

    if (due < 0) {
        due = 0;
    }

    dueInput.value = due.toFixed(2);


    if (paid <= 0) {

        statusInput.value = "Unpaid";

    } else if (paid < total) {

        statusInput.value = "Partial";

    } else {

        statusInput.value = "Paid";
    }
}


totalInput.addEventListener("input", calculateFee);
paidInput.addEventListener("input", calculateFee);

calculateFee();

</script>

</body>
</html>