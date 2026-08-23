<?php
// api/subjects/create.php
ob_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../common/auth.php';
require_once __DIR__ . '/../common/db.php';

try {
    // Ensure subject_catalog exists (idempotent)
    $pdo->exec("CREATE TABLE IF NOT EXISTS subject_catalog (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_name VARCHAR(100) NOT NULL,
        department INT NULL,
        year INT NOT NULL,
        is_common TINYINT(1) NOT NULL DEFAULT 0,
        regulation_year INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department) REFERENCES departments(id)
    )");

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(401);
        echo json_encode(['error' => 'unauthorized']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $name = isset($data['subject_name']) ? trim((string)$data['subject_name']) : '';
    $isCommon = isset($data['is_common']) ? (int)!!$data['is_common'] : 0;
    $deptId = isset($data['department']) ? ($data['department'] === null ? null : (int)$data['department']) : null;
    $year = isset($data['year']) ? (int)$data['year'] : null;
    $regYear = isset($data['regulation_year']) ? (int)$data['regulation_year'] : (int)date('Y');

    if ($name === '' || !$year || !in_array($year, [1,2,3], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_input']);
        exit;
    }
    if ($isCommon === 1) {
        $deptId = null;
    } else {
        if (!$deptId || !in_array($deptId, [1,2,3,4], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_department']);
            exit;
        }
    }

    // Prevent simple duplicates for same dept/year/name
    $dup = $pdo->prepare("SELECT COUNT(*) FROM subject_catalog WHERE subject_name = ? AND year = ? AND " . ($isCommon ? "is_common = 1" : "(is_common = 0 AND department = ?)"));
    $dupParams = $isCommon ? [$name, $year] : [$name, $year, $deptId];
    $dup->execute($dupParams);
    if ((int)$dup->fetchColumn() > 0) {
        echo json_encode(['success' => true, 'duplicate' => true]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO subject_catalog (subject_name, department, year, is_common, regulation_year) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $deptId, $year, $isCommon, $regYear]);
    echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error']);
}
