<?php

$currentPage = basename($_SERVER["PHP_SELF"]);

?>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-brand">

        <div class="brand-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>

        <div>
            <h5>
                <?php
                echo htmlspecialchars(
                    $_SESSION["user_name"] ?? "Faculty"
                );
                ?>
            </h5>

            <span>Faculty Panel</span>
        </div>

    </div>


    <nav class="sidebar-menu">

        <a
            href="../faculty/dashboard.php"
            class="menu-link <?php
            echo $currentPage === "dashboard.php"
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-grid-fill"></i>
            <span>Dashboard</span>
        </a>


        <a
            href="../faculty/timetable.php"
            class="menu-link <?php
            echo $currentPage === "timetable.php"
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-calendar3"></i>
            <span>My Timetable</span>
        </a>


        <a
            href="../faculty/attendance.php"
            class="menu-link <?php
            echo $currentPage === "attendance.php"
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-calendar-check-fill"></i>
            <span>Attendance</span>
        </a>


        <a
            href="../faculty/results.php"
            class="menu-link <?php
            echo $currentPage === "results.php"
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-bar-chart-fill"></i>
            <span>Results</span>
        </a>


        <a
            href="../faculty/notices.php"
            class="menu-link <?php
            echo $currentPage === "notices.php"
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-megaphone-fill"></i>
            <span>Notices</span>
        </a>


        <a
            href="../faculty/profile.php"
            class="menu-link <?php
            echo $currentPage === "profile.php"
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-person-circle"></i>
            <span>Profile</span>
        </a>

    </nav>


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