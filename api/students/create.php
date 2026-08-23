<?php
// api/students/create.php
require_once '../common/auth.php';
require_once '../common/csrf.php';
require_once '../common/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    if (empty($data['name']) || empty($data['reg_no']) || empty($data['dept_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, Reg No, and Department are required']);
        exit;
    }

    try {
        // Duplicate reg_no check
        $dup = $pdo->prepare("SELECT id FROM students WHERE reg_no = ?");
        $dup->execute([$data['reg_no']]);
        if ($dup->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Duplicate registration number']);
            exit;
        }

        // Parent mobile normalization & validation (accepts 10 digits, 0XXXXXXXXXX, 91XXXXXXXXXX, +91XXXXXXXXXX)
        $parent_name = isset($data['parent_name']) ? trim($data['parent_name']) : null;
        $parent_mobile = null;
        if (isset($data['parent_mobile'])) {
            $digits = preg_replace('/\D/', '', (string)$data['parent_mobile']);
            if ($digits !== '') {
                if (strlen($digits) === 12 && substr($digits, 0, 2) === '91') {
                    $digits = substr($digits, -10);
                } elseif (strlen($digits) === 11 && $digits[0] === '0') {
                    $digits = substr($digits, -10);
                }
                if (strlen($digits) !== 10) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Parent mobile must be a valid 10-digit number']);
                    exit;
                }
                $parent_mobile = $digits;
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO students (reg_no, name, dept_id, year, phone, email, parent_name, parent_mobile, total_fee, paid_fee, balance_fee, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Average')
        ");
        
        // Calculate balance
        $total = $data['total_fee'] ?? 50000;
        $paid = $data['paid_fee'] ?? 0;
        $balance = $total - $paid;

        $stmt->execute([
            $data['reg_no'],
            $data['name'],
            $data['dept_id'],
            $data['year'] ?? 1,
            $data['phone'] ?? '',
            $data['email'] ?? '',
            $parent_name,
            $parent_mobile,
            $total,
            $paid,
            $balance
        ]);
        
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
