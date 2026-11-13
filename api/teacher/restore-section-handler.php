<?php
require_once '../../includes/config.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$teacher_id = $_SESSION['user_id'];
$class_id = $_POST['class_id'] ?? null;

if (!$class_id) {
    echo json_encode(['success' => false, 'message' => 'Class ID is required']);
    exit;
}

try {
    // Verify that the class belongs to this teacher and is archived
    $stmt = $conn->prepare("
        SELECT class_id, class_name, section, subject 
        FROM classes 
        WHERE class_id = ? AND teacher_id = ? AND status = 'archived'
    ");
    $stmt->execute([$class_id, $teacher_id]);
    $class = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$class) {
        echo json_encode(['success' => false, 'message' => 'Class not found or not archived']);
        exit;
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    // Restore class status to active
    $stmt = $conn->prepare("
        UPDATE classes 
        SET status = 'active', 
            updated_at = CURRENT_TIMESTAMP 
        WHERE class_id = ?
    ");
    $stmt->execute([$class_id]);
    
    // Log the restore action
    logAudit(
        'Restored class section',
        'UPDATE',
        'classes',
        $class_id,
        "Restored section {$class['section']} - {$class['subject']} {$class['class_name']}",
        $teacher_id
    );
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Section restored successfully'
    ]);
    
} catch (PDOException $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Restore Section Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred while restoring section'
    ]);
}
?>