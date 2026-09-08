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
$audienceFilter = trim($_GET["audience"] ?? "");
$statusFilter = trim($_GET["status"] ?? "");

$sql = "
    SELECT *
    FROM notices
    WHERE 1 = 1
";

$params = [];
$types = "";


/* SEARCH */

if ($search !== "") {

    $sql .= "
        AND (
            title LIKE ?
            OR description LIKE ?
        )
    ";

    $likeSearch = "%" . $search . "%";

    $params[] = $likeSearch;
    $params[] = $likeSearch;

    $types .= "ss";
}


/* AUDIENCE FILTER */

if (
    in_array(
        $audienceFilter,
        ["All", "Students", "Faculty"],
        true
    )
) {

    $sql .= " AND audience = ?";

    $params[] = $audienceFilter;
    $types .= "s";
}


/* STATUS FILTER */

if ($statusFilter === "1" || $statusFilter === "0") {

    $sql .= " AND status = ?";

    $params[] = (int)$statusFilter;
    $types .= "i";
}


$sql .= "
    ORDER BY notice_date DESC, id DESC
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

    <title>Notices | College CMS</title>

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

                    <h2>Notices Management</h2>

                    <p>
                        Manage college announcements and notices.
                    </p>

                </div>

                <a
                    href="add-notice.php"
                    class="btn primary-action-btn"
                >
                    <i class="bi bi-plus-circle"></i>
                    Add Notice
                </a>

            </div>


            <!-- DELETE SUCCESS -->

            <?php if (
                isset($_GET["deleted"]) &&
                $_GET["deleted"] === "1"
            ): ?>

                <div class="alert alert-success">

                    <i class="bi bi-check-circle me-1"></i>

                    Notice deleted successfully.

                </div>

            <?php endif; ?>


            <div class="dashboard-card">


                <!-- FILTERS -->

                <form
                    method="GET"
                    action="notices.php"
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
                            placeholder="Search title or description..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                    </div>


                    <div class="col-md-2">

                        <label class="form-label">
                            Audience
                        </label>

                        <select
                            name="audience"
                            class="form-select"
                        >

                            <option value="">
                                All
                            </option>

                            <option
                                value="All"
                                <?php
                                if ($audienceFilter === "All") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Everyone
                            </option>

                            <option
                                value="Students"
                                <?php
                                if ($audienceFilter === "Students") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Students
                            </option>

                            <option
                                value="Faculty"
                                <?php
                                if ($audienceFilter === "Faculty") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Faculty
                            </option>

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
                                value="1"
                                <?php
                                if ($statusFilter === "1") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Active
                            </option>

                            <option
                                value="0"
                                <?php
                                if ($statusFilter === "0") {
                                    echo "selected";
                                }
                                ?>
                            >
                                Inactive
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
                            href="notices.php"
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
                                <th>Notice</th>
                                <th>Date</th>
                                <th>Audience</th>
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
                                $notice = $result->fetch_assoc()
                            ):

                            ?>

                                <tr>

                                    <td>
                                        <?php echo $number++; ?>
                                    </td>


                                    <!-- NOTICE -->

                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $notice["title"]
                                            );
                                            ?>

                                        </strong>

                                        <small class="d-block text-muted mt-1">

                                            <?php

                                            $description =
                                                $notice["description"];

                                            if (
                                                strlen($description) > 70
                                            ) {

                                                $description =
                                                    substr(
                                                        $description,
                                                        0,
                                                        70
                                                    ) . "...";
                                            }

                                            echo htmlspecialchars(
                                                $description
                                            );

                                            ?>

                                        </small>

                                    </td>


                                    <!-- DATE -->

                                    <td>

                                        <?php
                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $notice["notice_date"]
                                            )
                                        );
                                        ?>

                                    </td>


                                    <!-- AUDIENCE -->

                                    <td>

                                        <span class="badge bg-light text-dark">

                                            <?php
                                            echo htmlspecialchars(
                                                $notice["audience"]
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- STATUS -->

                                    <td>

                                        <?php if (
                                            (int)$notice["status"] === 1
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


                                    <!-- ACTIONS -->

                                    <td class="text-end">

                                        <a
                                            href="view-notice.php?id=<?php echo (int)$notice["id"]; ?>"
                                            class="btn btn-sm btn-outline-primary"
                                            title="View Notice"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a
                                            href="edit-notice.php?id=<?php echo (int)$notice["id"]; ?>"
                                            class="btn btn-sm btn-outline-warning"
                                            title="Edit Notice"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form
                                            action="delete-notice.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this notice?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo (int)$notice["id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete Notice"
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
                                    colspan="6"
                                    class="text-center py-5"
                                >

                                    <i
                                        class="bi bi-megaphone"
                                        style="font-size: 38px;"
                                    ></i>

                                    <h6 class="mt-3">
                                        No notices found
                                    </h6>

                                    <p class="text-muted mb-0">
                                        Add a notice to get started.
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