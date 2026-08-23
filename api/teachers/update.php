<?php
// api/teachers/update.php
require_once '../common/auth.php';
require_once '../common/csrf.php';
require_once '../common/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required']);
        exit;
    }

    try {
        $fields = [];
        $params = [];

        if (isset($data['name'])) { $fields[] = 'name = ?'; $params[] = $data['name']; }
        if (isset($data['email'])) { $fields[] = 'email = ?'; $params[] = $data['email']; }
        if (isset($data['dept_id'])) { $fields[] = 'dept_id = ?'; $params[] = $data['dept_id']; }
        if (isset($data['password']) && !empty($data['password'])) { 
            $fields[] = 'password = ?'; 
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT); 
        }

        if (empty($fields)) {
            echo json_encode(['success' => true, 'message' => 'No changes']);
            exit;
        }

        $params[] = $data['id'];
        // Ensure we only update teachers
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ? AND role = 'teacher'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
