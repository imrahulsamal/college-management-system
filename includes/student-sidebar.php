<?php

$currentPage = basename($_SERVER["PHP_SELF"]);

?>

<aside class="sidebar">

    <div class="sidebar-brand">

        <div class="brand-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>

        <div>
            <h5>
                <?php
                echo htmlspecialchars(
                    $_SESSION["user_name"]
                );
                ?>
            </h5>

            <span>Student Panel</span>
        </div>

    </div>


    <nav class="sidebar-menu">

        <a
            href="../student/dashboard.php"
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
            href="../student/timetable.php"
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
            href="../student/attendance.php"
            class="menu-link <?php
            echo $currentPage === "attendance.php"
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-check2-square"></i>
            <span>Attendance</span>
        </a>


        <a
            href="../student/results.php"
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
            href="../student/fees.php"
            class="menu-link <?php
            echo in_array(
                $currentPage,
                ["fees.php", "pay-fee.php"]
            )
                ? "active"
                : "";
            ?>"
        >
            <i class="bi bi-wallet2"></i>
            <span>Fees</span>
        </a>


        <a
            href="../student/notices.php"
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
            href="../student/profile.php"
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
        class="student-logout-btn"
        style="
            display: flex;
            align-items: center;
            gap: 12px;
            background: #dc3545;
            color: #ffffff;
            padding: 12px 16px;
            margin: 10px 14px 18px;
            border-radius: 10px;
            text-decoration: none;
        "
    >
        <i
            class="bi bi-box-arrow-right"
            style="color: #ffffff;"
        ></i>

        <span style="color: #ffffff;">
            Logout
        </span>
    </a>

</div>

</aside>