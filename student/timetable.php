<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "student"
) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$studentId = $_SESSION["student_id"] ?? null;

if (!$studentId) {
    header("Location: ../index.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT *
    FROM students
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $studentId);
$stmt->execute();

$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    header("Location: ../logout.php");
    exit;
}

/* Student Timetable */

$semester = (int)$student["semester"];
$course = $student["course"];

// $timetableStmt = $conn->prepare("
//     SELECT
//         timetable.*,
//         subjects.subject_code,
//         subjects.subject_name,
//         departments.department_name,
//         CONCAT(
//             faculty.first_name,
//             ' ',
//             faculty.last_name
//         ) AS faculty_name

//     FROM timetable

//     INNER JOIN subjects
//         ON timetable.subject_id = subjects.id

//     INNER JOIN departments
//         ON timetable.department_id = departments.id

//     INNER JOIN faculty
//         ON timetable.faculty_id = faculty.id

//     WHERE timetable.semester = ?
//       AND timetable.status = 1

//     ORDER BY FIELD(
//         timetable.day_of_week,
//         'Monday',
//         'Tuesday',
//         'Wednesday',
//         'Thursday',
//         'Friday',
//         'Saturday'
//     ),
//     timetable.start_time ASC
// ");

$semester = (int)$student["semester"];
$course = trim($student["course"]);

/* Student Timetable */

$timetableStmt = $conn->prepare("
    SELECT
        timetable.*,
        subjects.subject_code,
        subjects.subject_name,
        departments.department_name,
        CONCAT(
            faculty.first_name,
            ' ',
            faculty.last_name
        ) AS faculty_name

    FROM timetable

    INNER JOIN subjects
        ON timetable.subject_id = subjects.id

    INNER JOIN departments
        ON timetable.department_id = departments.id

    INNER JOIN faculty
        ON timetable.faculty_id = faculty.id

    WHERE timetable.status = 1
      AND timetable.semester = ?
      AND departments.department_name = ?

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

$timetableStmt->bind_param(
    "is",
    $semester,
    $course
);

$timetableStmt->execute();

$timetable = $timetableStmt->get_result();

$timetableStmt->bind_param(
    "is",
    $semester,
    $course
);

$timetableStmt->execute();

$timetable = $timetableStmt->get_result();

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
        href="../assets/css/admin.css?v=10"
    >
</head>

<body class="student-panel">

<?php include "../includes/student-sidebar.php"; ?>

<div class="main-area">

    <?php include "../includes/student-header.php"; ?>

    <main class="content-area">

        <div class="mb-4">
            <h3>My Timetable</h3>

            <p class="text-muted mb-0">
                View your Semester
                <?php echo (int)$student["semester"]; ?>
                class schedule.
            </p>
        </div>

        <div class="card border-0 shadow-sm">

            <div class="card-body p-4">

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

/* Existing timetable data ko grid me convert */
while ($row = $timetable->fetch_assoc()) {

    $slotKey = $row["start_time"] . "|" . $row["end_time"];

    $timeSlots[$slotKey] = [
        "start" => $row["start_time"],
        "end"   => $row["end_time"]
    ];

    $schedule[$slotKey][$row["day_of_week"]] = $row;
}

/* Time ke according periods sort */
uasort($timeSlots, function ($a, $b) {
    return strcmp($a["start"], $b["start"]);
});
?>

<div class="table-responsive">

    <table class="table table-bordered timetable-grid align-middle text-center">

        <thead>
            <tr>
                <th>Period</th>
                <th>Time</th>

                <?php foreach ($days as $day): ?>
                    <th><?php echo $day; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>

        <?php if (!empty($timeSlots)): ?>

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

                        <span class="text-muted">to</span>

                        <br>

                        <?php
                        echo date(
                            "h:i A",
                            strtotime($slot["end"])
                        );
                        ?>

                    </td>


                    <!-- Monday - Saturday -->
                    <?php foreach ($days as $day): ?>

                        <td class="schedule-cell">

                            <?php
                            if (isset($schedule[$slotKey][$day])):

                                $class =
                                    $schedule[$slotKey][$day];
                            ?>

                                <div class="timetable-subject">

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $class["subject_name"]
                                        );
                                        ?>
                                    </strong>

                                    <span class="subject-code">
                                        <?php
                                        echo htmlspecialchars(
                                            $class["subject_code"]
                                        );
                                        ?>
                                    </span>

                                    <small>
                                        <i class="bi bi-person"></i>

                                        <?php
                                        echo htmlspecialchars(
                                            $class["faculty_name"]
                                        );
                                        ?>
                                    </small>

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

                                <span class="text-muted">—</span>

                            <?php endif; ?>

                        </td>

                    <?php endforeach; ?>

                </tr>

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

        <?php else: ?>

            <tr>
                <td colspan="8" class="py-5 text-muted">
                    No timetable available.
                </td>
            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

                    </div>

                <?php else: ?>

                    <div class="text-center py-5 text-muted">

                        <i
                            class="bi bi-calendar-x"
                            style="font-size: 32px;"
                        ></i>

                        <p class="mt-3 mb-0">
                            No timetable available for your semester.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </main>

</div>

<script src="../assets/js/admin.js"></script>

</body>
</html>