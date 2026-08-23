<?php
require_once '../common/auth.php';
require_once '../common/db.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$dept_id = $_SESSION['dept_id'];

try {
    $stmt = $pdo->prepare("SELECT id, name, reg_no, total_fee, paid_fee, balance_fee FROM students WHERE dept_id = ? ORDER BY name");
    $stmt->execute([$dept_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($students);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>