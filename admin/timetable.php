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
$departmentFilter = trim($_GET["department"] ?? "");
$semesterFilter = trim($_GET["semester"] ?? "");
$dayFilter = trim($_GET["day"] ?? "");


/* DEPARTMENTS FOR FILTER */

$departments = $conn->query("
    SELECT id, department_name
    FROM departments
    WHERE status = 1
    ORDER BY department_name ASC
");


/* TIMETABLE QUERY */

$sql = "
    SELECT
        timetable.*,
        departments.department_name,
        subjects.subject_code,
        subjects.subject_name,
        faculty.first_name AS faculty_first_name,
        faculty.last_name AS faculty_last_name
    FROM timetable

    INNER JOIN departments
        ON timetable.department_id = departments.id

    INNER JOIN subjects
        ON timetable.subject_id = subjects.id

    INNER JOIN faculty
        ON timetable.faculty_id = faculty.id

    WHERE 1 = 1
";

$params = [];
$types = "";


/* SEARCH */

if ($search !== "") {

    $sql .= "
        AND (
            subjects.subject_name LIKE ?
            OR subjects.subject_code LIKE ?
            OR faculty.first_name LIKE ?
            OR faculty.last_name LIKE ?
            OR timetable.room_no LIKE ?
        )
    ";

    $likeSearch = "%" . $search . "%";

    for ($i = 0; $i < 5; $i++) {
        $params[] = $likeSearch;
    }

    $types .= "sssss";
}


/* DEPARTMENT */

if (
    $departmentFilter !== "" &&
    ctype_digit($departmentFilter)
) {

    $sql .= " AND timetable.department_id = ?";

    $params[] = (int)$departmentFilter;
    $types .= "i";
}


/* SEMESTER */

if (
    $semesterFilter !== "" &&
    ctype_digit($semesterFilter)
) {

    $sql .= " AND timetable.semester = ?";

    $params[] = (int)$semesterFilter;
    $types .= "i";
}


/* DAY */

$validDays = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
];

if (in_array($dayFilter, $validDays, true)) {

    $sql .= " AND timetable.day_of_week = ?";

    $params[] = $dayFilter;
    $types .= "s";
}


$sql .= "
    ORDER BY
        FIELD(
            timetable.day_of_week,
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        ),
        timetable.start_time ASC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
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

    <title>Timetable | College CMS</title>

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

                    <h2>Timetable Management</h2>

                    <p>
                        Manage class schedules, faculty and rooms.
                    </p>

                </div>

                <a
                    href="add-timetable.php"
                    class="btn primary-action-btn"
                >
                    <i class="bi bi-plus-circle"></i>
                    Add Schedule
                </a>

            </div>


            <!-- DELETE SUCCESS -->

            <?php if (
                isset($_GET["deleted"]) &&
                $_GET["deleted"] === "1"
            ): ?>

                <div class="alert alert-success">

                    <i class="bi bi-check-circle me-1"></i>

                    Timetable entry deleted successfully.

                </div>

            <?php endif; ?>


            <div class="dashboard-card">


                <!-- FILTERS -->

                <form
                    method="GET"
                    action="timetable.php"
                    class="row g-3 mb-4"
                >


                    <div class="col-md-4">

                        <label class="form-label">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Subject, faculty or room..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Department
                        </label>

                        <select
                            name="department"
                            class="form-select"
                        >

                            <option value="">
                                All Departments
                            </option>

                            <?php if ($departments): ?>

                                <?php while (
                                    $department = $departments->fetch_assoc()
                                ): ?>

                                    <option
                                        value="<?php echo (int)$department["id"]; ?>"
                                        <?php
                                        if (
                                            $departmentFilter ===
                                            (string)$department["id"]
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >
                                        <?php
                                        echo htmlspecialchars(
                                            $department["department_name"]
                                        );
                                        ?>
                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

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
                                    if ($semesterFilter === (string)$i) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    Semester <?php echo $i; ?>
                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">
                            Day
                        </label>

                        <select
                            name="day"
                            class="form-select"
                        >

                            <option value="">
                                All Days
                            </option>

                            <?php foreach ($validDays as $day): ?>

                                <option
                                    value="<?php echo $day; ?>"
                                    <?php
                                    if ($dayFilter === $day) {
                                        echo "selected";
                                    }
                                    ?>
                                >
                                    <?php echo $day; ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <div class="col-12">

                        <button
                            type="submit"
                            class="btn btn-dark"
                        >
                            <i class="bi bi-funnel"></i>
                            Filter
                        </button>

                        <a
                            href="timetable.php"
                            class="btn btn-outline-secondary ms-2"
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
                                <th>Day</th>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Department</th>
                                <th>Semester</th>
                                <th>Faculty</th>
                                <th>Room</th>
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


                                    <td>
                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $row["day_of_week"]
                                            );
                                            ?>
                                        </strong>
                                    </td>


                                    <td>

                                        <?php
                                        echo date(
                                            "h:i A",
                                            strtotime(
                                                $row["start_time"]
                                            )
                                        );
                                        ?>

                                        -

                                        <?php
                                        echo date(
                                            "h:i A",
                                            strtotime(
                                                $row["end_time"]
                                            )
                                        );
                                        ?>

                                    </td>


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


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["department_name"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        Semester
                                        <?php
                                        echo (int)$row["semester"];
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["faculty_first_name"] .
                                            " " .
                                            $row["faculty_last_name"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $row["room_no"] ?: "-"
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php if (
                                            (int)$row["status"] === 1
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
                                            href="view-timetable.php?id=<?php echo (int)$row["id"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a
                                            href="edit-timetable.php?id=<?php echo (int)$row["id"]; ?>"
                                            class="btn btn-sm btn-outline-warning"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            action="delete-timetable.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Delete this timetable entry?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo (int)$row["id"]; ?>"
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
                                    colspan="10"
                                    class="text-center py-5"
                                >

                                    <i
                                        class="bi bi-calendar-week"
                                        style="font-size: 38px;"
                                    ></i>

                                    <h6 class="mt-3">
                                        No timetable entries found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Add a class schedule to get started.
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