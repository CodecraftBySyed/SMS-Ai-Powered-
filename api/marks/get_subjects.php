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
$year = $_GET['year'] ?? 1;

try {
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE dept_id = ? AND year = ?");
    $stmt->execute([$dept_id, $year]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>