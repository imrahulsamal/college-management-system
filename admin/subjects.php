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

$departmentFilter = (int) ($_GET["department"] ?? 0);
$semesterFilter = (int) ($_GET["semester"] ?? 0);

$departments = $conn->query("
    SELECT id, department_name
    FROM departments
    ORDER BY department_name ASC
");

$sql = "
    SELECT
        subjects.*,
        departments.department_name
    FROM subjects
    INNER JOIN departments
        ON subjects.department_id = departments.id
    WHERE 1 = 1
";

$params = [];
$types = "";

if ($search !== "") {

    $sql .= "
        AND (
            subjects.subject_code LIKE ?
            OR subjects.subject_name LIKE ?
            OR departments.department_name LIKE ?
        )
    ";

    $likeSearch = "%" . $search . "%";

    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;

    $types .= "sss";
}

if ($departmentFilter > 0) {

    $sql .= " AND subjects.department_id = ? ";

    $params[] = $departmentFilter;
    $types .= "i";
}

if ($semesterFilter > 0) {

    $sql .= " AND subjects.semester = ? ";

    $params[] = $semesterFilter;
    $types .= "i";
}

$sql .= " ORDER BY subjects.id DESC ";

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

    <title>Subjects | College CMS</title>

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

                    <h2>Subjects</h2>

                    <p>
                        Manage subjects, departments and semester details.
                    </p>

                </div>

                <a
                    href="add-subject.php"
                    class="btn primary-action-btn"
                >
                    <i class="bi bi-journal-plus"></i>
                    Add Subject
                </a>

            </div>


            <?php if (
                isset($_GET["deleted"]) &&
                $_GET["deleted"] === "1"
            ): ?>

                <div class="alert alert-success">
                    Subject deleted successfully.
                </div>

            <?php endif; ?>


            <div class="dashboard-card">

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
                                placeholder="Search subject..."
                                value="<?php
                                    echo htmlspecialchars($search);
                                ?>"
                            >

                        </div>

                    </div>

                    <!-- Department Filter -->
                    <div class="col-md-3 col-lg-3">

                        <select
                            name="department"
                            class="form-select"
                        >
                            <option value="0">All Departments</option>

                            <?php while (
                                $department = $departments->fetch_assoc()
                            ): ?>

                                <option
                                    value="<?php echo (int)$department["id"]; ?>"
                                    <?php
                                    echo (
                                        $departmentFilter === (int)$department["id"]
                                    ) ? "selected" : "";
                                    ?>
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $department["department_name"]
                                    );
                                    ?>
                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- Semester Filter -->
                    <div class="col-md-3 col-lg-2">

                        <select
                            name="semester"
                            class="form-select"
                        >
                            <option value="0">All Semesters</option>

                            <?php for ($sem = 1; $sem <= 8; $sem++): ?>

                                <option
                                    value="<?php echo $sem; ?>"
                                    <?php
                                    echo ($semesterFilter === $sem)
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Semester <?php echo $sem; ?>
                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>

                    <div class="col-auto">

                        <button
                            type="submit"
                            class="btn btn-dark"
                        >
                            Search
                        </button>

                        <a
                                href="subjects.php"
                                class="btn btn-outline-secondary"
                            >
                                
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Clear
                                
                            </a>

                    </div>

                </form>


                <div class="table-responsive">

                    <table class="table align-middle student-table">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Subject</th>
                                <th>Code</th>
                                <th>Department</th>
                                <th>Semester</th>
                                <th>Credits</th>
                                <th>Type</th>
                                <th>Status</th>
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
                                $subject = $result->fetch_assoc()
                            ):
                            ?>

                                <tr>

                                    <td>
                                        <?php echo $number++; ?>
                                    </td>


                                    <td>

                                        <div class="student-info">

                                            <div class="student-avatar">
                                                <i class="bi bi-book"></i>
                                            </div>

                                            <div>

                                                <strong>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $subject["subject_name"]
                                                    );
                                                    ?>
                                                </strong>

                                                <small>
                                                    Academic Subject
                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $subject["subject_code"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $subject["department_name"]
                                        );
                                        ?>
                                    </td>


                                    <td>
                                        Semester
                                        <?php
                                        echo (int)$subject["semester"];
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo (int)$subject["credits"];
                                        ?>
                                    </td>


                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $subject["subject_type"]
                                        );
                                        ?>
                                    </td>


                                    <td>

                                        <?php if (
                                            (int)$subject["status"] === 1
                                        ): ?>

                                            <span class="student-status active-status">
                                                Active
                                            </span>

                                        <?php else: ?>

                                            <span class="student-status inactive-status">
                                                Inactive
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td class="text-end">

                                        <a
                                            href="view-subject.php?id=<?php
                                                echo (int)$subject["id"];
                                            ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a
                                            href="edit-subject.php?id=<?php
                                                echo (int)$subject["id"];
                                            ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            action="delete-subject.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this subject?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php
                                                    echo (int)$subject["id"];
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
                                    colspan="9"
                                    class="text-center py-5"
                                >

                                    <i
                                        class="bi bi-journals"
                                        style="font-size: 38px;"
                                    ></i>

                                    <h6 class="mt-3">
                                        No subjects found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Add your first subject to get started.
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