<?php
require_once '../common/auth.php';
require_once '../common/csrf.php';
require_once '../common/db.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['student_id']) || !isset($data['paid_fee'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$student_id = $data['student_id'];
$paid_fee = floatval($data['paid_fee']);

try {
    // Get total fee to calculate balance and verify dept
    $stmt = $pdo->prepare("SELECT total_fee FROM students WHERE id = ? AND dept_id = ?");
    $stmt->execute([$student_id, $_SESSION['dept_id']]); 
    $student = $stmt->fetch();

    if (!$student) {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found or access denied']);
        exit;
    }

    $total_fee = floatval($student['total_fee']);
    $balance_fee = $total_fee - $paid_fee;

    $update = $pdo->prepare("UPDATE students SET paid_fee = ?, balance_fee = ? WHERE id = ?");
    $update->execute([$paid_fee, $balance_fee, $student_id]);

    echo json_encode(['success' => true, 'balance_fee' => $balance_fee]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>