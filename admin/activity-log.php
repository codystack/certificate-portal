<?php
$page = "ActivityLog";
include __DIR__ . "/components/head.php";

// Super Admin only.
if (($_SESSION["designation"] ?? "") !== "Super Admin") {
    flash("Super Admin access required.", "error");
    redirect("dashboard.php");
}
include __DIR__ . "/components/sidebar.php";

$rows = mysqli_query($conn, "
    SELECT al.action, al.module, al.target_id, al.description, al.ip_address, al.created_at,
           a.firstName, a.lastName, a.picture
    FROM activity_logs al
    INNER JOIN admin a ON a.id = al.admin_id
    ORDER BY al.created_at DESC
    LIMIT 500
");
$logs = $rows ? mysqli_fetch_all($rows, MYSQLI_ASSOC) : [];
$total = (int) (mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM activity_logs"))["c"] ?? 0);

$actionColours = [
    "logged_in"  => "success",
    "logged_out" => "secondary",
    "created"    => "primary",
    "updated"    => "warning",
    "deleted"    => "danger",
];
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle">Access</h6>
                        <h1 class="header-title">Activity Log</h1>
                    </div>
                    <div class="col-auto">
                        <span class="text-body-secondary"><?php echo number_format($total); ?> total entries</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php if (!empty($logs)): ?>
                <div class="card" data-list='{"valueNames": ["item-name", "item-action", "item-module", "item-description", "item-ip", "item-time"], "page": 25, "pagination": {"paginationClass": "list-pagination"}}' id="activityList">
                    <div class="card-header">
                        <div class="input-group input-group-flush input-group-merge input-group-reverse">
                            <input class="form-control list-search" type="search" placeholder="Search activity...">
                            <span class="input-group-text"><i class="fe fe-search"></i></span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-nowrap card-table">
                            <thead>
                                <tr>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-name" href="#">Admin</a></th>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-action" href="#">Action</a></th>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-module" href="#">Module</a></th>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-description" href="#">Description</a></th>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-ip" href="#">IP Address</a></th>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-time" href="#">Date &amp; Time</a></th>
                                </tr>
                            </thead>
                            <tbody class="list fs-base">
                                <?php foreach ($logs as $log):
                                    $colour = $actionColours[$log["action"]] ?? "secondary";
                                    $actionLabel = ucwords(str_replace("_", " ", $log["action"]));
                                    $avatar = $log["picture"] ? "upload/" . rawurlencode(basename($log["picture"])) : "../assets/images/avatar.png";
                                ?>
                                <tr>
                                    <td class="item-name">
                                        <div class="avatar avatar-xs align-middle me-2">
                                            <img class="avatar-img rounded-circle" src="<?php echo e($avatar); ?>" alt="" onerror="this.src='../assets/images/avatar.png'">
                                        </div>
                                        <?php echo e($log["firstName"] . " " . $log["lastName"]); ?>
                                    </td>
                                    <td class="item-action">
                                        <span class="badge bg-<?php echo $colour; ?>-subtle text-<?php echo $colour; ?>"><?php echo e($actionLabel); ?></span>
                                    </td>
                                    <td class="item-module">
                                        <?php echo e(ucfirst($log["module"])); ?>
                                        <?php if ($log["target_id"]): ?>
                                        <span class="text-body-secondary">#<?php echo (int) $log["target_id"]; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="item-description text-body-secondary" style="max-width:320px;white-space:normal;">
                                        <?php echo e($log["description"] ?? "—"); ?>
                                    </td>
                                    <td class="item-ip"><code><?php echo e($log["ip_address"] ?? "—"); ?></code></td>
                                    <td class="item-time"><?php echo e(date("d M Y, g:i A", strtotime($log["created_at"]))); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <ul class="list-pagination-prev pagination pagination-tabs card-pagination">
                            <li class="page-item"><a class="page-link ps-0 pe-4 border-end" href="#"><i class="fe fe-arrow-left me-1"></i> Prev</a></li>
                        </ul>
                        <ul class="list-pagination pagination pagination-tabs card-pagination"></ul>
                        <ul class="list-pagination-next pagination pagination-tabs card-pagination">
                            <li class="page-item"><a class="page-link ps-4 pe-0 border-start" href="#">Next <i class="fe fe-arrow-right ms-1"></i></a></li>
                        </ul>
                    </div>
                </div>
                <?php else: ?>
                <div class="card"><div class="card-body text-center my-5">
                    <i class="fe fe-activity" style="font-size:3rem;"></i>
                    <p class="mt-4 lead">No activity yet.</p>
                </div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . "/components/footer.php"; ?>
