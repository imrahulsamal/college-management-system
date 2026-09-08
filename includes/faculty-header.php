<header class="topbar">

    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <div class="topbar-title">
        <h4>Faculty Dashboard</h4>

        <p>
            Welcome back,
            <?php
            echo htmlspecialchars(
                $_SESSION["user_name"] ?? "Faculty"
            );
            ?>
        </p>
    </div>

    <div class="topbar-actions">

        <button class="icon-button">
            <i class="bi bi-bell"></i>
        </button>

        <a href="profile.php" class="profile-box faculty-profile-link">

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
            echo htmlspecialchars(
                $_SESSION["user_name"]
            );
            ?>
        </strong>

        <span>Faculty</span>

    </div>

</a>

    </div>

</header>