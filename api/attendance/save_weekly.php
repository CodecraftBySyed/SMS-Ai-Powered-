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

if (!isset($data['updates']) || !is_array($data['updates'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Validate student belongs to teacher's department
    $stmt_validate = $pdo->prepare("SELECT id FROM students WHERE id = ? AND dept_id = ?");
    // Update student status based on attendance percentage
    $stmt_update_status = $pdo->prepare("UPDATE students SET status = ? WHERE id = ?");

    $stmt_check = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND week_date = ?");
    $stmt_insert = $pdo->prepare("INSERT INTO attendance (student_id, week_date, present) VALUES (?, ?, ?)");
    $stmt_update = $pdo->prepare("UPDATE attendance SET present = ? WHERE id = ?");
    
    // For recalculating percentage
    $stmt_stats = $pdo->prepare("SELECT COUNT(*) as total, SUM(present) as present_count FROM attendance WHERE student_id = ?");
    $stmt_update_perc = $pdo->prepare("UPDATE attendance SET percentage = ? WHERE student_id = ?"); 
    // Updating ALL records for the student with the new percentage might be what is intended, 
    // or just the latest one. Given the data, it seems to be a snapshot. 
    // But to keep it consistent, maybe I should update the current record's percentage?
    // Let's just update the record we touched.
    $stmt_update_perc_record = $pdo->prepare("UPDATE attendance SET percentage = ? WHERE id = ?");

    foreach ($data['updates'] as $update) {
        $student_id = $update['student_id'];
        
        // Validate Student Dept
        $stmt_validate->execute([$student_id, $_SESSION['dept_id']]);
        if (!$stmt_validate->fetch()) {
            continue; // Skip invalid students
        }

        $date = $update['date'];
        $present = $update['present'] ? 1 : 0;

        // Check if exists
        $stmt_check->execute([$student_id, $date]);
        $existing = $stmt_check->fetch();

        if ($existing) {
            $stmt_update->execute([$present, $existing['id']]);
            $record_id = $existing['id'];
        } else {
            $stmt_insert->execute([$student_id, $date, $present]);
            $record_id = $pdo->lastInsertId();
        }

        // Recalculate Percentage
        $stmt_stats->execute([$student_id]);
        $stats = $stmt_stats->fetch();
        $percentage = ($stats['total'] > 0) ? ($stats['present_count'] / $stats['total']) * 100 : 0;
        
        // Update percentage for this record
        $stmt_update_perc_record->execute([$percentage, $record_id]);

        // Update Student Status based on Percentage
        $status = ($percentage < 75) ? 'Needs Attention' : 'Average';
        $stmt_update_status->execute([$status, $student_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
