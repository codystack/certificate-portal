<?php
/**
 * Auth guard for JSON mutation endpoints (require_once at the very top).
 * Guarantees a logged-in admin and a valid CSRF token before any DB work.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../config/db.php";    // $conn (mysqli)
require_once __DIR__ . "/../includes/helpers.php";

header("Content-Type: application/json");

if (!isset($_SESSION["admin_id"])) {
    json_response(["success" => false, "message" => "Unauthorized. Please log in."], 401);
}

// All mutations are POST + CSRF protected.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(["success" => false, "message" => "Method not allowed."], 405);
}
verify_csrf(true);

/** Restrict an action to specific positions, e.g. require_role('Super Admin'). */
function require_role(string ...$roles): void
{
    if (!in_array($_SESSION["designation"] ?? "", $roles, true)) {
        json_response(["success" => false, "message" => "You do not have permission to do that."], 403);
    }
}
