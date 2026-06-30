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
    $html = $renderer($cert, $def, $qr);

    $options = new Options();
    $options->set("isRemoteEnabled", false);   // only inline data: images
    $options->set("defaultFont", "DejaVu Sans"); // ships with Dompdf, has ✓
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper($def["paper"] ?? "A4", $def["orientation"] ?? "portrait");
    $dompdf->render();

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $safe = preg_replace("/[^A-Za-z0-9._-]+/", "_", $cert["certNum"]);
    $name = "upload/" . uniqid() . "_" . substr($safe, 0, 40) . ".pdf";
    file_put_contents(UPLOAD_DIR . "/" . basename($name), $dompdf->output());

    return $name;
}
