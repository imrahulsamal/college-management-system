<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "faculty"
) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$facultyId = $_SESSION["faculty_id"] ?? null;

$timetable = null;

if ($facultyId) {

    $stmt = $conn->prepare("
        SELECT
            timetable.*,
            subjects.subject_code,
            subjects.subject_name,
            departments.department_name
        FROM timetable
        INNER JOIN subjects
            ON timetable.subject_id = subjects.id
        INNER JOIN departments
            ON timetable.department_id = departments.id
        WHERE timetable.faculty_id = ?
        AND timetable.status = 1
        ORDER BY FIELD(
            timetable.day_of_week,
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday'
        ),
        timetable.start_time ASC
    ");

    $stmt->bind_param("i", $facultyId);
    $stmt->execute();

    $timetable = $stmt->get_result();
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

    <title>My Timetable | College CMS</title>

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
        href="../assets/css/admin.css?v=13"
    >

</head>

<body class="faculty-timetable-page">

<div class="admin-layout">

    <?php include "../includes/faculty-sidebar.php"; ?>

    <div class="main-area">

        <?php include "../includes/faculty-header.php"; ?>

        <main class="dashboard-content">

            <div class="page-heading">

                <div>
                    <h2>My Timetable</h2>

                    <p>
                        View your assigned classes and schedule.
                    </p>
                </div>

            </div>


            <div class="dashboard-card">

                <?php if (
                    $timetable &&
                    $timetable->num_rows > 0
                ): ?>

                    <div class="table-responsive">

                        <?php

$days = [
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
];

/* Fixed college periods */
$timeSlots = [
    "08:00:00|09:00:00" => [
        "start" => "08:00:00",
        "end"   => "09:00:00"
    ],

    "09:00:00|10:00:00" => [
        "start" => "09:00:00",
        "end"   => "10:00:00"
    ],

    "10:00:00|11:00:00" => [
        "start" => "10:00:00",
        "end"   => "11:00:00"
    ],

    "11:15:00|12:15:00" => [
        "start" => "11:15:00",
        "end"   => "12:15:00"
    ],

    "12:15:00|13:15:00" => [
        "start" => "12:15:00",
        "end"   => "13:15:00"
    ]
];

$schedule = [];

/* Faculty timetable records ko grid me convert karo */
while ($row = $timetable->fetch_assoc()) {

    $slotKey =
        $row["start_time"] . "|" .
        $row["end_time"];

    $schedule[$slotKey][$row["day_of_week"]] = $row;
}

?>


<div class="table-responsive">

    <table
        class="table table-bordered timetable-grid
               align-middle text-center"
    >

        <thead>

            <tr>

                <th>Period</th>
                <th>Time</th>

                <?php foreach ($days as $day): ?>

                    <th>
                        <?php echo $day; ?>
                    </th>

                <?php endforeach; ?>

            </tr>

        </thead>


        <tbody>

        <?php $period = 1; ?>

        <?php foreach ($timeSlots as $slotKey => $slot): ?>

            <tr>

                <!-- Period -->
                <td class="period-cell">

                    <?php echo $period++; ?>

                </td>


                <!-- Time -->
                <td class="time-cell">

                    <?php
                    echo date(
                        "h:i A",
                        strtotime($slot["start"])
                    );
                    ?>

                    <br>

                    <span class="text-muted">
                        to
                    </span>

                    <br>

                    <?php
                    echo date(
                        "h:i A",
                        strtotime($slot["end"])
                    );
                    ?>

                </td>


                <!-- Monday to Saturday -->
                <?php foreach ($days as $day): ?>

                    <td class="schedule-cell">

                        <?php
                        if (
                            isset(
                                $schedule[$slotKey][$day]
                            )
                        ):

                            $class =
                                $schedule[$slotKey][$day];
                        ?>

                            <div class="timetable-subject">

                                <!-- Subject -->
                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $class["subject_name"]
                                    );
                                    ?>

                                </strong>


                                <!-- Subject Code -->
                                <span class="subject-code">

                                    <?php
                                    echo htmlspecialchars(
                                        $class["subject_code"]
                                    );
                                    ?>

                                </span>


                                <!-- Department -->
                                <small>

                                    <i class="bi bi-building"></i>

                                    <?php
                                    echo htmlspecialchars(
                                        $class["department_name"]
                                    );
                                    ?>

                                </small>


                                <!-- Semester -->
                                <small>

                                    <i class="bi bi-mortarboard"></i>

                                    Semester
                                    <?php
                                    echo (int)$class["semester"];
                                    ?>

                                </small>


                                <!-- Room -->
                                <small>

                                    <i class="bi bi-door-open"></i>

                                    Room:
                                    <?php
                                    echo htmlspecialchars(
                                        $class["room_no"] ?: "-"
                                    );
                                    ?>

                                </small>

                            </div>

                        <?php else: ?>

                            <span class="text-muted">
                                —
                            </span>

                        <?php endif; ?>

                    </td>

                <?php endforeach; ?>

            </tr>


            <!-- Recess after Period 3 -->
            <?php
            if ($slot["end"] === "11:00:00"):
            ?>

                <tr class="recess-row">

                    <td colspan="8">
                        RECESS
                    </td>

                </tr>

            <?php endif; ?>


        <?php endforeach; ?>

        </tbody>

    </table>

</div>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5 text-muted">

                        <i class="bi bi-calendar-x fs-2"></i>

                        <p class="mt-2 mb-0">
                            No timetable assigned.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </main>

    </div>

</div>

</body>

</html>