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
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('monday this week'));
$end_date = date('Y-m-d', strtotime($start_date . ' + 4 days')); // Mon-Fri

try {
    // 1. Get Students in Dept with latest percentage
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.reg_no,
        (SELECT percentage FROM attendance a WHERE a.student_id = s.id ORDER BY a.week_date DESC, a.id DESC LIMIT 1) as percentage
        FROM students s 
        WHERE s.dept_id = ? 
        ORDER BY s.name
    ");
    $stmt->execute([$dept_id]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get Attendance for the week
    $stmt = $pdo->prepare("
        SELECT student_id, week_date, present 
        FROM attendance 
        WHERE week_date BETWEEN ? AND ?
        AND student_id IN (SELECT id FROM students WHERE dept_id = ?)
    ");
    $stmt->execute([$start_date, $end_date, $dept_id]);
    $attendance_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize attendance by student_id -> date -> status
    $attendance_map = [];
    foreach ($attendance_records as $record) {
        $attendance_map[$record['student_id']][$record['week_date']] = $record['present'];
    }

    // 3. Attach attendance to students
    $dates = [];
    for ($i = 0; $i < 5; $i++) {
        $dates[] = date('Y-m-d', strtotime($start_date . " + $i days"));
    }

    foreach ($students as &$student) {
        $student['attendance'] = [];
        foreach ($dates as $date) {
            $student['attendance'][$date] = $attendance_map[$student['id']][$date] ?? null; // null means not marked
        }
    }

    echo json_encode(['students' => $students, 'dates' => $dates]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
