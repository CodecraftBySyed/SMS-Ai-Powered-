<?php
/**
 * Local-only bootstrap for loading secrets from .env into server env.
 * Security boundaries:
 * - Never commits secrets: .env and this file are ignored by .git.
 * - Frontend never receives keys; this runs server-side only.
 * - In production, if key missing, exit with a clear warning.
 */
try {
    $isLocal = isset($_SERVER['SERVER_NAME']) ? (stripos($_SERVER['SERVER_NAME'], 'localhost') !== false) : true;
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        $vars = @parse_ini_file($envPath, false, INI_SCANNER_RAW);
        if (is_array($vars)) {
            foreach ($vars as $k => $v) {
                if (!isset($_SERVER[$k])) $_SERVER[$k] = $v;
                if (getenv($k) === false) putenv($k . '=' . $v);
            }
        }
    }
    $key = isset($_SERVER['GROQ_API_KEY']) ? (string)$_SERVER['GROQ_API_KEY'] : getenv('GROQ_API_KEY');
    if (!$isLocal && (!$key || $key === '')) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'code' => 'missing_api_key_production',
            'response' => 'Server misconfiguration: GROQ_API_KEY is missing.',
            'source' => 'internal'
        ]);
        exit;
    }
} catch (Throwable $e) {
    // Silent on parse errors; regular handlers will catch later if needed
}
?>
