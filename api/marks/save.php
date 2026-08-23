<?php
require_once '../common/auth.php';
require_once '../common/csrf.php';
require_once '../common/db.php';
require_once '../common/constants.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['updates']) || !is_array($data['updates'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

// Ensure schema compatibility: marks.subject_id must exist
try {
    $stmt_check_col = $pdo->prepare("
        SELECT COUNT(*) 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'marks' AND COLUMN_NAME = 'subject_id'
    ");
    $stmt_check_col->execute([DB_NAME]);
    $has_subject_id = (int)$stmt_check_col->fetchColumn() === 1;
    if (!$has_subject_id) {
        http_response_code(500);
        echo json_encode([
            'error' => "Database schema mismatch: 'marks.subject_id' column is missing. Please run setup scripts (api/common/setup_full_db.php or api/common/setup_marks_tables.php) or add the column:\nALTER TABLE marks ADD COLUMN subject_id INT NOT NULL;"
        ]);
        exit;
    }
} catch (PDOException $e) {
    // If INFORMATION_SCHEMA isn't accessible, proceed and let the insert fail with a clear message
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO marks (student_id, subject_id, marks_obtained) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE marks_obtained = ?");
    $stmt_validate = $pdo->prepare("SELECT id FROM students WHERE id = ? AND dept_id = ?");

    foreach ($data['updates'] as $u) {
        $marks = $u['marks'];
        // Ensure marks is valid integer
        if (!is_numeric($marks)) continue; 
        
        // Validate Student
        $stmt_validate->execute([$u['student_id'], $_SESSION['dept_id']]);
        if (!$stmt_validate->fetch()) continue;

        $stmt->execute([$u['student_id'], $u['subject_id'], $marks, $marks]);
    }
    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
