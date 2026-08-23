<?php
// api/subjects/delete.php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db.php';

try {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['error' => 'unauthorized']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = isset($data['id']) ? (int)$data['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_id']);
        exit;
    }
    $check = $pdo->prepare("SELECT COUNT(*) FROM student_marks WHERE subject_id = ?");
    $check->execute([$id]);
    if ((int)$check->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'subject_in_use']);
        exit;
    }
    $del = $pdo->prepare("DELETE FROM subject_catalog WHERE id = ?");
    $del->execute([$id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error']);
}
