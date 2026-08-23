<?php
// api/teachers/read.php
require_once '../common/auth.php';
require_once '../common/db.php';

header('Content-Type: application/json');

try {
    $sql = "
        SELECT u.*, d.name as dept_name 
        FROM users u 
        LEFT JOIN departments d ON u.dept_id = d.id 
        WHERE u.role = 'teacher' 
        ORDER BY u.created_at DESC
    ";
    $stmt = $pdo->query($sql);
    $teachers = $stmt->fetchAll();
    
    // Remove password from response
    foreach ($teachers as &$teacher) {
        unset($teacher['password']);
    }
    
    echo json_encode($teachers);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
