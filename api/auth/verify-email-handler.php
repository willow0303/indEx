<?php

require_once '../../includes/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithMessage(BASE_URL . 'auth/verify-email.php', 'danger', 'Invalid request method.');
}

if (!isset($_SESSION['pending_registration'])) {
    redirectWithMessage(BASE_URL . 'auth/login.php', 'danger', 'No pending registration found. Please register again.');
}

$pending = $_SESSION['pending_registration'];

$entered_code = '';
for ($i = 1; $i <= 6; $i++) {
    $entered_code .= trim($_POST['digit' . $i] ?? '');
}

if (empty($entered_code) || strlen($entered_code) !== 6) {
    redirectWithMessage(BASE_URL . 'auth/verify-email.php', 'danger', 'Please enter the complete 6-digit verification code.');
}

if (strtotime($pending['expires_at']) < time()) {
    unset($_SESSION['pending_registration']);
    redirectWithMessage(BASE_URL . 'auth/login.php', 'danger', 'Verification code has expired. Please register again.');
}

if (strtoupper($entered_code) !== strtoupper($pending['verification_code'])) {
    redirectWithMessage(BASE_URL . 'auth/verify-email.php', 'danger', 'Invalid verification code. Please try again.');
}

try {
    error_log("=== VERIFICATION DEBUG START ===");
    error_log("Role: " . $pending['role']);
    error_log("Email: " . $pending['email']);
    error_log("Full Name: " . $pending['full_name']);
    error_log("First Name: " . $pending['first_name']);
    error_log("Middle Name: " . ($pending['middle_name'] ?? 'NULL'));
    error_log("Last Name: " . $pending['last_name']);
    error_log("Contact Number: " . ($pending['contact_number'] ?? 'NULL'));
    error_log("Code ID: " . ($pending['code_id'] ?? 'NULL'));
    
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("
        INSERT INTO users (
            email, 
            password, 
            full_name,
            first_name,
            middle_name,
            last_name,
            role, 
            contact_number, 
            student_number, 
            program, 
            year_section,
            profile_picture,
            status, 
            email_verified,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', TRUE, NOW())
    ");
    
    $middle_name_value = !empty($pending['middle_name']) ? $pending['middle_name'] : null;
    $contact_number_value = !empty($pending['contact_number']) ? $pending['contact_number'] : null;
    $student_number_value = !empty($pending['student_number']) ? $pending['student_number'] : null;
    $program_value = !empty($pending['program']) ? $pending['program'] : null;
    $year_section_value = !empty($pending['year_section']) ? $pending['year_section'] : null;
    $profile_picture_value = !empty($pending['profile_picture']) ? $pending['profile_picture'] : null;
    
    error_log("Prepared values:");
    error_log("  middle_name_value: " . ($middle_name_value ?? 'NULL'));
    error_log("  contact_number_value: " . ($contact_number_value ?? 'NULL'));
    error_log("  student_number_value: " . ($student_number_value ?? 'NULL'));
    error_log("  program_value: " . ($program_value ?? 'NULL'));
    error_log("  year_section_value: " . ($year_section_value ?? 'NULL'));
    error_log("  profile_picture_value: " . ($profile_picture_value ?? 'NULL'));
    
    $params = [
        $pending['email'],
        $pending['password'],
        $pending['full_name'],
        $pending['first_name'],
        $middle_name_value,
        $pending['last_name'],
        $pending['role'],
        $contact_number_value,
        $student_number_value,
        $program_value,
        $year_section_value,
        $profile_picture_value
    ];
    
    error_log("Executing INSERT with parameters:");
    error_log(print_r($params, true));
    
    $result = $stmt->execute($params);
    
    if (!$result) {
        $errorInfo = $stmt->errorInfo();
        error_log("SQL Execute failed: " . print_r($errorInfo, true));
        throw new Exception('Failed to insert user data: ' . print_r($errorInfo, true));
    }
    
    $user_id = $conn->lastInsertId();
    error_log("User inserted successfully with ID: " . $user_id);
    
    if ($pending['role'] === 'teacher' && !empty($pending['code_id'])) {
        error_log("Updating teacher code: " . $pending['code_id']);
        
        $stmt = $conn->prepare("
    UPDATE teacher_codes 
    SET is_used = 1, use_count = use_count + 1
    WHERE code_id = ?
");
$updateResult = $stmt->execute([$pending['code_id']]);
        if (!$updateResult) {
            $errorInfo = $stmt->errorInfo();
            error_log("Teacher code update failed: " . print_r($errorInfo, true));
        } else {
            error_log("Teacher code updated successfully");
        }
    }
    
    if (function_exists('logAudit')) {
        logAudit(
            'User registered and verified email',
            'insert',
            'users',
            $user_id,
            'New ' . $pending['role'] . ' registration: ' . $pending['full_name'],
            $user_id
        );
    }
    
    $conn->commit();
    error_log("Transaction committed successfully");
    error_log("=== VERIFICATION DEBUG END ===");
    
    unset($_SESSION['pending_registration']);
    
    redirectWithMessage(
        BASE_URL . 'auth/login.php',
        'success',
        'Email verified successfully! You can now log in to your account.'
    );
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("=== DATABASE ERROR IN VERIFICATION ===");
    error_log("Error Message: " . $e->getMessage());
    error_log("Error Code: " . $e->getCode());
    error_log("SQL State: " . ($e->errorInfo[0] ?? 'N/A'));
    error_log("Driver Error Code: " . ($e->errorInfo[1] ?? 'N/A'));
    error_log("Driver Error Message: " . ($e->errorInfo[2] ?? 'N/A'));
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("Pending registration data: " . print_r($pending, true));
    error_log("=== END DATABASE ERROR ===");
    
    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
        redirectWithMessage(
            BASE_URL . 'auth/login.php',
            'danger',
            'This email is already registered. Please login instead.'
        );
    }
    
    redirectWithMessage(
        BASE_URL . 'auth/verify-email.php',
        'danger',
        'A database error occurred. Error: ' . $e->getMessage()
    );
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("=== GENERAL ERROR IN VERIFICATION ===");
    error_log("Error Message: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    error_log("=== END GENERAL ERROR ===");
    
    redirectWithMessage(
        BASE_URL . 'auth/verify-email.php',
        'danger',
        'An error occurred during verification: ' . $e->getMessage()
    );
}
?>