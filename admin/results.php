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
$semesterFilter = trim($_GET["semester"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");

$sql = "
    SELECT
        results.*,
        students.admission_no,
        students.first_name,
        students.last_name,
        subjects.subject_code,
        subjects.subject_name
    FROM results
    INNER JOIN students
        ON results.student_id = students.id
    INNER JOIN subjects
        ON results.subject_id = subjects.id
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
            OR subjects.subject_code LIKE ?
            OR subjects.subject_name LIKE ?
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


/* SEMESTER FILTER */

if ($semesterFilter !== "") {

    $sql .= " AND results.semester = ?";

    $params[] = (int)$semesterFilter;
    $types .= "i";
}


/* STATUS FILTER */

if (
    in_array(
        $statusFilter,
        ["Pass", "Fail"],
        true
    )
) {

    $sql .= " AND results.result_status = ?";

    $params[] = $statusFilter;
    $types .= "s";
}


$sql .= " ORDER BY results.id DESC";


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

    <title>Results | College CMS</title>

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

                    <h2>Results Management</h2>

                    <p>
                        Manage student examination results.
                    </p>

                </div>

                <a
                    href="add-result.php"
                    class="btn primary-action-btn"
                >
                    <i class="bi bi-plus-circle"></i>
                    Add Result
                </a>

            </div>


            <?php if (
                isset($_GET["deleted"]) &&
                $_GET["deleted"] === "1"
            ): ?>

                <div class="alert alert-success">

                    <i class="bi bi-check-circle me-1"></i>

                    Result deleted successfully.

                </div>

            <?php endif; ?>


            <div class="dashboard-card">


                <!-- FILTERS -->

                <form
                    method="GET"
                    action="results.php"
                    class="row g-3 mb-4"
                >

                    <div class="col-md-5">

                        <label class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Student, admission no. or subject..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                    </div>


                    <div class="col-md-2">

                        <label class="form-label">
                            Semester
                        </label>

                        <select
                            name="semester"
                            class="form-select"
                        >

                            <option value="">
                                All
                            </option>

                            <?php for ($i = 1; $i <= 8; $i++): ?>

                                <option
                                    value="<?php echo $i; ?>"
                                    <?php
                                    if (
                                        (string)$semesterFilter
                                        ===
                                        (string)$i
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Semester <?php echo $i; ?>
                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>


                    <div class="col-md-2">

                        <label class="form-label">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All
                            </option>

                            <option
                                value="Pass"
                                <?php
                                if ($statusFilter === "Pass") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Pass
                            </option>

                            <option
                                value="Fail"
                                <?php
                                if ($statusFilter === "Fail") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Fail
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
                            href="results.php"
                            class="btn btn-outline-secondary"
                        >
                            Clear
                        </a>

                    </div>

                </form>


                <!-- RESULTS TABLE -->

                <div class="table-responsive">

                    <table class="table align-middle student-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Student</th>

                                <th>Subject</th>

                                <th>Semester</th>

                                <th>Exam</th>

                                <th>Marks</th>

                                <th>Grade</th>

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
                                $row = $result->fetch_assoc()
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
                                                        $row["first_name"],
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
                                                        $row["first_name"]
                                                        . " "
                                                        . $row["last_name"]
                                                    );
                                                    ?>

                                                </strong>

                                                <small>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $row["admission_no"]
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- SUBJECT -->

                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $row["subject_name"]
                                            );
                                            ?>

                                        </strong>

                                        <small class="d-block text-muted">

                                            <?php
                                            echo htmlspecialchars(
                                                $row["subject_code"]
                                            );
                                            ?>

                                        </small>

                                    </td>


                                    <!-- SEMESTER -->

                                    <td>

                                        Semester
                                        <?php echo (int)$row["semester"]; ?>

                                    </td>


                                    <!-- EXAM -->

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["exam_type"]
                                        );
                                        ?>

                                    </td>


                                    <!-- MARKS -->

                                    <td>

                                        <?php
                                        echo number_format(
                                            (float)$row["obtained_marks"],
                                            2
                                        );
                                        ?>

                                        /

                                        <?php
                                        echo number_format(
                                            (float)$row["total_marks"],
                                            2
                                        );
                                        ?>

                                    </td>


                                    <!-- GRADE -->

                                    <td>

                                        <span class="badge bg-secondary">

                                            <?php
                                            echo htmlspecialchars(
                                                $row["grade"] ?? "-"
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <?php if (
                                            $row["result_status"]
                                            === "Pass"
                                        ): ?>

                                            <span
                                                class="student-status active-status"
                                            >
                                                Pass
                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="student-status inactive-status"
                                            >
                                                Fail
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td class="text-end">

                                        <a
                                            href="view-result.php?id=<?php echo (int)$row["id"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View Result"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a
                                            href="edit-result.php?id=<?php echo (int)$row["id"]; ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Edit Result"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            action="delete-result.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this result?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo (int)$row["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete Result"
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
                                        class="bi bi-bar-chart"
                                        style="font-size: 38px;"
                                    ></i>

                                    <h6 class="mt-3">
                                        No results found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Add a result record to get started.
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