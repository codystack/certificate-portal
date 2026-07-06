<?php
/**
 * Pure helpers shared by admin pages and JSON endpoints.
 * Does NOT start a session or open the DB — callers do that.
 */

/** HTML-escape a value for safe output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

/** Redirect and stop. */
function redirect(string $path): void
{
    header("Location: $path");
    exit;
}

/**
 * Public-facing base URL of the verification portal (with trailing slash).
 * Uses APP_BASE_URL in production; otherwise derives it from the current
 * request, treating the parent of /admin/ as the portal root.
 */
function public_base_url(): string
{
    if (defined("APP_BASE_URL") && APP_BASE_URL) {
        return rtrim(APP_BASE_URL, "/") . "/";
    }
    $scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
    $host = $_SERVER["HTTP_HOST"] ?? "localhost";
    // Anchor on "/admin/" so this works no matter how deep the calling
    // script is (a page in /admin/ or an endpoint in /admin/auth/create/).
    $script = $_SERVER["SCRIPT_NAME"] ?? "/admin/x";
    $pos = strpos($script, "/admin/");
    $root = $pos !== false ? substr($script, 0, $pos) : rtrim(dirname($script), "/\\");
    return $scheme . "://" . $host . $root . "/";
}

/** Build the QR verification URL for a certificate number. */
function verify_url(string $certNum): string
{
    return public_base_url() . "?cert=" . rawurlencode($certNum);
}

/** Emit a JSON response and stop (used by auth/ endpoints). */
function json_response(array $payload, int $code = 200): void
{
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($payload);
    exit;
}

/* ---------------------------------------------------------------- CSRF */

function csrf_token(): string
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}

/** Meta tag so JS fetch calls can read the token. */
function csrf_meta(): string
{
    return '<meta name="csrf-token" content="' . e(csrf_token()) . '">';
}

/** Hidden input for classic form posts. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/**
 * Verify the CSRF token from a POST field or the X-CSRF-Token header.
 * On a JSON endpoint, fails with a JSON 400; otherwise a plain 400.
 */
function verify_csrf(bool $json = false): void
{
    $sent = $_POST["csrf"] ?? ($_SERVER["HTTP_X_CSRF_TOKEN"] ?? "");
    if (!is_string($sent) || !hash_equals($_SESSION["csrf"] ?? "", $sent)) {
        if ($json) {
            json_response(["success" => false, "message" => "Invalid request token."], 400);
        }
        http_response_code(400);
        exit("Invalid request token. Please refresh and try again.");
    }
}

/* --------------------------------------------------------------- Flash */

function flash(string $message, string $type = "success"): void
{
    $_SESSION["flash"] = ["message" => $message, "type" => $type];
}

function take_flash(): ?array
{
    if (empty($_SESSION["flash"])) {
        return null;
    }
    $f = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    return $f;
}

/* -------------------------------------------------------- File uploads */

const UPLOAD_DIR = __DIR__ . "/../upload";
const UPLOAD_ALLOWED = [
    "pdf"  => "application/pdf",
    "jpg"  => "image/jpeg",
    "jpeg" => "image/jpeg",
    "png"  => "image/png",
];
const UPLOAD_MAX_BYTES = 10485760; // 10 MB

/**
 * Validate and store an uploaded certificate file.
 * Returns DB-relative path ("upload/<name>") or null (sets $error).
 */
function store_upload(array $file, ?string &$error): ?string
{
    if (($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = "File upload failed (code " . ($file["error"] ?? "?") . ").";
        return null;
    }
    if ($file["size"] > UPLOAD_MAX_BYTES) {
        $error = "File is too large (max 10 MB).";
        return null;
    }

    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!isset(UPLOAD_ALLOWED[$ext])) {
        $error = "Unsupported file type. Allowed: PDF, JPG, PNG.";
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file["tmp_name"]);
    finfo_close($finfo);
    if ($mime !== UPLOAD_ALLOWED[$ext]) {
        $error = "File contents do not match its extension.";
        return null;
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $base = preg_replace("/[^A-Za-z0-9._-]/", "_", pathinfo($file["name"], PATHINFO_FILENAME));
    $base = substr($base, 0, 40);
    $name = uniqid() . "_" . $base . "." . $ext;

    if (!move_uploaded_file($file["tmp_name"], UPLOAD_DIR . "/" . $name)) {
        $error = "Could not save the uploaded file.";
        return null;
    }

    $error = null;
    return "upload/" . $name;
}

/** Delete a stored upload referenced as "upload/<name>". */
function delete_upload(?string $relPath): void
{
    if (!$relPath) {
        return;
    }
    $full = realpath(UPLOAD_DIR . "/" . basename($relPath));
    $dir = realpath(UPLOAD_DIR);
    if ($full && $dir && strpos($full, $dir) === 0 && is_file($full)) {
        @unlink($full);
    }
}

/* ------------------------------------------------------------- Activity log */

/** Best-effort client IP, skipping private/reserved ranges from proxy headers. */
function client_ip(): string
{
    foreach (["HTTP_CLIENT_IP", "HTTP_X_FORWARDED_FOR", "REMOTE_ADDR"] as $key) {
        $val = $_SERVER[$key] ?? "";
        $ip = trim(explode(",", $val)[0]);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }
    }
    return $_SERVER["REMOTE_ADDR"] ?? "Unknown";
}

/**
 * Record an admin action in activity_logs. Never throws — a failed log
 * write must not break the action it's describing.
 */
function log_activity(
    mysqli $conn,
    int $adminId,
    string $action,
    string $module,
    ?int $targetId = null,
    ?string $description = null
): void {
    $ip = client_ip();
    $device = substr($_SERVER["HTTP_USER_AGENT"] ?? "Unknown device", 0, 255);
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO activity_logs (admin_id, action, module, target_id, description, ip_address, device_info) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        return;
    }
    mysqli_stmt_bind_param($stmt, "ississs", $adminId, $action, $module, $targetId, $description, $ip, $device);
    mysqli_stmt_execute($stmt);
}
