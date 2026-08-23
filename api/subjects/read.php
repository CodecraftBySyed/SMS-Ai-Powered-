<?php
// api/subjects/read.php
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

    $deptId = isset($_GET['dept_id']) ? (int)$_GET['dept_id'] : null;
    $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
    $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

    if (!$year || !in_array($year, [1,2,3], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_year']);
        exit;
    }
    if ($deptId !== null && $deptId !== 0 && !in_array($deptId, [1,2,3,4], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'invalid_department']);
        exit;
    }

    $params = [];
    $where = [];

    if ($year === 1) {
        $where[] = "( (year = 1 AND is_common = 1) " . ($deptId ? " OR (year = 1 AND is_common = 0 AND department = ?)" : "") . " )";
        if ($deptId) $params[] = $deptId;
    } else {
        if (!$deptId) {
            http_response_code(400);
            echo json_encode(['error' => 'dept_required_for_year_2_3']);
            exit;
        }
        $where[] = "(year = ? AND is_common = 0 AND department = ?)";
        $params[] = $year;
        $params[] = $deptId;
    }

    if ($q !== '') {
        $where[] = "(subject_name LIKE ?)";
        $params[] = '%' . $q . '%';
    }

    $sql = "SELECT id, subject_name, department, year, is_common, regulation_year, created_at FROM subject_catalog";
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY year ASC, subject_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'server_error']);
}
