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

$courseFilter = trim($_GET["course"] ?? "");
$semesterFilter = (int) ($_GET["semester"] ?? 0);

$coursesResult = $conn->query("
    SELECT DISTINCT course
    FROM students
    WHERE course IS NOT NULL
      AND course <> ''
    ORDER BY course ASC
");

$sql = "
    SELECT *
    FROM students
    WHERE 1 = 1
";

$params = [];
$types = "";

if ($search !== "") {

    $sql .= "
        AND (
            admission_no LIKE ?
            OR first_name LIKE ?
            OR last_name LIKE ?
            OR email LIKE ?
            OR course LIKE ?
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

if ($courseFilter !== "") {

    $sql .= "
        AND course = ?
    ";

    $params[] = $courseFilter;
    $types .= "s";
}

if ($semesterFilter > 0) {

    $sql .= "
        AND semester = ?
    ";

    $params[] = $semesterFilter;
    $types .= "i";
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

    <title>Manage Students</title>

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
                    <h2>Students</h2>

                    <p>
                        Manage all student records.
                    </p>
                </div>

                <a
                    href="add-student.php"
                    class="btn primary-action-btn"
                >
                    <i class="bi bi-person-plus-fill"></i>
                    Add Student
                </a>

            </div>

            <?php if (isset($_GET["deleted"]) && $_GET["deleted"] === "1"): ?>

    <div class="alert alert-success">
        Student deleted successfully.
    </div>

<?php endif; ?>


            <div class="dashboard-card">

                <!-- SEARCH -->

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
                                placeholder="Search student..."
                                value="<?php echo htmlspecialchars($search); ?>"
                            >

                        </div>

                    </div>

                    <!-- Course Filter -->
                    <div class="col-md-3 col-lg-3">

                        <select
                            name="course"
                            class="form-select"
                        >
                            <option value="">All Courses</option>

                            <?php while (
                                $courseRow = $coursesResult->fetch_assoc()
                            ): ?>

                                <option
                                    value="<?php
                                        echo htmlspecialchars($courseRow["course"]);
                                    ?>"
                                    <?php
                                    echo (
                                        $courseFilter === $courseRow["course"]
                                    ) ? "selected" : "";
                                    ?>
                                >
                                    <?php
                                    echo htmlspecialchars($courseRow["course"]);
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

                    <div class="col-auto d-flex gap-2">

                        <button
                            type="submit"
                            class="btn btn-dark"
                        >
                            <i class="bi bi-search me-1"></i>
                            Search
                        </button>

                        <a
                            href="students.php"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Clear
                        </a>

                    </div>

                </form>


                <div class="table-responsive">

                    <table
                        class="table align-middle student-table"
                    >

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Admission No.</th>
                                <th>Course</th>
                                <th>Semester</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th class="text-end">
                                    Actions
                                </th>
                            </tr>

                        </thead>

                        <tbody>

                        <?php if ($result && $result->num_rows > 0): ?>

                            <?php
                            $number = 1;

                            while ($student = $result->fetch_assoc()):
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
                                                        $student["first_name"],
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
                                                        $student["first_name"] .
                                                        " " .
                                                        $student["last_name"]
                                                    );
                                                    ?>
                                                </strong>

                                                <small>
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $student["email"]
                                                    );
                                                    ?>
                                                </small>

                                            </div>

                                        </div>

                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $student["admission_no"]
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $student["course"]
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        Semester
                                        <?php
                                        echo (int)$student["semester"];
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo htmlspecialchars(
                                            $student["phone"]
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <?php
                                        if ((int)$student["status"] === 1):
                                        ?>

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
                                            href="view-student.php?id=<?php echo $student["id"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a
                                            href="edit-student.php?id=<?php echo $student["id"]; ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Edit"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            action="delete-student.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this student?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo (int)$student["id"]; ?>"
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
                                        class="bi bi-people"
                                        style="font-size: 38px;"
                                    ></i>

                                    <h6 class="mt-3">
                                        No students found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Add your first student to get started.
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