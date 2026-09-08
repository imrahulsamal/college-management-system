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

$search = trim($_GET["search"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");

$sql = "
    SELECT
        fees.*,
        students.admission_no,
        students.first_name,
        students.last_name
    FROM fees
    INNER JOIN students
        ON fees.student_id = students.id
    WHERE 1 = 1
";

$params = [];
$types = "";


/* SEARCH */

if ($search !== "") {

    $sql .= "
        AND (
            students.admission_no LIKE ?
            OR students.first_name LIKE ?
            OR students.last_name LIKE ?
            OR fees.fee_type LIKE ?
        )
    ";

    $likeSearch = "%" . $search . "%";

    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;

    $types .= "ssss";
}


/* STATUS FILTER */

if (
    in_array(
        $statusFilter,
        ["Paid", "Partial", "Unpaid"],
        true
    )
) {

    $sql .= " AND fees.payment_status = ?";

    $params[] = $statusFilter;
    $types .= "s";
}


$sql .= " ORDER BY fees.id DESC";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Query prepare failed: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Fees | College CMS</title>

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

                    <h2>Fees Management</h2>

                    <p>
                        Manage student fees and payment records.
                    </p>

                </div>


                <a
                    href="add-fee.php"
                    class="btn primary-action-btn"
                >

                    <i class="bi bi-plus-circle"></i>

                    Add Fee

                </a>

            </div>


            <!-- DELETE SUCCESS -->

            <?php if (
                isset($_GET["deleted"]) &&
                $_GET["deleted"] === "1"
            ): ?>

                <div class="alert alert-success">

                    <i class="bi bi-check-circle me-1"></i>

                    Fee record deleted successfully.

                </div>

            <?php endif; ?>


            <div class="dashboard-card">


                <!-- FILTERS -->

                <form
                    method="GET"
                    action="fees.php"
                    class="row g-3 mb-4"
                >


                    <div class="col-md-6">

                        <label class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Student, admission no. or fee type..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Payment Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All Status
                            </option>

                            <option
                                value="Paid"
                                <?php
                                if ($statusFilter === "Paid") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Paid
                            </option>

                            <option
                                value="Partial"
                                <?php
                                if ($statusFilter === "Partial") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Partial
                            </option>

                            <option
                                value="Unpaid"
                                <?php
                                if ($statusFilter === "Unpaid") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Unpaid
                            </option>

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label d-block">
                            &nbsp;
                        </label>

                        <button
                            type="submit"
                            class="btn btn-dark"
                        >
                            <i class="bi bi-funnel"></i>
                            Filter
                        </button>

                        <a
                            href="fees.php"
                            class="btn btn-outline-secondary"
                        >
                            Clear
                        </a>

                    </div>

                </form>


                <!-- TABLE -->

                <div class="table-responsive">

                    <table class="table align-middle student-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Student</th>

                                <th>Fee Type</th>

                                <th>Total</th>

                                <th>Paid</th>

                                <th>Due</th>

                                <th>Status</th>

                                <th>Payment Date</th>

                                <th class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (
                            $result &&
                            $result->num_rows > 0
                        ): ?>


                            <?php

                            $number = 1;

                            while (
                                $fee = $result->fetch_assoc()
                            ):

                            ?>


                                <tr>


                                    <td>
                                        <?php echo $number++; ?>
                                    </td>


                                    <!-- STUDENT -->

                                    <td>

                                        <div class="student-info">

                                            <div class="student-avatar">

                                                <?php

                                                echo strtoupper(
                                                    substr(
                                                        $fee["first_name"],
                                                        0,
                                                        1
                                                    )
                                                );

                                                ?>

                                            </div>


                                            <div>

                                                <strong>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $fee["first_name"]
                                                        . " "
                                                        . $fee["last_name"]
                                                    );

                                                    ?>

                                                </strong>


                                                <small>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $fee["admission_no"]
                                                    );

                                                    ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- FEE TYPE -->

                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $fee["fee_type"]
                                        );

                                        ?>

                                    </td>


                                    <!-- TOTAL -->

                                    <td>

                                        ₹<?php
                                        echo number_format(
                                            (float)$fee["total_amount"],
                                            2
                                        );
                                        ?>

                                    </td>


                                    <!-- PAID -->

                                    <td>

                                        ₹<?php
                                        echo number_format(
                                            (float)$fee["paid_amount"],
                                            2
                                        );
                                        ?>

                                    </td>


                                    <!-- DUE -->

                                    <td>

                                        ₹<?php
                                        echo number_format(
                                            (float)$fee["due_amount"],
                                            2
                                        );
                                        ?>

                                    </td>


                                    <!-- STATUS -->

                                    <td>


                                        <?php if (
                                            $fee["payment_status"] === "Paid"
                                        ): ?>

                                            <span
                                                class="student-status active-status"
                                            >
                                                Paid
                                            </span>


                                        <?php elseif (
                                            $fee["payment_status"] === "Partial"
                                        ): ?>

                                            <span
                                                class="badge bg-warning text-dark"
                                            >
                                                Partial
                                            </span>


                                        <?php else: ?>

                                            <span
                                                class="student-status inactive-status"
                                            >
                                                Unpaid
                                            </span>

                                        <?php endif; ?>


                                    </td>


                                    <!-- PAYMENT DATE -->

                                    <td>

                                        <?php if (
                                            !empty($fee["payment_date"])
                                        ): ?>

                                            <?php

                                            echo date(
                                                "d M Y",
                                                strtotime(
                                                    $fee["payment_date"]
                                                )
                                            );

                                            ?>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                -
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td class="text-end">

                                    <a
                                        href="view-fee.php?id=<?php echo (int)$fee["id"]; ?>"
                                        class="btn btn-sm btn-outline-primary"
                                        title="View Fee"
                                        >
                                        <i class="bi bi-eye"></i>
                                    </a>


                                        <!-- EDIT -->

                                        <a
                                            href="edit-fee.php?id=<?php echo (int)$fee["id"]; ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Edit Fee"
                                        >

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <!-- DELETE -->

                                        <form
                                            action="delete-fee.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this fee record?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo (int)$fee["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete Fee"
                                            >

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </form>


                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="9"
                                    class="text-center py-5"
                                >

                                    <i
                                        class="bi bi-wallet2"
                                        style="font-size: 38px;"
                                    ></i>

                                    <h6 class="mt-3">
                                        No fee records found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Add a fee record to get started.
                                    </p>

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>

                    </table>

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

<?php

$stmt->close();

?>