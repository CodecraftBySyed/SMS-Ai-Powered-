<?php
// api/students/update.php
require_once '../common/auth.php';
require_once '../common/csrf.php';
require_once '../common/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID is required']);
        exit;
    }

    try {
        // Duplicate reg_no check if reg_no is changing
        if (isset($data['reg_no'])) {
            $chk = $pdo->prepare("SELECT id FROM students WHERE reg_no = ? AND id <> ?");
            $chk->execute([$data['reg_no'], $data['id']]);
            if ($chk->fetch()) {
                http_response_code(409);
                echo json_encode(['error' => 'Duplicate registration number']);
                exit;
            }
        }

        // Calculate balance if fees provided
        $fields = [];
        $params = [];

        if (isset($data['name'])) { $fields[] = 'name = ?'; $params[] = $data['name']; }
        if (isset($data['reg_no'])) { $fields[] = 'reg_no = ?'; $params[] = $data['reg_no']; }
        if (isset($data['dept_id'])) { $fields[] = 'dept_id = ?'; $params[] = $data['dept_id']; }
        if (isset($data['year'])) { $fields[] = 'year = ?'; $params[] = $data['year']; }
        if (isset($data['phone'])) { $fields[] = 'phone = ?'; $params[] = $data['phone']; }
        if (isset($data['email'])) { $fields[] = 'email = ?'; $params[] = $data['email']; }
        if (array_key_exists('parent_name', $data)) { $fields[] = 'parent_name = ?'; $params[] = $data['parent_name']; }
        if (array_key_exists('parent_mobile', $data)) {
            $input = $data['parent_mobile'];
            if ($input !== null && $input !== '') {
                $digits = preg_replace('/\D/', '', (string)$input);
                if ($digits !== '') {
                    if (strlen($digits) === 12 && substr($digits, 0, 2) === '91') {
                        $digits = substr($digits, -10);
                    } elseif (strlen($digits) === 11 && $digits[0] === '0') {
                        $digits = substr($digits, -10);
                    }
                }
                if (strlen($digits) !== 10) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Parent mobile must be a valid 10-digit number']);
                    exit;
                }
                $fields[] = 'parent_mobile = ?'; $params[] = $digits;
            } else {
                $fields[] = 'parent_mobile = ?'; $params[] = null;
            }
        }
        
        // Fee logic
        if (isset($data['total_fee']) || isset($data['paid_fee'])) {
            // Need current values if one is missing, but simpler to require both or fetch first.
            // Let's assume frontend sends both or we just update what's sent.
            // Actually, balance depends on both. 
            // Better to fetch current first if partial update.
            $stmt = $pdo->prepare("SELECT total_fee, paid_fee FROM students WHERE id = ?");
            $stmt->execute([$data['id']]);
            $current = $stmt->fetch();
            
            $total = $data['total_fee'] ?? $current['total_fee'];
            $paid = $data['paid_fee'] ?? $current['paid_fee'];
            $balance = $total - $paid;
            
            $fields[] = 'total_fee = ?'; $params[] = $total;
            $fields[] = 'paid_fee = ?'; $params[] = $paid;
            $fields[] = 'balance_fee = ?'; $params[] = $balance;
        }

        if (empty($fields)) {
            echo json_encode(['success' => true, 'message' => 'No changes']);
            exit;
        }

        $params[] = $data['id'];
        $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
