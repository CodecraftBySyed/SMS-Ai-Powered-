<?php
// api/teachers/create.php
require_once '../common/auth.php';
require_once '../common/csrf.php';
require_once '../common/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    if (empty($data['name']) || empty($data['email']) || empty($data['password']) || empty($data['dept_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, Email, Password, and Department are required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, dept_id) 
            VALUES (?, ?, ?, 'teacher', ?)
        ");
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt->execute([
            $data['name'],
            $data['email'],
            $hashed_password,
            $data['dept_id']
        ]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
