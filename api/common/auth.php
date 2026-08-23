<?php
// api/common/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/csrf.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // If it's an API request, return JSON error
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    // Otherwise redirect to login
    header("Location: /edusync/public/index.php");
    exit;
}

function checkAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: /edusync/public/teacher_dashboard.php");
        exit;
    }
}

function checkTeacher() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
        header("Location: /edusync/public/dashboard.php");
        exit;
    }
}
?>
