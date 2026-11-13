<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireTeacher();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$teacher_id = $_SESSION['user_id'];
$class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

if (!$class_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid class ID']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT class_id FROM classes WHERE class_id = ? AND teacher_id = ? AND status = 'active'");
    $stmt->execute([$class_id, $teacher_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Class not found or already archived']);
        exit();
    }
    
    $updateStmt = $conn->prepare("UPDATE classes SET status = 'archived', updated_at = NOW() WHERE class_id = ? AND teacher_id = ?");
    $updateStmt->execute([$class_id, $teacher_id]);
    
    echo json_encode(['success' => true, 'message' => 'Section archived successfully']);
    
} catch (PDOException $e) {
    error_log("Archive Section Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>