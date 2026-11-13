<?php
// Start with clean output buffer
ob_start();

require_once '../../includes/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

// Clear any output that might have been generated
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Disable error display and log to file instead
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // Check if user is logged in and is a teacher
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
        throw new Exception('Unauthorized access');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $teacher_id = $_SESSION['user_id'];
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

    if (!$class_id) {
        throw new Exception('Class ID is required');
    }

    // Verify class ownership and archived status
    $stmt = $conn->prepare("
        SELECT class_id, class_name, section, subject, status
        FROM classes 
        WHERE class_id = ? AND teacher_id = ?
    ");
    $stmt->execute([$class_id, $teacher_id]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$class) {
        throw new Exception('Class not found or unauthorized');
    }

    if ($class['status'] !== 'archived') {
        throw new Exception('Only archived classes can be permanently deleted');
    }

    // Start transaction
    $conn->beginTransaction();

    // Delete in order (children first, then parent)
    
    // 1. Delete attendance
    $stmt = $conn->prepare("DELETE FROM attendance WHERE class_id = ?");
    $stmt->execute([$class_id]);
    
    // 2. Delete grades
    $stmt = $conn->prepare("DELETE FROM grades WHERE class_id = ?");
    $stmt->execute([$class_id]);
    
    // 3. Delete schedules
    $stmt = $conn->prepare("DELETE FROM class_schedules WHERE class_id = ?");
    $stmt->execute([$class_id]);
    
    // 4. Delete enrollments
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE class_id = ?");
    $stmt->execute([$class_id]);
    
    // 5. Delete class
    $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ?");
    $stmt->execute([$class_id]);
    
    // Log the deletion
    try {
        logAudit(
            'Permanently deleted class section',
            'DELETE',
            'classes',
            $class_id,
            "Deleted: {$class['section']} - {$class['subject']} {$class['class_name']}",
            $teacher_id
        );
    } catch (Exception $e) {
        // Continue even if logging fails
        error_log("Audit log failed: " . $e->getMessage());
    }
    
    // Commit transaction
    $conn->commit();
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Section permanently deleted successfully'
    ]);

} catch (PDOException $e) {
    // Rollback on database error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Delete Error (PDO): " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    
} catch (Exception $e) {
    // Rollback on general error
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Delete Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// End output buffering and flush
ob_end_flush();
?>