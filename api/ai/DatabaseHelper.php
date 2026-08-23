<?php
// api/ai/DatabaseHelper.php
// Abstraction layer for AI-specific database queries using prepared statements

class AIDatabaseHelper {
    private $pdo;

    /**
     * @param PDO $pdo
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get student by exact name match (case-insensitive).
     *
     * @param string $name
     * @return array|null
     */
    public function getStudentByName(string $name): ?array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.id, s.name, s.reg_no, s.email
                FROM students s
                WHERE LOWER(s.name) = LOWER(?)
                LIMIT 1
            ");
            $stmt->execute([$name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            // Convert false (no results) to null
            return $result !== false ? $result : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get student contact details from the students table.
     *
     * @param int $studentId
     * @return array{phone:?string,parent_name:?string,parent_phone:?string}
     */
    public function getStudentContacts(int $studentId): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.phone AS phone
                FROM students s
                WHERE s.id = ?
                LIMIT 1
            ");
            $stmt->execute([$studentId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            return [
                'phone' => $row['phone'] ?? null,
                'parent_name' => null,
                'parent_phone' => null
            ];
        } catch (Exception $e) {
            return [
                'phone' => null,
                'parent_name' => null,
                'parent_phone' => null
            ];
        }
    }

    /**
     * Get student attendance percentage (average across all records).
     *
     * @param int $studentId
     * @return float
     */
    public function getStudentAttendance(int $studentId): float {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(AVG(percentage), 0) as avg_attendance
                FROM attendance
                WHERE student_id = ?
            ");
            $stmt->execute([$studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return round((float)($result['avg_attendance'] ?? 0), 2);
        } catch (Exception $e) {
            return 0.0;
        }
    }

    /**
     * Get student average marks.
     *
     * @param int $studentId
     * @return float
     */
    public function getStudentMarks(int $studentId): float {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COALESCE(AVG(marks), 0) as avg_marks
                FROM marks
                WHERE student_id = ?
            ");
            $stmt->execute([$studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return round((float)($result['avg_marks'] ?? 0), 2);
        } catch (Exception $e) {
            return 0.0;
        }
    }

    /**
     * Get student academic status based on marks.
     *
     * @param int $studentId
     * @return string
     */
    public function getStudentStatus(int $studentId): string {
        try {
            $stmt = $this->pdo->prepare("
                SELECT AVG(marks) as avg_marks
                FROM marks
                WHERE student_id = ?
            ");
            $stmt->execute([$studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $avg = (float)($result['avg_marks'] ?? 0);

            if ($avg >= 75) return 'Excellent';
            if ($avg >= 60) return 'Good';
            if ($avg >= 50) return 'Average';
            return 'Needs Improvement';
        } catch (Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get student fee information.
     *
     * @param int $studentId
     * @return array{total:float,paid:float,balance:float}
     */
    public function getStudentFees(int $studentId): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.total_fee,
                    s.paid_fee,
                    s.balance_fee
                FROM students s
                WHERE s.id = ?
            ");
            $stmt->execute([$studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return ['total' => 0, 'paid' => 0, 'balance' => 0];
            }

            return [
                'total' => (float)($result['total_fee'] ?? 0),
                'paid' => (float)($result['paid_fee'] ?? 0),
                'balance' => (float)($result['balance_fee'] ?? 0)
            ];
        } catch (Exception $e) {
            return ['total' => 0, 'paid' => 0, 'balance' => 0];
        }
    }

    /**
     * Get students with low attendance (below threshold).
     *
     * @param float $threshold
     * @param int $limit
     * @return array<int,array>
     */
    public function getLowAttendanceStudents(float $threshold = 75, int $limit = 10): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.id, s.name, s.reg_no,
                    AVG(a.percentage) as avg_attendance
                FROM students s
                LEFT JOIN attendance a ON s.id = a.student_id
                WHERE a.percentage IS NOT NULL
                GROUP BY s.id
                HAVING AVG(a.percentage) < ?
                ORDER BY AVG(a.percentage) ASC
                LIMIT ?
            ");
            $stmt->execute([$threshold, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get students with pending fees.
     *
     * @param int $limit
     * @return array<int,array>
     */
    public function getStudentsWithPendingFees(int $limit = 10): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.id, s.name, s.reg_no,
                    s.balance_fee
                FROM students s
                WHERE s.balance_fee > 0
                ORDER BY s.balance_fee DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get class statistics.
     *
     * @return array{total_students:int,avg_attendance:float,avg_marks:float,fee_collected:float}
     */
    public function getClassStatistics(): array {
        try {
            $stats = [
                'total_students' => 0,
                'avg_attendance' => 0,
                'avg_marks' => 0,
                'fee_collected' => 0
            ];

            // Total students
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM students");
            $stmt->execute([]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_students'] = (int)($result['count'] ?? 0);

            // Average attendance
            $stmt = $this->pdo->prepare("SELECT AVG(percentage) as avg FROM attendance WHERE created_at > DATE_SUB(NOW(), INTERVAL 3 MONTH)");
            $stmt->execute([]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['avg_attendance'] = round((float)($result['avg'] ?? 0), 2);

            // Average marks
            $stmt = $this->pdo->prepare("SELECT AVG(marks) as avg FROM student_marks WHERE created_at > DATE_SUB(NOW(), INTERVAL 3 MONTH)");
            $stmt->execute([]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['avg_marks'] = round((float)($result['avg'] ?? 0), 2);

            // Fee collected
            $stmt = $this->pdo->prepare("SELECT SUM(paid_fee) as total FROM students");
            $stmt->execute([]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['fee_collected'] = (float)($result['total'] ?? 0);

            return $stats;
        } catch (Exception $e) {
            return [
                'total_students' => 0,
                'avg_attendance' => 0,
                'avg_marks' => 0,
                'fee_collected' => 0
            ];
        }
    }

    /**
     * Verify database connection is working.
     *
     * @return bool
     */
    public function isConnected(): bool {
        try {
            $stmt = $this->pdo->prepare("SELECT 1");
            $stmt->execute([]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get subject-wise average marks for a student from student_marks + subject_catalog.
     *
     * @param int $studentId
     * @return array<int,array{subject_name:string,avg_marks:float}>
     */
    public function getStudentSubjectAverages(int $studentId): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT sc.subject_name, COALESCE(AVG(sm.marks),0) AS avg_marks
                FROM student_marks sm
                JOIN subject_catalog sc ON sm.subject_id = sc.id
                WHERE sm.student_id = ?
                GROUP BY sc.subject_name
                ORDER BY sc.subject_name ASC
            ");
            $stmt->execute([$studentId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as &$r) {
                $r['avg_marks'] = round((float)$r['avg_marks'], 2);
            }
            return $rows ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
    /**

    /**
     * Validate students table schema and data integrity.
     *
     * @return array{exists:bool, columns_ok:bool, primary_key_ok:bool, duplicate_reg_no:int}
     */
    public function validateStudentsSchema(): array {
        $exists = false;
        $columnsOk = false;
        $pkOk = false;
        $dupCount = 0;
        try {
            $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'students'");
            $stmt->execute([]);
            $exists = $stmt->fetchColumn() !== false;
            if ($exists) {
                $stmt2 = $this->pdo->prepare("SHOW COLUMNS FROM students");
                $stmt2->execute([]);
                $cols = $stmt2->fetchAll(PDO::FETCH_COLUMN);
                $required = ['id','reg_no','name','dept_id','year','phone','email','total_fee','paid_fee','balance_fee','status','created_at'];
                $columnsOk = !array_diff($required, $cols);
                $stmt3 = $this->pdo->prepare("SHOW INDEX FROM students WHERE Key_name = 'PRIMARY'");
                $stmt3->execute([]);
                $pkOk = $stmt3->fetchColumn() !== false;
                $stmt4 = $this->pdo->prepare("SELECT reg_no, COUNT(*) c FROM students GROUP BY reg_no HAVING c > 1");
                $stmt4->execute([]);
                $dupCount = count($stmt4->fetchAll(PDO::FETCH_ASSOC));
            }
        } catch (Exception $e) {
            $exists = false;
            $columnsOk = false;
            $pkOk = false;
            $dupCount = 0;
        }
        return [
            'exists' => $exists,
            'columns_ok' => $columnsOk,
            'primary_key_ok' => $pkOk,
            'duplicate_reg_no' => $dupCount
        ];
    }
}
?>
