<?php
// api/common/departments.php
require_once 'auth.php';
require_once 'db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM departments");
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
