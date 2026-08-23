<?php
require_once __DIR__ . '/../api/ai/DatabaseHelper.php';

function fetch_student_db(PDO $pdo, string $student_name): array {
    $helper = new AIDatabaseHelper($pdo);
    $student = null;
    try {
        $stmt = $pdo->prepare("
            SELECT s.id, s.name, s.reg_no, s.year, d.name AS dept_name
            FROM students s
            LEFT JOIN departments d ON s.dept_id = d.id
            WHERE LOWER(s.name) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$student_name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $student = $row !== false ? $row : null;
        if ($student === null) {
            $normalized = preg_replace('/[^a-zA-Z\s]/', ' ', $student_name);
            $normalized = preg_replace('/\s{2,}/', ' ', trim($normalized));
            if ($normalized !== '') {
                $like = '%' . $normalized . '%';
                $stmt2 = $pdo->prepare("
                    SELECT s.id, s.name, s.reg_no, s.year, d.name AS dept_name
                    FROM students s
                    LEFT JOIN departments d ON s.dept_id = d.id
                    WHERE LOWER(s.name) LIKE LOWER(?)
                    ORDER BY LENGTH(s.name) DESC
                    LIMIT 1
                ");
                $stmt2->execute([$like]);
                $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                $student = $row2 !== false ? $row2 : null;
            }
        }
    } catch (Exception $e) {
        $student = null;
    }
    if ($student === null) {
        if (getenv('AI_DEBUG') === '1') {
            error_log("[EduSync AI] fetch_student_db: not found for input=" . substr($student_name, 0, 80));
        }
        return [
            'found' => false,
            'message' => 'student not found'
        ];
    }
    $sid = (int)$student['id'];
    $attendance = 0.0;
    $marks = 0.0;
    $status = 'Unknown';
    $fees = ['total' => 0.0, 'paid' => 0.0, 'balance' => 0.0];
    try {
        $attendance = $helper->getStudentAttendance($sid);
    } catch (Exception $e) {
        $attendance = 0.0;
    }
    try {
        $marks = $helper->getStudentMarks($sid);
    } catch (Exception $e) {
        $marks = 0.0;
    }
    try {
        $status = $helper->getStudentStatus($sid);
    } catch (Exception $e) {
        $status = 'Unknown';
    }
    try {
        $fees = $helper->getStudentFees($sid);
    } catch (Exception $e) {
        $fees = ['total' => 0.0, 'paid' => 0.0, 'balance' => 0.0];
    }
    $pendingAmount = (float)($fees['balance'] ?? 0.0);
    $feesStatus = $pendingAmount > 0 ? 'Pending' : 'Paid';
    $subjectScores = [];
    $strengths = [];
    $weaknesses = [];
    try {
        $subjectScores = $helper->getStudentSubjectAverages($sid);
        if (!empty($subjectScores)) {
            $sorted = $subjectScores;
            usort($sorted, function($a,$b){ return ($b['avg_marks'] <=> $a['avg_marks']); });
            $strengths = array_slice(array_map(fn($r)=>$r['subject_name'], $sorted), 0, 2);
            $weaknesses = array_slice(array_map(fn($r)=>$r['subject_name'], array_reverse($sorted)), 0, 2);
        }
    } catch (Exception $e) {
        $subjectScores = [];
    }
    return [
        'found' => true,
        'student_name' => $student['name'] ?? '',
        'reg_no' => $student['reg_no'] ?? '',
        'department' => $student['dept_name'] ?? '',
        'class' => isset($student['year']) ? 'Year ' . (string)$student['year'] : '',
        'attendance' => $attendance,
        'marks' => $marks,
        'status' => $status,
        'subject_scores' => $subjectScores,
        'subject_strengths' => $strengths,
        'subject_weaknesses' => $weaknesses,
        'pending_amount' => $pendingAmount,
        'fees_status' => $feesStatus,
        'fee_status' => $feesStatus
    ];
}
?>
