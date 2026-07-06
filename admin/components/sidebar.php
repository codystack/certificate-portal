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
                <img src="./assets/img/logo-dark.svg" class="navbar-brand-img mx-auto logo-mode-light" alt="Glajoe">
                <img src="./assets/img/logo-light.svg" class="navbar-brand-img mx-auto logo-mode-dark" alt="Glajoe">
            </a>
            <style>
                .logo-mode-dark { display: none; }
                [data-bs-theme="dark"] .logo-mode-light { display: none; }
                [data-bs-theme="dark"] .logo-mode-dark { display: inline-block; }
            </style>

            <div class="navbar-user d-md-none">
                <div class="avatar avatar-sm avatar-online">
                    <img src="<?php echo e($avatar); ?>" class="avatar-img rounded-circle" alt="avatar" onerror="this.src='../assets/images/avatar.png'">
                </div>
            </div>

            <div class="collapse navbar-collapse" id="sidebarCollapse">

                <ul class="navbar-nav mt-4 mt-md-4">
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
                                    <a href="new-inspection.php?type=drill_collar" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "drill_collar") ? "active" : ""; ?>">Drill Collar Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=pedestal_crane" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "pedestal_crane") ? "active" : ""; ?>">Pedestal Crane Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=pedestal_crane_starboard" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "pedestal_crane_starboard") ? "active" : ""; ?>">Pedestal Crane (Starboard) Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=shackle" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "shackle") ? "active" : ""; ?>">Shackle Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=wire_rope_sling" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "wire_rope_sling") ? "active" : ""; ?>">Wire Rope Sling Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=winch_defect" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "winch_defect") ? "active" : ""; ?>">Winch Non-Conformance Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=chain_hoist_defect" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "chain_hoist_defect") ? "active" : ""; ?>">Chain Hoist Non-Conformance Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=visual_monkeyboard" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "visual_monkeyboard") ? "active" : ""; ?>">Visual Inspection (Item)</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=mpi_inspection" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "mpi_inspection") ? "active" : ""; ?>">MPI Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=lever_hoist" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "lever_hoist") ? "active" : ""; ?>">Lever Hoist Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=heavy_weight" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "heavy_weight") ? "active" : ""; ?>">Heavy Weight (HWDP) Inspection</a>
                                </li>
                                <li class="nav-item">
                                    <a href="new-inspection.php?type=rotary_sub" class="nav-link <?php echo ($page === "NewInspection" && ($_GET["type"] ?? "") === "rotary_sub") ? "active" : ""; ?>">Rotary Connection Report</a>
                                </li>
                                <li class="nav-item">
                                    <a href="certificates.php" class="nav-link <?php echo $page === "Certificates" ? "active" : ""; ?>">All Certificates</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>

                <hr class="navbar-divider my-3">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === "Profile" ? "active" : ""; ?>" href="profile.php">
                            <i class="fe fe-user"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === "Security" ? "active" : ""; ?>" href="security.php">
                            <i class="fe fe-lock"></i> Security
                        </a>
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
                    <li class="nav-item">
                        <a class="nav-link <?php echo $page === "ActivityLog" ? "active" : ""; ?>" href="activity-log.php">
                            <i class="fe fe-activity"></i> Activity Log
                        </a>
                    </li>
                </ul>
                <?php endif; ?>

                <ul class="navbar-nav d-md-none">
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="offcanvas" href="#sidebarOffcanvasActivity" aria-controls="sidebarOffcanvasActivity">
                            <i class="fe fe-bell"></i> Notifications
                        </a>
                    </li>
                </ul>

                <div class="mt-auto"></div>

                <!-- Customize -->
                <div class="mb-4" id="popoverDemo" title="Make it yours" data-bs-content="Switch to Dark Mode to change the look of your dashboard.">
                    <a class="btn w-100 btn-primary" data-bs-toggle="offcanvas" href="#offcanvasDemo" aria-controls="offcanvasDemo">
                        <i class="fe fe-sliders me-2"></i> Customize
                    </a>
                </div>
                <div id="popoverDemoContainer" data-bs-theme="dark"></div>

                <div class="navbar-user d-none d-md-flex" id="sidebarUser">
                    <a class="navbar-user-link" data-bs-toggle="offcanvas" href="#sidebarOffcanvasActivity" aria-controls="sidebarOffcanvasActivity" title="Notifications">
                        <span class="icon"><i class="fe fe-bell"></i></span>
                    </a>
                    <div class="avatar avatar-sm avatar-online">
                        <img src="<?php echo e($avatar); ?>" class="avatar-img rounded-circle" alt="avatar" onerror="this.src='../assets/images/avatar.png'">
                    </div>
                    <a class="navbar-user-link" href="logout.php" title="Logout">
                        <span class="icon"><i class="fe fe-power"></i></span>
                    </a>
                </div>

            </div>
        </div>
    </nav>
