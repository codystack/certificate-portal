<?php
$page = "Certificates";
include __DIR__ . "/components/head.php";
include __DIR__ . "/components/sidebar.php";

$rows = mysqli_query(
    $conn,
    "SELECT id, title, client, certNum, image, status, dateCreated FROM certificate ORDER BY dateCreated DESC"
);
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
                    <div class="col-auto">
                        <a href="new-certificate.php" class="btn btn-primary lift">Add Certificate <i class="fe fe-plus"></i></a>
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
                                <input class="form-control list-search" type="search" placeholder="Search certificates">
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
                                        <a href="edit-certificate.php?id=<?php echo (int) $c["id"]; ?>" class="btn btn-sm btn-white"><i class="fe fe-edit-2"></i></a>
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
                    <p class="mt-4 lead">No certificates yet.</p>
                    <a href="new-certificate.php" class="btn btn-primary">Add the first one</a>
                </div></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- QR code modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-card card">
                <div class="card-header">
                    <h4 class="card-header-title">Certificate QR Code</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="card-body text-center">
                    <div id="qrCanvas" class="d-inline-block p-3 bg-white rounded"></div>
                    <p class="small text-body-secondary mt-3 mb-1">Scan to verify</p>
                    <p class="small fw-bold mb-3" id="qrCertNum"></p>
                    <a href="#" id="qrDownload" class="btn btn-primary w-100" download>
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

<script src="assets/js/qrcode.min.js"></script>
<script>
// ---- QR code generation + download ----
document.addEventListener('DOMContentLoaded', () => {
    const qrCanvas   = document.getElementById('qrCanvas');
    const qrCertNum  = document.getElementById('qrCertNum');
    const qrDownload = document.getElementById('qrDownload');

    document.querySelectorAll('.show-qr').forEach(btn => {
        btn.addEventListener('click', () => {
            const url  = btn.dataset.url;
            const cert = btn.dataset.cert;
            qrCanvas.innerHTML = '';
            // High-res for crisp printing; displayed scaled down via CSS.
            new QRCode(qrCanvas, { text: url, width: 512, height: 512, correctLevel: QRCode.CorrectLevel.M });
            qrCertNum.textContent = cert;

            // Resolve the generated image once it's rendered, then wire download.
            setTimeout(() => {
                const canvas = qrCanvas.querySelector('canvas');
                const img    = qrCanvas.querySelector('img');
                const data   = canvas ? canvas.toDataURL('image/png') : (img ? img.src : '');
                qrDownload.href = data;
                qrDownload.setAttribute('download', 'QR-' + cert.replace(/[^A-Za-z0-9._-]+/g, '_') + '.png');
                if (img) { img.style.width = '220px'; img.style.height = '220px'; }
                if (canvas) { canvas.style.width = '220px'; canvas.style.height = '220px'; }
            }, 60);
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
