<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/session.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Get all audit logs with user information and clean data
    $stmt = $conn->prepare("
        SELECT 
            al.log_id,
            al.user_id,
            al.action,
            al.action_type,
            al.table_affected,
            al.record_id,
            al.description,
            al.created_at,
            u.full_name,
            u.email,
            u.role
        FROM audit_logs al
        INNER JOIN users u ON al.user_id = u.user_id
        ORDER BY al.created_at DESC
        LIMIT 1000
    ");
    
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Clean and format the logs
    $cleanedLogs = array_map(function($log) {
        // Parse description if it's JSON to extract only relevant info
        $description = $log['description'];
        
        // Try to decode JSON description
        $decodedDesc = json_decode($description, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDesc)) {
            // Extract only relevant fields for display
            $relevantData = [];
            
            // For class/course related actions
            if (isset($decodedDesc['class_name'])) {
                $relevantData['class_name'] = $decodedDesc['class_name'];
            }
            if (isset($decodedDesc['section'])) {
                $relevantData['section'] = $decodedDesc['section'];
            }
            if (isset($decodedDesc['subject'])) {
                $relevantData['subject'] = $decodedDesc['subject'];
            }
            if (isset($decodedDesc['teacher_name'])) {
                $relevantData['teacher_name'] = $decodedDesc['teacher_name'];
            }
            
            // For student/grade related actions
            if (isset($decodedDesc['student_name'])) {
                $relevantData['student_name'] = $decodedDesc['student_name'];
            }
            if (isset($decodedDesc['grade'])) {
                $relevantData['grade'] = $decodedDesc['grade'];
            }
            if (isset($decodedDesc['activity_name'])) {
                $relevantData['activity_name'] = $decodedDesc['activity_name'];
            }
            
            // Format the cleaned description based on action type
            if (!empty($relevantData)) {
                $log['description'] = formatCleanDescription($log['action'], $log['action_type'], $relevantData);
            }
        }
        
        return $log;
    }, $logs);
    
    echo json_encode([
        'success' => true,
        'logs' => $cleanedLogs,
        'total' => count($cleanedLogs)
    ]);

} catch (PDOException $e) {
    error_log("Audit Logs Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}

// Helper function to format clean descriptions
function formatCleanDescription($action, $action_type, $data) {
    $parts = [];
    
    // Add action-specific formatting
    if ($action_type === 'join') {
        $icon = '➕ Joined: ';
    } elseif ($action_type === 'unenroll') {
        $icon = '🚪 Unenrolled: ';
    } elseif ($action_type === 'create') {
        $icon = '✨ Created: ';
    } elseif ($action_type === 'update') {
        $icon = '📝 Updated: ';
    } elseif ($action_type === 'delete') {
        $icon = '🗑️ Deleted: ';
    } else {
        $icon = '';
    }
    
    if (isset($data['class_name'])) {
        $parts[] = $data['class_name'];
    }
    if (isset($data['section'])) {
        $parts[] = $data['section'];
    }
    if (isset($data['subject'])) {
        $parts[] = "({$data['subject']})";
    }
    if (isset($data['student_name'])) {
        $parts[] = "Student: {$data['student_name']}";
    }
    if (isset($data['grade'])) {
        $parts[] = "Grade: {$data['grade']}";
    }
    if (isset($data['activity_name'])) {
        $parts[] = "Activity: {$data['activity_name']}";
    }
    if (isset($data['teacher_name'])) {
        $parts[] = "Teacher: {$data['teacher_name']}";
    }
    
    $description = !empty($parts) ? implode(' - ', $parts) : $action;
    return $icon . $description;
}
?>