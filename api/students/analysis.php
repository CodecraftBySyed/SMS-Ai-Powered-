<?php
// api/students/analysis.php
require_once '../common/auth.php';
require_once '../common/db.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['student_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'student_id is required']);
        exit;
    }
    $studentId = (int)$_GET['student_id'];
    if ($studentId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid student_id']);
        exit;
    }

    // Basic student info with dept name
    $stmt = $pdo->prepare("
        SELECT s.id, s.reg_no, s.name, s.dept_id, d.name AS dept_name, s.year,
               s.parent_name, s.parent_mobile,
               s.total_fee, s.paid_fee, s.balance_fee, s.status
        FROM students s
        LEFT JOIN departments d ON s.dept_id = d.id
        WHERE s.id = ?
    ");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT AVG(percentage) AS avg_attendance FROM attendance WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $att = $stmt->fetch(PDO::FETCH_ASSOC);
    $attendance = round(floatval($att['avg_attendance'] ?? 0), 2);

    // Last 30 days attendance average
    $stmt = $pdo->prepare("SELECT AVG(percentage) AS last_month FROM attendance WHERE student_id = ? AND week_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $stmt->execute([$studentId]);
    $att2 = $stmt->fetch(PDO::FETCH_ASSOC);
    $attendanceLastMonth = round(floatval($att2['last_month'] ?? 0), 2);

    $colStmt = $pdo->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'marks'
    ");
    $colStmt->execute();
    $cols = array_map('strtolower', array_column($colStmt->fetchAll(PDO::FETCH_ASSOC), 'COLUMN_NAME'));
    
    $marks = [];
    $marksOverall = 0.0;
    if (in_array('marks_obtained', $cols, true) && in_array('subject_id', $cols, true)) {
        $sql = "
            SELECT sub.name AS subject_name, m.marks_obtained AS marks, 
                   " . (in_array('total_marks', $cols, true) ? "m.total_marks" : "100") . " AS total_marks
            FROM marks m
            JOIN subjects sub ON sub.id = m.subject_id
            WHERE m.student_id = ?
            ORDER BY sub.year, sub.code
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$studentId]);
        $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sum = 0; $cnt = 0;
        foreach ($marks as $row) {
            $t = floatval($row['total_marks'] ?: 100);
            if ($t > 0) { $sum += (floatval($row['marks']) / $t) * 100.0; $cnt++; }
        }
        $marksOverall = $cnt > 0 ? round($sum / $cnt, 2) : 0.0;
    } elseif (in_array('marks', $cols, true) && in_array('subject', $cols, true)) {
        $stmt = $pdo->prepare("
            SELECT subject AS subject_name, marks, 100 AS total_marks
            FROM marks
            WHERE student_id = ?
            ORDER BY year, subject
        ");
        $stmt->execute([$studentId]);
        $marks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sum = 0; $cnt = 0;
        foreach ($marks as $row) { $sum += floatval($row['marks']); $cnt++; }
        $marksOverall = $cnt > 0 ? round($sum / $cnt, 2) : 0.0;
    } else {
        $marks = [];
        $marksOverall = 0.0;
    }

    $stmt = $pdo->prepare("SELECT week_date, percentage FROM attendance WHERE student_id = ? ORDER BY week_date ASC LIMIT 12");
    $stmt->execute([$studentId]);
    $attRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'student' => $student,
        'attendance' => $attendance,
        'attendance_last_month' => $attendanceLastMonth,
        'attendance_records' => $attRows,
        'marks' => $marks,
        'marks_overall' => $marksOverall
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
