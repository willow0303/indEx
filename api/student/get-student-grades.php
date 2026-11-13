<?php
require_once '../../includes/config.php';
require_once '../../includes/session.php';

// Set timeout and memory limits
set_time_limit(5);
ini_set('memory_limit', '128M');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['user_id'];
$class_id = $_POST['class_id'] ?? $_GET['class_id'] ?? null;

if (!$class_id) {
    echo json_encode(['success' => false, 'message' => 'Missing class ID']);
    exit;
}

try {
    // Check enrollment
    $enrollmentCheck = $conn->prepare("
        SELECT enrollment_id FROM enrollments 
        WHERE student_id = ? AND class_id = ? AND status = 'active'
    ");
    $enrollmentCheck->execute([$student_id, $class_id]);
    
    if (!$enrollmentCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Not enrolled in this class']);
        exit;
    }
    
    // Get student info with profile picture
    $studentInfo = $conn->prepare("
        SELECT 
            u.full_name,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.program,
            u.year_section,
            u.profile_picture,
            c.class_name,
            c.subject
        FROM users u
        CROSS JOIN classes c
        WHERE u.user_id = ? AND c.class_id = ?
    ");
    $studentInfo->execute([$student_id, $class_id]);
    $student = $studentInfo->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        echo json_encode(['success' => false, 'message' => 'Student or class not found']);
        exit;
    }
    
    // Format full name properly: Last Name, First Name M.I.
    $first_name = $student['first_name'] ?? '';
    $middle_name = $student['middle_name'] ?? '';
    $last_name = $student['last_name'] ?? '';
    
    // Create middle initial if middle name exists
    $middle_initial = '';
    if (!empty($middle_name)) {
        $middle_initial = strtoupper(substr(trim($middle_name), 0, 1)) . '.';
    }
    
    // Format: Last Name, First Name M.I.
    $formatted_name = trim($last_name);
    if (!empty($first_name)) {
        $formatted_name .= ', ' . trim($first_name);
        if ($middle_initial) {
            $formatted_name .= ' ' . $middle_initial;
        }
    }
    
    // If somehow the separate columns are empty, fall back to full_name
    if (empty($formatted_name) || $formatted_name === ',') {
        $formatted_name = $student['full_name'] ?? 'Unknown';
    }
    
    $student['full_name'] = $formatted_name;
    
    // FIXED: Comprehensive profile picture path handling
    error_log("=== Profile Picture Debug ===");
    error_log("Raw profile_picture from DB: " . ($student['profile_picture'] ?? 'NULL'));
    
    if (!empty($student['profile_picture'])) {
        $profilePic = $student['profile_picture'];
        
        // Remove any existing '../' prefix
        $profilePic = str_replace('../', '', $profilePic);
        
        // Remove leading slashes
        $profilePic = ltrim($profilePic, '/');
        
        error_log("After cleanup: " . $profilePic);
        
        // Check if path already contains 'uploads/'
        if (strpos($profilePic, 'uploads/') === 0) {
            // Path already has uploads/ prefix
            $finalPath = '../' . $profilePic;
        } else {
            // Need to add uploads path
            // Check if it's just a filename
            if (basename($profilePic) === $profilePic) {
                // Just a filename, add full path
                // Try uploads/profiles/ first (newer path)
                $finalPath = '../uploads/profiles/' . $profilePic;
                
                // Check if file exists, if not try uploads/profile_pictures/
                $fullServerPath = __DIR__ . '/../../uploads/profiles/' . $profilePic;
                if (!file_exists($fullServerPath)) {
                    error_log("File not found at: " . $fullServerPath);
                    $finalPath = '../uploads/profile_pictures/' . $profilePic;
                    $fullServerPath = __DIR__ . '/../../uploads/profile_pictures/' . $profilePic;
                    if (!file_exists($fullServerPath)) {
                        error_log("File also not found at: " . $fullServerPath);
                    } else {
                        error_log("File found at: " . $fullServerPath);
                    }
                } else {
                    error_log("File found at: " . $fullServerPath);
                }
            } else {
                // Has some path structure, add ../ prefix
                $finalPath = '../' . $profilePic;
            }
        }
        
        $student['profile_picture'] = $finalPath;
        error_log("Final profile_picture path: " . $finalPath);
        
    } else {
        // No profile picture, use default
        $student['profile_picture'] = '../uploads/profile_pictures/default.jpg';
        error_log("Using default profile picture");
    }
    
    // Get all grades at once
    $allGrades = $conn->prepare("
        SELECT activity_name, activity_type, score, max_score, grading_period
        FROM grades
        WHERE student_id = ? AND class_id = ?
        ORDER BY grading_period, activity_name
    ");
    $allGrades->execute([$student_id, $class_id]);
    $gradesData = $allGrades->fetchAll(PDO::FETCH_ASSOC);
    
    // Separate by period
    $midtermData = [];
    $finalsData = [];
    
    foreach ($gradesData as $grade) {
        if ($grade['grading_period'] === 'midterm') {
            $midtermData[] = $grade;
        } else if ($grade['grading_period'] === 'finals') {
            $finalsData[] = $grade;
        }
    }
    
    function organizeGrades($gradesData) {
        $organized = [
            'quizzes' => [],
            'activities' => [],
            'project' => ['name' => '', 'score' => 0, 'max_score' => 0],
            'exam' => ['name' => '', 'score' => 0, 'max_score' => 0]
        ];
        
        foreach ($gradesData as $grade) {
            $name = trim($grade['activity_name']);
            $type = strtolower(trim($grade['activity_type']));
            $nameLower = strtolower($name);
            
            $gradeItem = [
                'name' => $name,
                'score' => (float)$grade['score'],
                'max_score' => (float)$grade['max_score']
            ];
            
            // Skip if no max score
            if ($gradeItem['max_score'] <= 0) {
                continue;
            }
            
            // SKIP RECITATION - Don't display it anywhere
            $isRecitation = ($type === 'recitation' || stripos($nameLower, 'recitation') !== false || stripos($nameLower, 'recit') !== false);
            if ($isRecitation) {
                continue; // Skip this grade entirely
            }
            
            // Categorize by type and name
            $isExam = ($type === 'exam' || stripos($nameLower, 'exam') !== false);
            $isProject = ($type === 'project' || stripos($nameLower, 'project') !== false);
            $isQuiz = ($type === 'quiz' || stripos($nameLower, 'quiz') !== false);
            
            if ($isExam && $organized['exam']['max_score'] == 0) {
                $organized['exam'] = $gradeItem;
            } elseif ($isProject && $organized['project']['max_score'] == 0) {
                $organized['project'] = $gradeItem;
            } elseif ($isQuiz && count($organized['quizzes']) < 10) {
                $organized['quizzes'][] = $gradeItem;
            } elseif (count($organized['activities']) < 10) {
                // Everything else goes to activities (except recitation which was already filtered)
                $organized['activities'][] = $gradeItem;
            }
        }
        
        // Pad to 10 items
        for ($i = count($organized['quizzes']); $i < 10; $i++) {
            $organized['quizzes'][] = ['name' => '', 'score' => 0, 'max_score' => 0];
        }
        for ($i = count($organized['activities']); $i < 10; $i++) {
            $organized['activities'][] = ['name' => '', 'score' => 0, 'max_score' => 0];
        }
        
        return $organized;
    }
    
    function calculateAverage($grades) {
        $totalScore = 0;
        $totalMax = 0;
        
        foreach ($grades['quizzes'] as $quiz) {
            if ($quiz['max_score'] > 0) {
                $totalScore += $quiz['score'];
                $totalMax += $quiz['max_score'];
            }
        }
        
        foreach ($grades['activities'] as $activity) {
            if ($activity['max_score'] > 0) {
                $totalScore += $activity['score'];
                $totalMax += $activity['max_score'];
            }
        }
        
        if ($grades['project']['max_score'] > 0) {
            $totalScore += $grades['project']['score'];
            $totalMax += $grades['project']['max_score'];
        }
        
        if ($grades['exam']['max_score'] > 0) {
            $totalScore += $grades['exam']['score'];
            $totalMax += $grades['exam']['max_score'];
        }
        
        return $totalMax > 0 ? number_format(($totalScore / $totalMax) * 100, 2) : '0.00';
    }
    
    $midtermOrganized = organizeGrades($midtermData);
    $finalsOrganized = organizeGrades($finalsData);
    
    echo json_encode([
        'success' => true,
        'student' => $student,
        'midterm' => [
            'grades' => $midtermOrganized,
            'average' => calculateAverage($midtermOrganized)
        ],
        'finals' => [
            'grades' => $finalsOrganized,
            'average' => calculateAverage($finalsOrganized)
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Get student grades error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    error_log("Get student grades error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>