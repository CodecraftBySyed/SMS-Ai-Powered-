<?php
// api/teacher/stats.php
require_once '../common/auth.php';
require_once '../common/db.php';

header('Content-Type: application/json');

if ($_SESSION['role'] !== 'teacher') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$dept_id = $_SESSION['dept_id'];

try {
    // 1. Total Students in Dept
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE dept_id = ?");
    $stmt->execute([$dept_id]);
    $total_students = $stmt->fetchColumn();

    // 2. Pending Fees in Dept
    $stmt = $pdo->prepare("SELECT SUM(balance_fee) FROM students WHERE dept_id = ?");
    $stmt->execute([$dept_id]);
    $pending_fees = $stmt->fetchColumn() ?: 0;

    // 3. Needs Attention Count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE dept_id = ? AND status = 'Needs Attention'");
    $stmt->execute([$dept_id]);
    $needs_attention = $stmt->fetchColumn();

    // 4. Avg Attendance (Latest)
    $stmt = $pdo->prepare("
        SELECT AVG(percentage) 
        FROM attendance a 
        JOIN students s ON a.student_id = s.id 
        WHERE s.dept_id = ? 
        AND a.week_date = (SELECT MAX(week_date) FROM attendance)
    ");
    $stmt->execute([$dept_id]);
    $avg_attendance = number_format($stmt->fetchColumn() ?: 0, 1);

    // --- CHARTS DATA ---

    // Chart 1: Attendance Trend (Weeks vs %)
    $stmt = $pdo->prepare("
        SELECT a.week_date, AVG(a.percentage) as percentage
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        WHERE s.dept_id = ?
        GROUP BY a.week_date
        ORDER BY a.week_date ASC
        LIMIT 10
    ");
    $stmt->execute([$dept_id]);
    $attendance_trend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Chart 2: Fee Status (Paid/Pending/Overdue)
    // Paid: balance = 0
    // Pending: balance > 0
    // Overdue: Simplified as significant pending for this demo, or just split by ratio
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN balance_fee = 0 THEN 'Paid'
                WHEN balance_fee < (total_fee * 0.5) THEN 'Pending'
                ELSE 'Overdue'
            END as status,
            COUNT(*) as count
        FROM students
        WHERE dept_id = ?
        GROUP BY status
    ");
    $stmt->execute([$dept_id]);
    $fee_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Chart 3: Performance Bar (Good/Average/Needs Attention)
    // Try query with marks table; if it fails, fallback to attendance-only
    try {
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN avg_att < 75 OR avg_marks < 40 THEN 'Needs Attention'
                    WHEN avg_att < 85 OR avg_marks < 60 THEN 'Average'
                    ELSE 'Good'
                END as status,
                COUNT(*) as count
            FROM (
                SELECT 
                    s.id,
                    COALESCE(AVG(a.percentage), 0) as avg_att,
                    COALESCE(AVG(m.marks_obtained), 0) as avg_marks
                FROM students s
                LEFT JOIN attendance a ON s.id = a.student_id
                LEFT JOIN marks m ON s.id = m.student_id
                WHERE s.dept_id = ?
                GROUP BY s.id
            ) as student_stats
            GROUP BY status
        ");
        $stmt->execute([$dept_id]);
        $performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ePerf) {
        $stmt = $pdo->prepare("
            SELECT 
                CASE 
                    WHEN avg_att < 75 THEN 'Needs Attention'
                    WHEN avg_att < 85 THEN 'Average'
                    ELSE 'Good'
                END as status,
                COUNT(*) as count
            FROM (
                SELECT 
                    s.id,
                    COALESCE(AVG(a.percentage), 0) as avg_att
                FROM students s
                LEFT JOIN attendance a ON s.id = a.student_id
                WHERE s.dept_id = ?
                GROUP BY s.id
            ) as student_stats
            GROUP BY status
        ");
        $stmt->execute([$dept_id]);
        $performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Chart 4: Student Trend (Marks Average per Year)
    // Use students.year and fallback to empty if marks aggregation fails
    try {
        $stmt = $pdo->prepare("
            SELECT s.year, AVG(m.marks_obtained) as avg_marks
            FROM marks m
            JOIN students s ON m.student_id = s.id
            WHERE s.dept_id = ?
            GROUP BY s.year
            ORDER BY s.year ASC
        ");
        $stmt->execute([$dept_id]);
        $student_trend = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $eTrend) {
        $student_trend = [];
    }

    // Students performance list for dashboard (name, reg_no, year, avg_att, avg_marks, status)
    try {
        $stmt = $pdo->prepare("
            SELECT 
                s.id,
                s.name,
                s.reg_no,
                s.year,
                COALESCE(AVG(a.percentage), 0) as avg_att,
                COALESCE(AVG(m.marks_obtained), 0) as avg_marks,
                CASE 
                    WHEN COALESCE(AVG(a.percentage), 0) < 75 OR COALESCE(AVG(m.marks_obtained), 0) < 40 THEN 'Needs Attention'
                    WHEN COALESCE(AVG(a.percentage), 0) < 85 OR COALESCE(AVG(m.marks_obtained), 0) < 60 THEN 'Average'
                    ELSE 'Good'
                END as status
            FROM students s
            LEFT JOIN attendance a ON s.id = a.student_id
            LEFT JOIN marks m ON s.id = m.student_id
            WHERE s.dept_id = ?
            GROUP BY s.id, s.name, s.reg_no, s.year
            ORDER BY s.name ASC
            LIMIT 200
        ");
        $stmt->execute([$dept_id]);
        $students_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $eList) {
        $students_list = [];
    }

    echo json_encode([
        'metrics' => [
            'total_students' => $total_students,
            'pending_fees' => $pending_fees,
            'needs_attention' => $needs_attention,
            'avg_attendance' => $avg_attendance
        ],
        'charts' => [
            'attendance_trend' => $attendance_trend,
            'fee_status' => $fee_status,
            'performance' => $performance,
            'student_trend' => $student_trend
        ],
        'students' => $students_list
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
