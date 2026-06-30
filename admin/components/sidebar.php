<?php
/** Vertical sidebar (Dashly / brit-backoffice template). Expects $page. */
$page = $page ?? "Dashboard";
$isSuper = ($_SESSION["designation"] ?? "") === "Super Admin";
$certSection = in_array($page, ["Certificates", "NewInspection", "AddCertificate"], true);
?>
<div data-bs-theme="">
    <nav class="navbar navbar-vertical fixed-start navbar-expand-md" id="sidebar">
        <div class="container-fluid">

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarCollapse" aria-controls="sidebarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a class="navbar-brand" href="dashboard.php">
                <img src="./assets/img/logo-dark.svg" class="navbar-brand-img mx-auto" alt="Glajoe">
            </a>

            <div class="navbar-user d-md-none">
                <div class="dropdown">
                    <a href="#" id="sidebarIcon" class="dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="avatar avatar-sm avatar-online">
                            <img src="<?php echo e($avatar); ?>" class="avatar-img rounded-circle" alt="avatar" onerror="this.src='../assets/images/avatar.png'">
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="sidebarIcon">
                        <a href="logout.php" class="dropdown-item">Logout</a>
                    </div>
                </div>
            </div>

            <div class="collapse navbar-collapse" id="sidebarCollapse">

                <ul class="navbar-nav mt-4">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === "Dashboard" ? "active" : ""; ?>" href="dashboard.php">
                            <i class="fe fe-grid"></i> Overview
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo $certSection ? "" : "collapsed"; ?>" href="#sidebarCertificates" role="button"
                           data-bs-toggle="collapse" aria-expanded="<?php echo $certSection ? "true" : "false"; ?>" aria-controls="sidebarCertificates">
                            <i class="fe fe-award"></i> Certificates
                        </a>
                        <div class="collapse <?php echo $certSection ? "show" : ""; ?>" id="sidebarCertificates">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=winch" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "winch") === "winch") ? "active" : ""; ?>">Winch Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=turnbuckle" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "turnbuckle") ? "active" : ""; ?>">Turnbuckle Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=beam_trolley" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "beam_trolley") ? "active" : ""; ?>">Beam Trolley Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=drill_pipe" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "drill_pipe") ? "active" : ""; ?>">Drill Pipe Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="certificates.php" class="nav-link <?php echo $page === "Certificates" ? "active" : ""; ?>">All Certificates</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>

                <?php if ($isSuper): ?>
                <hr class="navbar-divider my-3">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === "Admins" ? "active" : ""; ?>" href="admins.php">
                            <i class="fe fe-briefcase"></i> Administrators
                        </a>
                    </li>
                </ul>
                <?php endif; ?>

                <div class="mt-auto"></div>

                <div class="navbar-user d-none d-md-flex" id="sidebarUser">
                    <a class="navbar-user-link" href="logout.php" title="Logout">
                        <span class="icon"><i class="fe fe-power"></i></span>
                    </a>
                    <div class="dropup">
                        <a href="#" id="sidebarIconCopy" class="dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="avatar avatar-sm avatar-online">
                                <img src="<?php echo e($avatar); ?>" class="avatar-img rounded-circle" alt="avatar" onerror="this.src='../assets/images/avatar.png'">
                            </div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="sidebarIconCopy">
                            <span class="dropdown-item-text small text-muted"><?php echo $designation; ?></span>
                            <hr class="dropdown-divider">
                            <a href="logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </nav>
</div>
