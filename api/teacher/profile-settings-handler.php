<?php
require_once '../../includes/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireTeacher();

// Set JSON header
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get and sanitize input for separate name fields
$first_name = sanitize($_POST['first_name'] ?? '');
$middle_name = sanitize($_POST['middle_name'] ?? '');
$last_name = sanitize($_POST['last_name'] ?? '');
$contact_number = sanitize($_POST['contact_number'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$user_id = $_SESSION['user_id'];

// Validation
if (empty($first_name)) {
    echo json_encode(['success' => false, 'message' => 'First name is required.']);
    exit();
}

if (empty($last_name)) {
    echo json_encode(['success' => false, 'message' => 'Last name is required.']);
    exit();
}

if (empty($contact_number)) {
    echo json_encode(['success' => false, 'message' => 'Contact number is required.']);
    exit();
}

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit();
}

try {
    // Check if email is already taken by another user
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email is already in use by another account.']);
        exit();
    }
    
    // Construct full name for compatibility
    $full_name = $first_name;
    if (!empty($middle_name)) {
        $full_name .= ' ' . $middle_name;
    }
    $full_name .= ' ' . $last_name;
    
    // Update profile with separate name columns
    $stmt = $conn->prepare("
        UPDATE users 
        SET 
            full_name = ?,
            first_name = ?,
            middle_name = ?,
            last_name = ?,
            contact_number = ?,
            email = ?,
            updated_at = NOW()
        WHERE user_id = ? AND role = 'teacher'
    ");
    $stmt->execute([
        $full_name,
        $first_name,
        $middle_name,
        $last_name,
        $contact_number,
        $email,
        $user_id
    ]);
    
    // Update session
    $_SESSION['full_name'] = $full_name;
    $_SESSION['user_name'] = $full_name;
    $_SESSION['email'] = $email;
    
    // Log the action
    logAudit(
        'Updated profile',
        'update',
        'users',
        $user_id,
        'Updated profile: ' . $full_name,
        $user_id
    );
    
    echo json_encode([
        'success' => true, 
        'message' => 'Profile updated successfully!',
        'full_name' => $full_name,
        'email' => $email
    ]);
    
} catch (PDOException $e) {
    error_log("Update Profile Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>