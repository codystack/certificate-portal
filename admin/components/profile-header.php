<?php /** "My Account" header + tabs, shared by profile.php and security.php. */ ?>
<div class="header mt-md-5">
    <div class="header-body">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="header-title">My Account</h1>
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col">
                <ul class="nav nav-tabs nav-overflow header-tabs">
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link <?php echo $page === "Profile" ? "active" : ""; ?>">
                            Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="security.php" class="nav-link <?php echo $page === "Security" ? "active" : ""; ?>">
                            Security
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
