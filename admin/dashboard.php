<?php
$page = "Dashboard";
include __DIR__ . "/components/head.php";
include __DIR__ . "/components/sidebar.php";

// Stats
$stats = ["total" => 0, "active" => 0, "expired" => 0, "admins" => 0];
$res = mysqli_query($conn, "SELECT status, COUNT(*) c FROM certificate GROUP BY status");
while ($res && $row = mysqli_fetch_assoc($res)) {
    $stats["total"] += (int) $row["c"];
    if ($row["status"] === "Active")  { $stats["active"]  = (int) $row["c"]; }
    if ($row["status"] === "Expired") { $stats["expired"] = (int) $row["c"]; }
}
$stats["admins"] = (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM admin"))["c"] ?? 0);

$recent = mysqli_query($conn, "SELECT title, client, certNum, status, dateCreated FROM certificate ORDER BY dateCreated DESC LIMIT 8");

$cards = [
    ["Total Certificates", $stats["total"],   "fe-award",        "primary", "All time"],
    ["Active",             $stats["active"],  "fe-check-circle", "success", "Currently valid"],
    ["Expired",            $stats["expired"], "fe-x-circle",     "danger",  "Needs renewal"],
    ["Administrators",     $stats["admins"],  "fe-users",        "info",    "System users"],
];
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle">Overview</h6>
                        <h1 class="header-title">Dashboard</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <?php foreach ($cards as [$label, $value, $icon, $color, $caption]): ?>
            <div class="col-6 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center gx-0">
                            <div class="col">
                                <h6 class="text-uppercase text-body-secondary mb-2"><?php echo e($label); ?></h6>
                                <span class="h2 mb-0"><?php echo (int) $value; ?></span>
                            </div>
                            <div class="col-auto">
                                <div class="avatar avatar-sm">
                                    <div class="avatar-title fs-lg bg-<?php echo $color; ?>-subtle rounded-circle text-<?php echo $color; ?>">
                                        <i class="fe <?php echo $icon; ?>"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><small class="text-body-secondary"><?php echo e($caption); ?></small></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">Recent Certificates</h4>
                        <a href="certificates.php" class="btn btn-sm btn-primary">View all</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-nowrap card-table">
                            <thead>
                                <tr>
                                    <th>Certificate No.</th><th>Title</th><th>Client</th><th>Status</th><th>Created</th>
                                </tr>
                            </thead>
                            <tbody class="list fs-base">
                                <?php if ($recent && mysqli_num_rows($recent)): ?>
                                    <?php while ($c = mysqli_fetch_assoc($recent)): ?>
                                    <tr>
                                        <td><code><?php echo e($c["certNum"]); ?></code></td>
                                        <td><?php echo e($c["title"]); ?></td>
                                        <td><?php echo e($c["client"]); ?></td>
                                        <td><span class="badge bg-<?php echo $c["status"] === "Active" ? "success" : "danger"; ?>-subtle text-<?php echo $c["status"] === "Active" ? "success" : "danger"; ?>"><?php echo e($c["status"]); ?></span></td>
                                        <td><?php echo e(date("d M Y", strtotime($c["dateCreated"]))); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-body-secondary py-4">No certificates yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/components/footer.php"; ?>
