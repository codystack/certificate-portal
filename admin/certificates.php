<?php
$page = "Certificates";
include __DIR__ . "/components/head.php";
include __DIR__ . "/components/sidebar.php";

$q = trim($_GET["q"] ?? "");

if ($q !== "") {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, title, client, certNum, image, status, dateCreated, generated, type FROM certificate
         WHERE certNum LIKE ? OR title LIKE ? OR client LIKE ? ORDER BY dateCreated DESC"
    );
    $like = "%{$q}%";
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_stmt_get_result($stmt);
} else {
    $rows = mysqli_query(
        $conn,
        "SELECT id, title, client, certNum, image, status, dateCreated, generated, type FROM certificate ORDER BY dateCreated DESC"
    );
}
$certs = $rows ? mysqli_fetch_all($rows, MYSQLI_ASSOC) : [];
?>
<div class="main-content">
    <?php include __DIR__ . "/components/topbar.php"; ?>

    <div class="header">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-end">
                    <div class="col">
                        <h6 class="header-pretitle">Records</h6>
                        <h1 class="header-title">Certificates</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <?php if (!empty($certs)): ?>
                <div class="card" data-list='{"valueNames": ["item-num", "item-title", "item-client", "item-status"], "page": 10, "pagination": {"paginationClass": "list-pagination"}}' id="certList">
                    <div class="card-header">
                        <form>
                            <div class="input-group input-group-flush input-group-merge input-group-reverse">
                                <input class="form-control list-search" type="search" placeholder="Search certificates" value="<?php echo e($q); ?>">
                                <span class="input-group-text"><i class="fe fe-search"></i></span>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-nowrap card-table">
                            <thead>
                                <tr>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-num" href="#">Certificate No.</a></th>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-title" href="#">Title</a></th>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-client" href="#">Client</a></th>
                                    <th><a class="list-sort text-body-secondary" data-sort="item-status" href="#">Status</a></th>
                                    <th>File</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="list fs-base">
                                <?php foreach ($certs as $c): ?>
                                <tr>
                                    <td class="item-num"><code><?php echo e($c["certNum"]); ?></code></td>
                                    <td class="item-title"><?php echo e($c["title"]); ?></td>
                                    <td class="item-client"><?php echo e($c["client"]); ?></td>
                                    <td class="item-status">
                                        <span class="badge bg-<?php echo $c["status"] === "Active" ? "success" : "danger"; ?>-subtle text-<?php echo $c["status"] === "Active" ? "success" : "danger"; ?>">
                                            <?php echo e($c["status"]); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($c["image"])): ?>
                                            <a href="<?php echo e($c["image"]); ?>" target="_blank" rel="noopener"><i class="fe fe-file-text"></i></a>
                                        <?php else: ?>
                                            <span class="text-body-secondary">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-white show-qr"
                                            data-url="<?php echo e(verify_url($c["certNum"])); ?>"
                                            data-cert="<?php echo e($c["certNum"]); ?>"
                                            data-bs-toggle="modal" data-bs-target="#qrModal" title="QR code">
                                            <i class="fe fe-maximize"></i>
                                        </button>
                                        <?php if (!empty($c["generated"]) && !empty($c["type"])): ?>
                                        <a href="new-inspection.php?id=<?php echo (int) $c["id"]; ?>" class="btn btn-sm btn-white" title="Edit"><i class="fe fe-edit-2"></i></a>
                                        <?php else: ?>
                                        <a href="edit-certificate.php?id=<?php echo (int) $c["id"]; ?>" class="btn btn-sm btn-white" title="Edit"><i class="fe fe-edit-2"></i></a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-danger delete-cert"
                                            data-id="<?php echo (int) $c["id"]; ?>"
                                            data-name="<?php echo e($c["certNum"]); ?>"
                                            data-bs-toggle="modal" data-bs-target="#confirmActionModal">
                                            <i class="fe fe-trash"></i>
                                        </button>
                                    </td>
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
                    <i class="fe fe-inbox" style="font-size:3rem;"></i>
                    <?php if ($q !== ""): ?>
                    <p class="mt-4 lead">No certificates match "<?php echo e($q); ?>".</p>
                    <a href="certificates.php" class="btn btn-white">Clear search</a>
                    <?php else: ?>
                    <p class="mt-4 lead">No certificates yet.</p>
                    <?php endif; ?>
                </div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- QR code modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-card card" style="min-height:550px">
                <div class="card-header py-2">
                    <h4 class="card-header-title">Certificate QR Code</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="card-body text-center py-4 d-flex flex-column justify-content-center" style="max-height:none;overflow:visible">
                    <div id="qrCanvas" class="d-inline-block p-3 bg-white rounded mx-auto"></div>
                    <p class="text-body-secondary mt-3 mb-1">Scan to verify</p>
                    <p class="fw-bold mb-3" id="qrCertNum"></p>
                    <a href="#" id="qrDownload" class="btn btn-primary w-50 mx-auto" download>
                        <i class="fe fe-download"></i> Download PNG
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . "/modal/confirm-modal.php";
include __DIR__ . "/components/footer.php";
?>

