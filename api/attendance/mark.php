<?php
require_once '../common/auth.php';
require_once '../common/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['student_id']) || !isset($data['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Student ID and Status are required']);
        exit;
    }

    try {
        $date = date('Y-m-d');
        // Check if already marked
        $stmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
        $stmt->execute([$data['student_id'], $date]);
        
        if ($stmt->fetch()) {
            // Update
            $stmt = $pdo->prepare("UPDATE attendance SET status = ? WHERE student_id = ? AND date = ?");
            $stmt->execute([$data['status'], $data['student_id'], $date]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)");
            $stmt->execute([$data['student_id'], $date, $data['status']]);
        }
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
