<?php
// api/auth/login.php
session_start();
require_once '../common/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // 1. CSRF Check
    if (empty($csrf_token) || $csrf_token !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token. Please refresh the page.']);
        exit;
    }

    // 2. Validation
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and Password are required']);
        exit;
    }

    try {
        // 3. Fetch User
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.password, u.role, u.dept_id, d.name as dept_name 
            FROM users u 
            LEFT JOIN departments d ON u.dept_id = d.id 
            WHERE u.email = :email
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // 4. Verify Credentials
        if ($user && password_verify($password, $user['password'])) {
            
            // 5. Role Verification
            if (!empty($role) && $user['role'] !== $role) {
                 echo json_encode(['success' => false, 'message' => "This account does not have $role privileges."]);
                 exit;
            }

            // 6. Session Security
            session_regenerate_id(true); // Prevent session fixation
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['dept_id'] = $user['dept_id'];
            $_SESSION['dept_name'] = $user['dept_name'];
            
            // 7. Redirect Logic
            $redirectRole = $user['role']; // Use actual DB role
            $redirectUrl = "dashboard.php?role=" . urlencode($redirectRole);
            
            echo json_encode(['success' => true, 'redirect' => $redirectUrl]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    } catch (Exception $e) {
        // Log error in production
        echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
