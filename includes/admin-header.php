<header class="topbar">

    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <div class="topbar-title">
        <h4>Dashboard</h4>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION["user_name"]); ?></p>
    </div>

    <div class="topbar-actions">

        <button class="icon-button">
            <i class="bi bi-bell"></i>
        </button>

        <a href="profile.php" class="profile-box admin-profile-link">

            <div class="profile-avatar">
                <?php
                echo strtoupper(
                    substr($_SESSION["user_name"], 0, 1)
                );
                ?>
            </div>

            <div class="profile-details">
                <strong>
                    <?php
                    echo htmlspecialchars($_SESSION["user_name"]);
                    ?>
                </strong>

                <span>Administrator</span>
            </div>

        </a>

    </div>

</header>