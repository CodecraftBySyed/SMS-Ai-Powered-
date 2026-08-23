<?php
// api/students/read.php
require_once '../common/auth.php';
require_once '../common/db.php';

header('Content-Type: application/json');

try {
    $sql = "
        SELECT s.*, d.name as dept_name 
        FROM students s 
        LEFT JOIN departments d ON s.dept_id = d.id 
        ORDER BY s.created_at DESC
    ";
    $stmt = $pdo->query($sql);
    $students = $stmt->fetchAll();
    echo json_encode($students);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