</div>

<!-- Customize -->
<form class="offcanvas offcanvas-end" id="offcanvasDemo" tabindex="-1">
    <div class="offcanvas-body">
        <a class="btn-close" href="#" data-bs-dismiss="offcanvas" aria-label="Close"></a>

        <h2 class="mb-2">Make it yours</h2>
        <p class="mb-4">Preferences are saved to this browser.</p>
        <hr class="mb-4">

        <h4 class="mb-1">Color Scheme</h4>
        <p class="small text-body-secondary mb-3">Overall light or dark presentation.</p>
        <div class="btn-group-toggle row gx-2 mb-4">
            <div class="col">
                <input class="btn-check" name="colorScheme" id="colorSchemeLight" type="radio" value="light">
                <label class="btn w-100 btn-white" for="colorSchemeLight"><i class="fe fe-sun me-2"></i> Light Mode</label>
            </div>
            <div class="col">
                <input class="btn-check" name="colorScheme" id="colorSchemeDark" type="radio" value="dark">
                <label class="btn w-100 btn-white" for="colorSchemeDark"><i class="fe fe-moon me-2"></i> Dark Mode</label>
            </div>
        </div>

        <!-- Not offered in this app (single fixed layout) — kept in the DOM, hidden,
             so the shared theme.bundle.js (which looks these fields up unconditionally
             on every page) doesn't throw and break other scripts on the page. -->
        <div class="d-none">
            <div class="row gx-2">
                <div class="col">
                    <input class="btn-check" name="navPosition" id="navPositionSidenav" type="radio" value="sidenav" checked>
                    <label class="btn w-100 btn-white" for="navPositionSidenav">Sidenav</label>
                </div>
                <div class="col">
                    <input class="btn-check" name="navPosition" id="navPositionTopnav" type="radio" value="topnav">
                    <label class="btn w-100 btn-white" for="navPositionTopnav">Topnav</label>
                </div>
                <div class="col">
                    <input class="btn-check" name="navPosition" id="navPositionCombo" type="radio" value="combo">
                    <label class="btn w-100 btn-white" for="navPositionCombo">Combo</label>
                </div>
            </div>
            <div id="sidebarSizeContainer" class="row gx-2">
                <div class="col">
                    <input class="btn-check" name="sidebarSize" id="sidebarSizeBase" type="radio" value="base" checked>
                    <label class="btn w-100 btn-white" for="sidebarSizeBase">Fullsize</label>
                </div>
                <div class="col">
                    <input class="btn-check" name="sidebarSize" id="sidebarSizeSmall" type="radio" value="small">
                    <label class="btn w-100 btn-white" for="sidebarSizeSmall">Icons</label>
                </div>
            </div>
            <div class="row gx-2">
                <div class="col">
                    <input class="btn-check" name="navColor" id="navColorDefault" type="radio" value="default" checked>
                    <label class="btn w-100 btn-white" for="navColorDefault">Default</label>
                </div>
                <div class="col">
                    <input class="btn-check" name="navColor" id="navColorInverted" type="radio" value="inverted">
                    <label class="btn w-100 btn-white" for="navColorInverted">Inverted</label>
                </div>
                <div class="col">
                    <input class="btn-check" name="navColor" id="navColorVibrant" type="radio" value="vibrant">
                    <label class="btn w-100 btn-white" for="navColorVibrant">Vibrant</label>
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas-header">
        <button type="submit" class="btn w-100 btn-primary mt-auto">Preview</button>
    </div>
</form>

<!-- Notifications -->
<div class="offcanvas offcanvas-start" id="sidebarOffcanvasActivity" tabindex="-1">
    <div class="offcanvas-header">
        <h4 class="offcanvas-title">Notifications</h4>
    </div>
    <div class="offcanvas-body d-flex flex-column align-items-center justify-content-center text-center text-body-secondary">
        <i class="fe fe-bell" style="font-size:2rem;"></i>
        <p class="mt-3 mb-0">No new notifications.</p>
    </div>
</div>