<script>
// ---- QR code display + download (server-rendered PNG, see admin/qr.php) ----
document.addEventListener('DOMContentLoaded', () => {
    const qrCanvas   = document.getElementById('qrCanvas');
    const qrCertNum  = document.getElementById('qrCertNum');
    const qrDownload = document.getElementById('qrDownload');

    const DISPLAY_SIZE = 280;

    document.querySelectorAll('.show-qr').forEach(btn => {
        btn.addEventListener('click', () => {
            const cert = btn.dataset.cert;
            // Render at the exact physical pixel size (accounting for device
            // pixel ratio) so the browser displays it 1:1 instead of scaling
            // it — scaling a QR code's high-frequency pattern causes moiré.
            const previewSize = Math.round(DISPLAY_SIZE * (window.devicePixelRatio || 1));
            const previewSrc   = 'qr.php?certNum=' + encodeURIComponent(cert) + '&size=' + previewSize;
            const downloadSrc  = 'qr.php?certNum=' + encodeURIComponent(cert) + '&size=1024';
            qrCanvas.innerHTML = '<img src="' + previewSrc + '" alt="QR code" style="width:' + DISPLAY_SIZE + 'px;height:' + DISPLAY_SIZE + 'px;">';
            qrCertNum.textContent = cert;
            qrDownload.href = downloadSrc;
            qrDownload.setAttribute('download', 'QR-' + cert.replace(/[^A-Za-z0-9._-]+/g, '_') + '.png');
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let currentId = null;
    const confirmMessage = document.getElementById('confirmActionMessage');
    const confirmButton  = document.getElementById('confirmActionButton');

    document.querySelectorAll('.delete-cert').forEach(btn => {
        btn.addEventListener('click', () => {
            currentId = btn.dataset.id;
            confirmMessage.innerHTML = `You are about to permanently delete<br><b>${btn.dataset.name}</b>.<br>This cannot be undone.`;
            confirmButton.textContent = 'Delete Certificate';
            confirmButton.className = 'btn btn-danger btn-lg mb-4';
        });
    });

    confirmButton.addEventListener('click', async () => {
        if (!currentId) return;
        confirmButton.disabled = true;
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        try {
            const res = await fetch('auth/delete/certificate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': CSRF_TOKEN },
                body: new URLSearchParams({ id: currentId })
            });
            const data = await res.json();
            if (data.success) {
                notyf.success(data.message);
                setTimeout(() => window.location.reload(), 900);
            } else {
                notyf.error(data.message || 'Operation failed.');
            }
        } catch (e) {
            notyf.error('Network or server error.');
        }
        confirmButton.disabled = false;
        confirmButton.textContent = 'Delete Certificate';
    });
});
</script>
