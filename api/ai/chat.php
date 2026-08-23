<?php
// Prevent any output before JSON header
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Custom error handler to catch all errors as exceptions
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Custom exception handler
set_exception_handler(function($exception) {
    http_response_code(200);
    error_log("[EduSync Chat] Fatal Error: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
    echo json_encode([
        'status' => 'success',
        'response' => 'I couldn\'t process your request. Please try again.',
        'source' => 'fallback'
    ]);
    exit;
});

header('Content-Type: application/json; charset=utf-8');

// Import required files with error handling
try {
    require_once '../common/auth.php';
    require_once '../common/db.php';
    // Local-only bootstrap for .env if present
    @require_once __DIR__ . '/../../bootstrap.php';
    require_once '../../ai/ai_config.php';
    require_once './AIService.php';
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    error_log("[EduSync Chat] Import Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'code' => 'import_error',
        'response' => 'Configuration error. Please try again later.',
        'source' => 'internal'
    ]);
    exit;
}

// Clear any accidental output buffered during includes
ob_end_clean();

// Ensure user is authorized
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['teacher', 'admin'], true)) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'code' => 'unauthorized',
        'response' => 'Please log in to use the chatbot.',
        'source' => 'internal'
    ]);
    exit;
}

try {
    // Get POST data
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
    $userMessage = isset($data['message']) ? trim($data['message']) : '';

    if (empty($userMessage)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 'empty_message',
            'response' => 'Please enter a message.',
            'source' => 'internal'
        ]);
        exit;
    }

    // Validate Groq API key presence and format
    $groqKey = AIConfig::groqApiKey();
    if (!$groqKey || $groqKey === '') {
        http_response_code(401);
        error_log("[EduSync Chat] Missing GROQ_API_KEY");
        echo json_encode([
            'status' => 'error',
            'code' => 'missing_api_key',
            'response' => 'AI key not configured. Please set GROQ_API_KEY.',
            'source' => 'internal'
        ]);
        exit;
    }
    if (strpos($groqKey, 'gsk_') !== 0) {
        http_response_code(403);
        error_log("[EduSync Chat] Invalid GROQ_API_KEY format");
        echo json_encode([
            'status' => 'error',
            'code' => 'invalid_api_key_format',
            'response' => 'AI key format invalid. Please provide a valid Groq token.',
            'source' => 'internal'
        ]);
        exit;
    }

    $dbHelper = new AIDatabaseHelper($pdo);
    $schema = $dbHelper->validateStudentsSchema();
    if (!$schema['exists'] || !$schema['columns_ok'] || !$schema['primary_key_ok']) {
        error_log("[EduSync Chat] DB schema issue detected");
    }
    if ($schema['duplicate_reg_no'] > 0) {
        error_log("[EduSync Chat] Duplicate reg_no entries: " . (string)$schema['duplicate_reg_no']);
    }
    $aiService = new AIService($pdo, $_SESSION['user_id']);
    $result = $aiService->processMessage($userMessage);

    // Status mapping
    $src = $result['source'] ?? 'internal';
    if ($src === 'static' || $src === 'ai' || $src === 'student_lookup') {
        http_response_code(200);
    } else {
        // Internal fallback -> treat as upstream failure
        http_response_code(502);
        // Replace generic fallback with concise message
        $result = [
            'status' => 'error',
            'code' => 'upstream_failure',
            'response' => 'AI service is currently unavailable. Please try again later.',
            'source' => 'internal'
        ];
    }

    // Store conversation memory in session (last 10 exchanges)
    if (session_status() === PHP_SESSION_ACTIVE) {
        if (!isset($_SESSION['chat_history']) || !is_array($_SESSION['chat_history'])) {
            $_SESSION['chat_history'] = [];
        }
        $_SESSION['chat_history'][] = ['role' => 'user', 'content' => $userMessage, 'ts' => time()];
        $_SESSION['chat_history'][] = ['role' => 'assistant', 'content' => $result['response'] ?? '', 'ts' => time()];
        if (count($_SESSION['chat_history']) > 20) {
            $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -20);
        }
    }
    
    // Ensure proper JSON encoding with error handling
    $json = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        // JSON encoding failed - return safe response
        error_log("[EduSync Chat] JSON encode failed: " . json_last_error_msg());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'code' => 'json_encode_failed',
            'response' => 'Unable to format the response. Please try again.',
            'source' => 'internal'
        ]);
    } else {
        echo $json;
    }
    
} catch (Exception $e) {
    // Log error for debugging
    error_log("[EduSync Chat] Exception: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    
    // Return structured error
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 'internal_error',
        'response' => 'A server error occurred. Please try again later.',
        'source' => 'internal'
    ]);
}
?>
