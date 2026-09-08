<?php

$currentPage = basename($_SERVER["PHP_SELF"]);

?>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">

        <div class="brand-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>

        <div>
            <h5>Dhiren Patel</h5>
            <span>Admin Panel</span>
        </div>

    </div>


    <nav class="sidebar-menu">

        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="menu-link <?php
            echo $currentPage === "dashboard.php"
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>


        <!-- STUDENTS -->

        <a
            href="students.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "students.php",
                    "add-student.php",
                    "view-student.php",
                    "edit-student.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-people-fill"></i>
            <span>Students</span>
        </a>


        <!-- FACULTY -->

        <a
            href="faculty.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "faculty.php",
                    "add-faculty.php",
                    "view-faculty.php",
                    "edit-faculty.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-person-badge-fill"></i>
            <span>Faculty</span>
        </a>


        <!-- DEPARTMENTS -->

        <a
            href="departments.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "departments.php",
                    "add-department.php",
                    "view-department.php",
                    "edit-department.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-building"></i>
            <span>Departments</span>
        </a>


        <!-- SUBJECTS -->

        <a
            href="subjects.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "subjects.php",
                    "add-subject.php",
                    "view-subject.php",
                    "edit-subject.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-journal-bookmark-fill"></i>
            <span>Subjects</span>
        </a>


        <!-- ATTENDANCE -->

        <a
            href="attendance.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "attendance.php",
                    "add-attendance.php",
                    "edit-attendance.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-calendar-check-fill"></i>
            <span>Attendance</span>
        </a>


        <!-- RESULTS -->

        <a
            href="results.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "results.php",
                    "add-result.php",
                    "view-result.php",
                    "edit-result.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-bar-chart-fill"></i>
            <span>Results</span>
        </a>


        <!-- FEES -->

        <a
            href="fees.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "fees.php",
                    "add-fee.php",
                    "view-fee.php",
                    "edit-fee.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-wallet2"></i>
            <span>Fees</span>
        </a>


        <!-- NOTICES -->

        <a
            href="notices.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "notices.php",
                    "add-notice.php",
                    "view-notice.php",
                    "edit-notice.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-megaphone-fill"></i>
            <span>Notices</span>
        </a>

        <!-- UPCOMING EVENTS -->
        
        <a
            href="events.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "events.php",
                    "add-event.php",
                    "view-event.php",
                    "edit-event.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-calendar-event-fill"></i>
            <span>Events</span>
        </a>
        


        <!-- TIMETABLE -->

        <a
            href="timetable.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                [
                    "timetable.php",
                    "add-timetable.php",
                    "view-timetable.php",
                    "edit-timetable.php"
                ],
                true
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-table"></i>
            <span>Timetable</span>
        </a>

        <!-- PROFILE -->
        <a
            href="profile.php"
            class="menu-link <?php
            echo $currentPage === "profile.php" ? "active" : "";
            ?>"
        >
            <i class="bi bi-person-circle"></i>
            <span>Profile</span>
        </a>

    </nav>

    <!-- LOG OUT -->
    <div class="sidebar-footer">

        <a
            href="../logout.php"
            class="menu-link logout-link"
        >
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>

    </div>

</aside>