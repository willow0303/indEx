<?php
header('Content-Type: application/json');
require_once '../../includes/config.php';
require_once '../../includes/session.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$action_type = $_GET['action_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$user_role = $_GET['user_role'] ?? '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

try {
    // Build the query dynamically based on filters
    $query = "
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
        WHERE 1=1
    ";
    
    $params = [];
    
    // Search filter
    if (!empty($search)) {
        $query .= " AND (
            u.full_name LIKE :search 
            OR al.action LIKE :search 
            OR al.description LIKE :search
            OR u.email LIKE :search
        )";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Action type filter - UPDATED to include 'unenroll'
    if (!empty($action_type)) {
        $query .= " AND al.action_type = :action_type";
        $params[':action_type'] = $action_type;
    }
    
    // User role filter
    if (!empty($user_role)) {
        $query .= " AND u.role = :user_role";
        $params[':user_role'] = $user_role;
    }
    
    // Date range filter
    if (!empty($date_from)) {
        $query .= " AND DATE(al.created_at) >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if (!empty($date_to)) {
        $query .= " AND DATE(al.created_at) <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    // Get total count
    $countQuery = preg_replace('/SELECT .+ FROM/s', 'SELECT COUNT(*) as total FROM', $query);
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Add order and pagination
    $query .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        if ($key === ':limit' || $key === ':offset') {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }
    
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Clean and format logs like in get-overview.php
    $cleanedLogs = array_map(function($log) {
        $description = $log['description'];
        $decodedDesc = json_decode($description, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDesc)) {
            $relevantData = [];
            
            // Extract relevant fields
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
            if (isset($decodedDesc['student_name'])) {
                $relevantData['student_name'] = $decodedDesc['student_name'];
            }
            if (isset($decodedDesc['grade'])) {
                $relevantData['grade'] = $decodedDesc['grade'];
            }
            if (isset($decodedDesc['activity_name'])) {
                $relevantData['activity_name'] = $decodedDesc['activity_name'];
            }
            
            if (!empty($relevantData)) {
                $log['description'] = formatCleanDescription($log['action'], $log['action_type'], $relevantData);
            }
        }
        
        return $log;
    }, $logs);
    
    echo json_encode([
        'success' => true,
        'logs' => $cleanedLogs,
        'total' => $totalCount,
        'showing' => count($cleanedLogs),
        'page' => floor($offset / $limit) + 1,
        'per_page' => $limit
    ]);

} catch (PDOException $e) {
    error_log("Filter Audit Logs Error: " . $e->getMessage());
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