<?php
/**
 * Minimal Resend (resend.com) email client — talks to their HTTP API
 * directly over cURL so no SDK/composer dependency is needed.
 * Requires RESEND_API_KEY and RESEND_FROM in .env.
 */

function send_email(string $to, string $subject, string $html): bool
{
    $apiKey = getenv("RESEND_API_KEY");
    $from   = getenv("RESEND_FROM");
    if (!$apiKey || !$from) {
        error_log("Resend: RESEND_API_KEY or RESEND_FROM is not configured.");
        return false;
    }

    $payload = json_encode([
        "from"    => $from,
        "to"      => [$to],
        "subject" => $subject,
        "html"    => $html,
    ]);

    $ch = curl_init("https://api.resend.com/emails");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json",
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error || $status < 200 || $status >= 300) {
        error_log("Resend: send failed (status $status): " . ($error ?: $response));
        return false;
    }
    return true;
}
