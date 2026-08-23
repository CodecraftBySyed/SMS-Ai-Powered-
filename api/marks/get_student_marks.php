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
$subject_id = $_GET['subject_id'] ?? 0;

if (!$subject_id) {
    echo json_encode([]);
    exit;
}

try {
    $sql = "
        SELECT s.id, s.name, s.reg_no, m.marks_obtained 
        FROM students s
        LEFT JOIN marks m ON s.id = m.student_id AND m.subject_id = ?
        WHERE s.dept_id = ? AND s.year = ?
        ORDER BY s.name
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$subject_id, $dept_id, $year]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    // Fallback: return students list without marks if marks table or column is missing
    try {
        $sql = "
            SELECT s.id, s.name, s.reg_no, NULL as marks_obtained
            FROM students s
            WHERE s.dept_id = ? AND s.year = ?
            ORDER BY s.name
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dept_id, $year]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (PDOException $e2) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
