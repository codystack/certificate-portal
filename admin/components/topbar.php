<?php /** Sticky top bar (Dashly). */ ?>
<nav class="navbar sticky-top navbar-expand-md navbar-light d-none d-md-flex" id="topbar">
    <div class="container-fluid">
        <div></div>
        <div class="navbar-user">
            <div class="dropdown">
                <a href="#" class="avatar avatar-sm avatar-online dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img src="<?php echo e($avatar); ?>" alt="avatar" class="avatar-img rounded-circle" onerror="this.src='../assets/images/avatar.png'">
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <span class="dropdown-item-text"><strong><?php echo $fullName; ?></strong></span>
                    <span class="dropdown-item-text small text-muted"><?php echo $userEmail; ?></span>
                    <hr class="dropdown-divider">
                    <a href="profile.php" class="dropdown-item">Profile</a>
                    <a href="security.php" class="dropdown-item">Security</a>
                    <hr class="dropdown-divider">
                    <a href="logout.php" class="dropdown-item">Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>
