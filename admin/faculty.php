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

$departmentFilter = trim(
    $_GET["department"] ?? ""
);

$departments = $conn->query("
    SELECT DISTINCT department
    FROM faculty
    WHERE department IS NOT NULL
      AND department != ''
    ORDER BY department ASC
");

$sql = "
    SELECT *
    FROM faculty
    WHERE 1 = 1
";

$params = [];
$types = "";

if ($search !== "") {

    $sql .= "
        AND (
            employee_no LIKE ?
            OR first_name LIKE ?
            OR last_name LIKE ?
            OR email LIKE ?
            OR designation LIKE ?
        )
    ";

    $likeSearch = "%" . $search . "%";

    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;

    $types .= "sssss";
}

if ($departmentFilter !== "") {

    $sql .= "
        AND department = ?
    ";

    $params[] = $departmentFilter;
    $types .= "s";
}

$sql .= "
    ORDER BY id DESC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param(
        $types,
        ...$params
    );
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

    <title>Faculty | College CMS</title>

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
                    <h2>Faculty</h2>

                    <p>
                        Manage faculty members and teaching staff.
                    </p>
                </div>

                <a
                    href="add-faculty.php"
                    class="btn primary-action-btn"
                >
                    <i class="bi bi-person-plus-fill"></i>
                    Add Faculty
                </a>

            </div>


            <?php if (
                isset($_GET["deleted"]) &&
                $_GET["deleted"] === "1"
            ): ?>

                <div class="alert alert-success">
                    Faculty member deleted successfully.
                </div>

            <?php endif; ?>


            <div class="dashboard-card">

                <!-- Search -->

                <form
                    method="GET"
                    class="row g-3 align-items-center mb-4"
                >

                    <div class="col-md-6 col-lg-5">

                        <div class="input-group">

                            <span class="input-group-text bg-white">
                                <i class="bi bi-search"></i>
                            </span>

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search name / employee no..."
                                value="<?php
                                    echo htmlspecialchars($search);
                                ?>"
                            >

                        </div>

                    </div>

                    <div class="col-md-3 col-lg-3">

    <select
        name="department"
        class="form-select"
    >
        <option value="">All Departments</option>

        <?php while (
            $department = $departments->fetch_assoc()
        ): ?>

            <option
                value="<?php
                    echo htmlspecialchars(
                        $department["department"]
                    );
                ?>"
                <?php
                echo (
                    $departmentFilter === $department["department"]
                ) ? "selected" : "";
                ?>
            >
                <?php
                echo htmlspecialchars(
                    $department["department"]
                );
                ?>
            </option>

        <?php endwhile; ?>

    </select>

</div>

                    <div class="col-auto">

                        <button
                            type="submit"
                            class="btn btn-dark"
                        >
                            Search
                        </button>

                    </div>

                    <?php if (
                        $search !== "" ||
                        $departmentFilter !== ""
                    ): ?>

                        <div class="col-auto">

                            <a
                                href="faculty.php"
                                class="btn btn-outline-secondary"
                            >
                                Clear
                            </a>

                        </div>

                    <?php endif; ?>

                </form>


                <!-- Faculty Table -->

                <div class="table-responsive">

                    <table class="table align-middle student-table">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Faculty</th>
                                <th>Employee No.</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Phone</th>
                                <th>Status</th>

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
                                $faculty = $result->fetch_assoc()
                            ):
                            ?>

                                <tr>

                                    <td>
                                        <?php echo $number++; ?>
                                    </td>


                                    <!-- Faculty Name -->

                                    <td>

                                        <div class="student-info">

                                            <div class="student-avatar">

                                                <?php
                                                echo strtoupper(
                                                    substr(
                                                        $faculty["first_name"],
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
                                                        $faculty["first_name"] .
                                                        " " .
                                                        $faculty["last_name"]
                                                    );
                                                    ?>

                                                </strong>

                                                <small>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $faculty["email"] ?? ""
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- Employee Number -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $faculty["employee_no"]
                                        );
                                        ?>

                                    </td>


                                    <!-- Department -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $faculty["department"]
                                        );
                                        ?>

                                    </td>


                                    <!-- Designation -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $faculty["designation"]
                                        );
                                        ?>

                                    </td>


                                    <!-- Phone -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $faculty["phone"] ?? ""
                                        );
                                        ?>

                                    </td>


                                    <!-- Status -->

                                    <td>

                                        <?php if (
                                            (int)$faculty["status"] === 1
                                        ): ?>

                                            <span
                                                class="student-status active-status"
                                            >
                                                Active
                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="student-status inactive-status"
                                            >
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- Actions -->

                                    <td class="text-end">

                                        <a
                                            href="view-faculty.php?id=<?php
                                                echo (int)$faculty["id"];
                                            ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a
                                            href="edit-faculty.php?id=<?php
                                                echo (int)$faculty["id"];
                                            ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            action="delete-faculty.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this faculty member?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php
                                                    echo (int)$faculty["id"];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
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
                                    colspan="8"
                                    class="text-center py-5"
                                >

                                    <i
                                        class="bi bi-person-badge"
                                        style="font-size: 38px;"
                                    ></i>

                                    <h6 class="mt-3">
                                        No faculty members found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Add your first faculty member to get started.
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