<?php
/**
 * Certificate PDF generation: renders an inspection-type template to a
 * branded PDF (with an embedded QR code) and stores it under admin/upload/.
 */
require_once __DIR__ . "/../../vendor/autoload.php";
require_once __DIR__ . "/inspection_types.php";
require_once __DIR__ . "/../includes/helpers.php";

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/** Build a QR PNG (data: URI) encoding the public verification URL. */
function qr_data_uri(string $text): string
{
    $qr = new QrCode(data: $text, size: 320, margin: 10);
    return (new PngWriter())->write($qr)->getDataUri();
}

/** Company logo (admin/assets/img/logo-dark.png) as a data: URI, for the certificate header. */
function logo_data_uri(): string
{
    static $uri = null;
    if ($uri === null) {
        $path = __DIR__ . "/../assets/img/logo-dark.png";
        $uri = "data:image/png;base64," . base64_encode(file_get_contents($path));
    }
    return $uri;
}

/**
 * Read a stored upload ("upload/<name>") and return it as a data: URI so
 * Dompdf (isRemoteEnabled=false) can embed it. Returns null if missing.
 */
function upload_data_uri(?string $relPath): ?string
{
    if (!$relPath) {
        return null;
    }
    $full = realpath(UPLOAD_DIR . "/" . basename($relPath));
    $dir = realpath(UPLOAD_DIR);
    if (!$full || !$dir || strpos($full, $dir) !== 0 || !is_file($full)) {
        return null;
    }
    $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $full) ?: "image/png";
    return "data:{$mime};base64," . base64_encode(file_get_contents($full));
}

/**
 * Generate the PDF for a certificate array (must include 'type', 'certNum',
 * and decoded 'details'). Returns the DB-relative path "upload/<name>.pdf".
 * Throws RuntimeException if the type has no template.
 */
function generate_certificate_pdf(array $cert): string
{
    $typeKey = $cert["type"] ?? "";
    $def = inspection_type($typeKey);
    $templateFile = __DIR__ . "/../templates/" . basename($typeKey) . ".php";
    if (!$def || !is_file($templateFile)) {
        throw new RuntimeException("No PDF template for inspection type: " . $typeKey);
    }
    require_once $templateFile;
    $renderer = "render_" . $typeKey . "_template";
    if (!function_exists($renderer)) {
        throw new RuntimeException("Template renderer missing: $renderer");
    }

    $qr = qr_data_uri(verify_url($cert["certNum"]));
    $cert["signature_img"] = upload_data_uri($cert["inspector_signature"] ?? null);
    $cert["stamp_img"] = upload_data_uri($cert["company_stamp"] ?? null);
    $html = $renderer($cert, $def, $qr);

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    // Dompdf subsets fonts into temp files during render(); the system temp dir
    // (sys_get_temp_dir()) is a per-user macOS folder that the web server user
    // can't write to, which makes font subsetting fail with a bare
    // "ValueError: Path cannot be empty". Point it at a dir we control instead.
    $tempDir = UPLOAD_DIR . "/tmp";
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    $options = new Options();
    $options->set("isRemoteEnabled", false);   // only inline data: images
    $options->set("defaultFont", "DejaVu Sans"); // ships with Dompdf, has ✓
    $options->set("tempDir", $tempDir);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper($def["paper"] ?? "A4", $def["orientation"] ?? "portrait");
    $dompdf->render();
    $safe = preg_replace("/[^A-Za-z0-9._-]+/", "_", $cert["certNum"]);
    $name = "upload/" . uniqid() . "_" . substr($safe, 0, 40) . ".pdf";
    file_put_contents(UPLOAD_DIR . "/" . basename($name), $dompdf->output());

    return $name;
}
