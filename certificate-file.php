<?php
/**
 * Streams the verified certificate's PDF inline for public visitors, without
 * ever revealing the /admin/ path or the internal upload filename in the URL
 * (unlike linking straight to admin/upload/<file>.pdf).
 */
session_start();

if (!isset($_SESSION['certNum']) || !isset($_SESSION['image'])) {
    header('location: ./');
    exit;
}

$uploadDir = realpath(__DIR__ . '/admin/upload');
$full = $uploadDir ? realpath($uploadDir . '/' . basename($_SESSION['image'])) : false;

if (!$full || strpos($full, $uploadDir) !== 0 || !is_file($full)) {
    http_response_code(404);
    exit('Certificate file not found.');
}

$safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $_SESSION['certNum']) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $safeName . '"');
header('Content-Length: ' . filesize($full));
readfile($full);
