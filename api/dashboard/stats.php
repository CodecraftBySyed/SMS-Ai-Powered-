<?php
// api/dashboard/stats.php
require_once '../common/auth.php';
require_once '../common/db.php';

header('Content-Type: application/json');

try {
    // 1. Total Students
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $totalStudents = $stmt->fetch()['count'];

    // 2. Pending Fees
    $stmt = $pdo->query("SELECT SUM(balance_fee) as total FROM students");
    $pendingFees = $stmt->fetch()['total'] ?? 0;

    // 3. Needs Attention
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students WHERE status = 'Needs Attention'");
    $needsAttention = $stmt->fetch()['count'];

    // 4. Low Attendance (< 75% Average)
    $stmt = $pdo->query("
        SELECT COUNT(*) as count FROM (
            SELECT student_id, AVG(percentage) as avg_att 
            FROM attendance 
            GROUP BY student_id 
            HAVING avg_att < 75
        ) as sub
    ");
    $lowAttendance = $stmt->fetch()['count'];

    // --- CHARTS DATA ---
    
    // Chart 1: Attendance Trend (Last 10 weeks)
    $stmt = $pdo->query("
        SELECT week_date, AVG(percentage) as percentage 
        FROM attendance 
        GROUP BY week_date 
        ORDER BY week_date ASC 
        LIMIT 10
    ");
    $attendanceTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Chart 2: Fee Status Distribution
    $stmt = $pdo->query("
        SELECT 
            CASE 
                WHEN balance_fee = 0 THEN 'Paid'
                WHEN balance_fee < (total_fee * 0.5) THEN 'Pending'
                ELSE 'Overdue'
            END as status,
            COUNT(*) as count
        FROM students
        GROUP BY status
    ");
    $feeStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Chart 3: Performance (Good/Average/Needs Attention)
    // Auto-calculated based on Attendance (<75% or Marks <40% -> Needs Attention)
    // Try query with marks table; if it fails, fallback to attendance-only
    try {
        $stmt = $pdo->query("
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
                GROUP BY s.id
            ) as student_stats
            GROUP BY status
        ");
        $performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ePerf) {
        $stmt = $pdo->query("
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
                GROUP BY s.id
            ) as student_stats
            GROUP BY status
        ");
        $performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Chart 4: Student Trend (Marks Average per Year)
    // Use students.year and fallback to empty if marks aggregation fails
    try {
        $stmt = $pdo->query("
            SELECT s.year as year, AVG(m.marks_obtained) as avg_marks
            FROM marks m
            JOIN students s ON m.student_id = s.id
            GROUP BY s.year
            ORDER BY s.year ASC
        ");
        $studentTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $eTrend) {
        $studentTrend = [];
    }

    echo json_encode([
        'metrics' => [
            'total_students' => $totalStudents,
            'pending_fees' => $pendingFees,
            'low_attendance' => $lowAttendance,
            'needs_attention' => $needsAttention
        ],
        'charts' => [
            'attendance_trend' => $attendanceTrend,
            'fee_status' => $feeStatus,
            'performance' => $performance,
            'student_trend' => $studentTrend
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
