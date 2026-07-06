<?php
/** Streams a PNG QR code (encoding a certificate's verify URL) for logged-in admins. */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/includes/helpers.php";

if (!isset($_SESSION["admin_id"])) {
    http_response_code(403);
    exit;
}

$certNum = trim($_GET["certNum"] ?? "");
if ($certNum === "") {
    http_response_code(400);
    exit;
}

require_once __DIR__ . "/../vendor/autoload.php";

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Rendered at the exact requested pixel size so the browser never has to scale
// it (QR modules are high-frequency; non-integer downscaling moirés them into
// visual noise). Defaults to 512 for the print-quality download link.
$size = (int) ($_GET["size"] ?? 512);
$size = max(64, min(1024, $size));

$qr = new QrCode(data: verify_url($certNum), size: $size, margin: 10);
$result = (new PngWriter())->write($qr);

$safeName = preg_replace("/[^A-Za-z0-9._-]+/", "_", $certNum);
header("Content-Type: " . $result->getMimeType());
header("Content-Disposition: inline; filename=\"QR-{$safeName}.png\"");
echo $result->getString();
