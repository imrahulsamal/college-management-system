<header class="topbar">

    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <div class="topbar-title">
        <h4>Student Dashboard</h4>

        <p>
            Welcome back,
            <?php echo htmlspecialchars(
                $_SESSION["user_name"] ?? "Student"
            ); ?>
        </p>
    </div>

    <div class="topbar-actions">

        <button class="icon-button">
            <i class="bi bi-bell"></i>
        </button>

        <a href="../student/profile.php"
            class="student-header-profile text-decoration-none">
        <div class="profile-box">

            <div class="profile-avatar">
                <?php
                echo strtoupper(
                    substr(
                        $_SESSION["user_name"] ?? "S",
                        0,
                        1
                    )
                );
                ?>
            </div>

            <div class="profile-details">

                <strong>
                    <?php echo htmlspecialchars(
                        $_SESSION["user_name"] ?? "Student"
                    ); ?>
                </strong>

                <span>Student</span>

            </div>

        </div>
        </a>

    </div>

</header>