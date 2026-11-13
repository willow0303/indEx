<?php
require_once '../../includes/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireStudent();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get input
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current_password)) {
    echo json_encode(['success' => false, 'message' => 'Current password is required']);
    exit();
}

if (empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'New password is required']);
    exit();
}

if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit();
}

try {
    // Get current password hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ? AND role = 'student'");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }

    // Verify current password
    if (!verifyPassword($current_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
        exit();
    }

    // Hash new password
    $hashed_password = hashPassword($new_password);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ? AND role = 'student'");
    $stmt->execute([$hashed_password, $user_id]);

    // Log action - CORRECT parameter order
    logAudit(
        'Changed password',
        'update',
        'users',
        $user_id,
        'Password changed from IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'),
        $user_id
    );

    echo json_encode([
        'success' => true,
        'message' => 'Password changed successfully. Please log in again.'
    ]);

} catch (PDOException $e) {
    error_log("Change Password Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']);
}
?>