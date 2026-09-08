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
$dateFilter = trim($_GET["date"] ?? "");

$sql = "
    SELECT
        attendance.*,
        students.admission_no,
        students.first_name,
        students.last_name,
        subjects.subject_code,
        subjects.subject_name
    FROM attendance
    INNER JOIN students
        ON attendance.student_id = students.id
    INNER JOIN subjects
        ON attendance.subject_id = subjects.id
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= "
        AND (
            students.admission_no LIKE ?
            OR students.first_name LIKE ?
            OR students.last_name LIKE ?
            OR subjects.subject_code LIKE ?
            OR subjects.subject_name LIKE ?
        )
    ";

    $likeSearch = "%" . $search . "%";

    for ($i = 0; $i < 5; $i++) {
        $params[] = $likeSearch;
    }

    $types .= "sssss";
}

if (
    in_array(
        $statusFilter,
        ["Present", "Absent", "Leave"],
        true
    )
) {
    $sql .= " AND attendance.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if ($dateFilter !== "") {
    $sql .= " AND attendance.attendance_date = ?";
    $params[] = $dateFilter;
    $types .= "s";
}

$sql .= "
    ORDER BY attendance.attendance_date DESC,
             attendance.id DESC
";

$stmt = $conn->prepare($sql);

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

    <title>Attendance | College CMS</title>

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
                    <h2>Attendance</h2>
                    <p>Manage student attendance records.</p>
                </div>

                <a
                    href="add-attendance.php"
                    class="btn primary-action-btn"
                >
                    <i class="bi bi-calendar-check"></i>
                    Add Attendance
                </a>

            </div>


            <?php if (
                isset($_GET["deleted"]) &&
                $_GET["deleted"] === "1"
            ): ?>

                <div class="alert alert-success">
                    Attendance record deleted successfully.
                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <form
                    method="GET"
                    class="row g-3 mb-4"
                >

                    <div class="col-md-4">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search student or subject..."
                            value="<?php
                                echo htmlspecialchars($search);
                            ?>"
                        >

                    </div>


                    <div class="col-md-3">

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All Status
                            </option>

                            <option
                                value="Present"
                                <?php
                                if ($statusFilter === "Present") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Present
                            </option>

                            <option
                                value="Absent"
                                <?php
                                if ($statusFilter === "Absent") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Absent
                            </option>

                            <option
                                value="Leave"
                                <?php
                                if ($statusFilter === "Leave") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Leave
                            </option>

                        </select>

                    </div>


                    <div class="col-md-3">

                        <input
                            type="date"
                            name="date"
                            class="form-control"
                            value="<?php
                                echo htmlspecialchars($dateFilter);
                            ?>"
                        >

                    </div>


                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-dark w-100"
                        >
                            Filter
                        </button>

                    </div>


                    <?php if (
                        $search !== "" ||
                        $statusFilter !== "" ||
                        $dateFilter !== ""
                    ): ?>

                        <div class="col-12">

                            <a
                                href="attendance.php"
                                class="btn btn-sm btn-outline-secondary"
                            >
                                Clear Filters
                            </a>

                        </div>

                    <?php endif; ?>

                </form>


                <div class="table-responsive">

                    <table class="table align-middle student-table">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remarks</th>
                                <th class="text-end">Actions</th>
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
                                $attendance = $result->fetch_assoc()
                            ):
                            ?>

                                <tr>

                                    <td>
                                        <?php echo $number++; ?>
                                    </td>


                                    <td>

                                        <div class="student-info">

                                            <div class="student-avatar">
                                                <?php
                                                echo strtoupper(
                                                    substr(
                                                        $attendance["first_name"],
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
                                                        $attendance["first_name"]
                                                        . " "
                                                        . $attendance["last_name"]
                                                    );
                                                    ?>
                                                </strong>

                                                <small>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $attendance["admission_no"]
                                                    );
                                                    ?>
                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $attendance["subject_name"]
                                            );
                                            ?>
                                        </strong>

                                        <small class="d-block text-muted">
                                            <?php
                                            echo htmlspecialchars(
                                                $attendance["subject_code"]
                                            );
                                            ?>
                                        </small>

                                    </td>


                                    <td>
                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $attendance["attendance_date"]
                                            )
                                        );
                                        ?>
                                    </td>


                                    <td>

                                        <?php if (
                                            $attendance["status"] === "Present"
                                        ): ?>

                                            <span class="student-status active-status">
                                                Present
                                            </span>

                                        <?php elseif (
                                            $attendance["status"] === "Absent"
                                        ): ?>

                                            <span class="student-status inactive-status">
                                                Absent
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-warning text-dark">
                                                Leave
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $attendance["remarks"] ?: "-"
                                        );
                                        ?>
                                    </td>


                                    <td class="text-end">

                                        <a
                                            href="edit-attendance.php?id=<?php
                                                echo (int)$attendance["id"];
                                            ?>"
                                            class="btn btn-sm btn-outline-warning"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            action="delete-attendance.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete this attendance record?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php
                                                    echo (int)$attendance["id"];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
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
                                    colspan="7"
                                    class="text-center py-5"
                                >

                                    <i
                                        class="bi bi-calendar-x"
                                        style="font-size: 38px;"
                                    ></i>

                                    <h6 class="mt-3">
                                        No attendance records found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Add attendance to get started.
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