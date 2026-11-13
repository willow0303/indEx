<?php

require_once '../../includes/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

requireStudent();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get and sanitize inputs
    $first_name = sanitize($_POST['first_name'] ?? '');
    $middle_name = sanitize($_POST['middle_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $program = sanitize($_POST['program'] ?? '');
    $year_section = sanitize($_POST['year_section'] ?? '');
    $contact_number = sanitize($_POST['contact_number'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($program)) {
        $errors[] = 'Course/Program is required';
    }
    
    if (empty($year_section)) {
        $errors[] = 'Year & Section is required';
    }
    
    if (empty($contact_number)) {
        $errors[] = 'Contact number is required';
    } elseif (!preg_match('/^[\d\s\+\-\(\)]+$/', $contact_number)) {
        $errors[] = 'Invalid contact number format';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => implode(', ', $errors)
        ]);
        exit;
    }
    
    // Construct full name: First [Middle] Last
    $full_name = trim($first_name);
    if (!empty($middle_name)) {
        $full_name .= ' ' . trim($middle_name);
    }
    $full_name .= ' ' . trim($last_name);
    
    // Check if email is already used by another user
    $stmt = $conn->prepare("
        SELECT user_id 
        FROM users 
        WHERE email = ? AND user_id != ? AND role = 'student'
    ");
    $stmt->execute([$email, $user_id]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Email is already in use by another account'
        ]);
        exit;
    }
    
    // CRITICAL FIX: Update ALL name columns including first_name, middle_name, last_name
    $stmt = $conn->prepare("
        UPDATE users 
        SET 
            first_name = ?,
            middle_name = ?,
            last_name = ?,
            full_name = ?,
            program = ?,
            year_section = ?,
            contact_number = ?,
            email = ?,
            updated_at = NOW()
        WHERE user_id = ? AND role = 'student'
    ");
    
    $result = $stmt->execute([
        $first_name,      // NEW: Update first_name column
        $middle_name,     // NEW: Update middle_name column
        $last_name,       // NEW: Update last_name column
        $full_name,       // Update full_name column
        $program,
        $year_section,
        $contact_number,
        $email,
        $user_id
    ]);
    
    $rows_affected = $stmt->rowCount();
    
    error_log("=== Profile Update ===");
    error_log("User ID: $user_id");
    error_log("First Name: '$first_name'");
    error_log("Middle Name: '$middle_name'");
    error_log("Last Name: '$last_name'");
    error_log("Full Name: '$full_name'");
    error_log("Rows affected: $rows_affected");
    
    if ($result) {
        // Update session data
        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;
        
        // Log the action
        if (function_exists('logAudit')) {
            logAudit(
                'Updated profile information',
                'update',
                'users',
                $user_id,
                'Updated profile: ' . $full_name,
                $user_id
            );
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'full_name' => $full_name,
            'email' => $email,
            'debug' => [
                'first_name' => $first_name,
                'middle_name' => $middle_name,
                'last_name' => $last_name,
                'rows_affected' => $rows_affected
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes were made or update failed'
        ]);
    }
    
} catch (PDOException $e) {
    error_log("Update Profile Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred. Please try again.'
    ]);
    
} catch (Exception $e) {
    error_log("Update Profile Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred'
    ]);
}
?>