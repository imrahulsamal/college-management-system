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

if ($search !== "") {

    $likeSearch = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT *
        FROM departments
        WHERE
            department_code LIKE ?
            OR department_name LIKE ?
            OR hod_name LIKE ?
            OR email LIKE ?
        ORDER BY id DESC
    ");

    $stmt->bind_param(
        "ssss",
        $likeSearch,
        $likeSearch,
        $likeSearch,
        $likeSearch
    );

    $stmt->execute();
    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT *
        FROM departments
        ORDER BY id DESC
    ");
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

    <title>Departments | College CMS</title>

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

                    <h2>Departments</h2>

                    <p>
                        Manage college departments and HOD details.
                    </p>

                </div>

                <a
                    href="add-department.php"
                    class="btn primary-action-btn"
                >
                    <i class="bi bi-building-add"></i>
                    Add Department
                </a>

            </div>


            <?php if (
                isset($_GET["deleted"]) &&
                $_GET["deleted"] === "1"
            ): ?>

                <div class="alert alert-success">
                    Department deleted successfully.
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
                                placeholder="Search department..."
                                value="<?php
                                    echo htmlspecialchars($search);
                                ?>"
                            >

                        </div>

                    </div>

                    <div class="col-auto">

                        <button
                            type="submit"
                            class="btn btn-dark"
                        >
                            Search
                        </button>

                    </div>

                    <?php if ($search !== ""): ?>

                        <div class="col-auto">

                            <a
                                href="departments.php"
                                class="btn btn-outline-secondary"
                            >
                                Clear
                            </a>

                        </div>

                    <?php endif; ?>

                </form>


                <!-- Departments Table -->

                <div class="table-responsive">

                    <table class="table align-middle student-table">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Department</th>
                                <th>Code</th>
                                <th>HOD</th>
                                <th>Email</th>
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
                                $department = $result->fetch_assoc()
                            ):
                            ?>

                                <tr>

                                    <td>
                                        <?php echo $number++; ?>
                                    </td>


                                    <td>

                                        <div class="student-info">

                                            <div class="student-avatar">
                                                <i class="bi bi-building"></i>
                                            </div>

                                            <div>

                                                <strong>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $department["department_name"]
                                                    );
                                                    ?>

                                                </strong>

                                                <small>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $department["description"] ?: "College Department"
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $department["department_code"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $department["hod_name"] ?: "-"
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $department["email"] ?: "-"
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $department["phone"] ?: "-"
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php if (
                                            (int)$department["status"] === 1
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


                                    <td class="text-end">

                                        <a
                                            href="view-department.php?id=<?php
                                                echo (int)$department["id"];
                                            ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a
                                            href="edit-department.php?id=<?php
                                                echo (int)$department["id"];
                                            ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            action="delete-department.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this department?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php
                                                    echo (int)$department["id"];
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
                                        class="bi bi-buildings"
                                        style="font-size: 38px;"
                                    ></i>

                                    <h6 class="mt-3">
                                        No departments found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Add your first department to get started.
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