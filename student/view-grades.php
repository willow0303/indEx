<?php

require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireStudent();

$student_id = $_SESSION['user_id'];
$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

if (empty($class_id)) {
    header('Location: ' . BASE_URL . 'student/my-courses.php');
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT 
            c.*,
            u.full_name as teacher_name,
            e.enrollment_id
        FROM enrollments e
        INNER JOIN classes c ON e.class_id = c.class_id
        INNER JOIN users u ON c.teacher_id = u.user_id
        WHERE e.student_id = ? AND e.class_id = ? AND e.status = 'active'
    ");
    $stmt->execute([$student_id, $class_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        setFlashMessage('danger', 'Course not found or you are not enrolled.');
        header('Location: ' . BASE_URL . 'student/my-courses.php');
        exit();
    }
    
    $gradeData = [
        'attendance' => [
            'total' => 20,
            'present' => 18,
            'percentage' => 90
        ],
        'quizzes' => [
            ['name' => 'Quiz 1', 'score' => 45, 'total' => 50, 'date' => '2025-01-15'],
            ['name' => 'Quiz 2', 'score' => 48, 'total' => 50, 'date' => '2025-01-22'],
            ['name' => 'Quiz 3', 'score' => 42, 'total' => 50, 'date' => '2025-02-05']
        ],
        'activities' => [
            ['name' => 'Activity 1', 'score' => 25, 'total' => 25, 'date' => '2025-01-18'],
            ['name' => 'Activity 2', 'score' => 22, 'total' => 25, 'date' => '2025-01-25'],
            ['name' => 'Activity 3', 'score' => 24, 'total' => 25, 'date' => '2025-02-08']
        ],
        'assignments' => [
            ['name' => 'Assignment 1', 'score' => 95, 'total' => 100, 'date' => '2025-01-20'],
            ['name' => 'Assignment 2', 'score' => 88, 'total' => 100, 'date' => '2025-02-03']
        ],
        'recitations' => [
            ['name' => 'Recitation 1', 'score' => 9, 'total' => 10, 'date' => '2025-01-17'],
            ['name' => 'Recitation 2', 'score' => 10, 'total' => 10, 'date' => '2025-01-24'],
            ['name' => 'Recitation 3', 'score' => 8, 'total' => 10, 'date' => '2025-02-07']
        ],
        'exams' => [
            ['name' => 'Midterm Exam', 'score' => 85, 'total' => 100, 'date' => '2025-02-15'],
            ['name' => 'Final Exam', 'score' => null, 'total' => 100, 'date' => 'TBA']
        ]
    ];
    
    $overallGrade = 88.5; 
    
} catch (PDOException $e) {
    error_log("View Grades Error: " . $e->getMessage());
    setFlashMessage('error', 'An error occurred while loading grades.');
    header('Location: ' . BASE_URL . 'student/my-courses.php');
    exit();
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - <?php echo htmlspecialchars($course['class_name']); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>student-pages/student-main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>student-main.css?v=<?php echo time(); ?>"> 
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>student-pages/view-grades.css?v=<?php echo time(); ?>"> 
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>student-pages/index-card-modal.css?v=<?php echo time(); ?>"> 
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/student-nav.php'; ?>
        
        <div class="main-content">
            <div class="grades-header">
                <div class="header-left">
                    <a href="my-courses.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Courses
                    </a>
                    <div class="course-info">
                        <h1 class="course-title"><?php echo htmlspecialchars($course['subject']); ?> - <?php echo htmlspecialchars($course['class_name']); ?></h1>
                        <p class="course-meta">
                            <span><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                        </p>
                    </div>
                </div>
                <div class="overall-grade-card">
                    <div class="grade-label">Overall Grade</div>
                    <div class="grade-value"><?php echo number_format($overallGrade, 2); ?>%</div>
                    <div class="grade-rating">Excellent</div>
                    <button class="btn-view-index-card" onclick="openGradeModal(<?php echo $class_id; ?>)">
                        <i class="fas fa-id-card"></i> View Index Card
                    </button>
                </div>
            </div>

            <div class="grade-card">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fas fa-calendar-check"></i>
                        <h2>Attendance</h2>
                    </div>
                    <div class="attendance-summary">
                        <?php echo $gradeData['attendance']['present']; ?> / <?php echo $gradeData['attendance']['total']; ?> days
                    </div>
                </div>
                <div class="card-body">
                    <div class="attendance-display">
                        <div class="attendance-circle">
                            <svg viewBox="0 0 36 36" class="circular-chart">
                                <path class="circle-bg"
                                    d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                                <path class="circle"
                                    stroke-dasharray="<?php echo $gradeData['attendance']['percentage']; ?>, 100"
                                    d="M18 2.0845
                                    a 15.9155 15.9155 0 0 1 0 31.831
                                    a 15.9155 15.9155 0 0 1 0 -31.831"
                                />
                                <text x="18" y="20.35" class="percentage"><?php echo $gradeData['attendance']['percentage']; ?>%</text>
                            </svg>
                        </div>
                        <div class="attendance-stats">
                            <div class="stat-item present">
                                <i class="fas fa-check-circle"></i>
                                <span>Present: <?php echo $gradeData['attendance']['present']; ?></span>
                            </div>
                            <div class="stat-item absent">
                                <i class="fas fa-times-circle"></i>
                                <span>Absent: <?php echo $gradeData['attendance']['total'] - $gradeData['attendance']['present']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grade-card">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fas fa-clipboard-question"></i>
                        <h2>Quizzes</h2>
                    </div>
                    <div class="item-count"><?php echo count($gradeData['quizzes']); ?> items</div>
                </div>
                <div class="card-body">
                    <div class="spreadsheet-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="col-name">Quiz Name</th>
                                    <th class="col-score">Score</th>
                                    <th class="col-total">Total</th>
                                    <th class="col-percentage">%</th>
                                    <th class="col-date">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gradeData['quizzes'] as $quiz): ?>
                                    <tr>
                                        <td class="col-name"><?php echo htmlspecialchars($quiz['name']); ?></td>
                                        <td class="col-score"><?php echo $quiz['score']; ?></td>
                                        <td class="col-total"><?php echo $quiz['total']; ?></td>
                                        <td class="col-percentage">
                                            <span class="percentage-badge">
                                                <?php echo number_format(($quiz['score'] / $quiz['total']) * 100, 1); ?>%
                                            </span>
                                        </td>
                                        <td class="col-date"><?php echo date('M d, Y', strtotime($quiz['date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grade-card">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fas fa-tasks"></i>
                        <h2>Activities</h2>
                    </div>
                    <div class="item-count"><?php echo count($gradeData['activities']); ?> items</div>
                </div>
                <div class="card-body">
                    <div class="spreadsheet-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="col-name">Activity Name</th>
                                    <th class="col-score">Score</th>
                                    <th class="col-total">Total</th>
                                    <th class="col-percentage">%</th>
                                    <th class="col-date">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gradeData['activities'] as $activity): ?>
                                    <tr>
                                        <td class="col-name"><?php echo htmlspecialchars($activity['name']); ?></td>
                                        <td class="col-score"><?php echo $activity['score']; ?></td>
                                        <td class="col-total"><?php echo $activity['total']; ?></td>
                                        <td class="col-percentage">
                                            <span class="percentage-badge">
                                                <?php echo number_format(($activity['score'] / $activity['total']) * 100, 1); ?>%
                                            </span>
                                        </td>
                                        <td class="col-date"><?php echo date('M d, Y', strtotime($activity['date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grade-card">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fas fa-file-alt"></i>
                        <h2>Assignments</h2>
                    </div>
                    <div class="item-count"><?php echo count($gradeData['assignments']); ?> items</div>
                </div>
                <div class="card-body">
                    <div class="spreadsheet-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="col-name">Assignment Name</th>
                                    <th class="col-score">Score</th>
                                    <th class="col-total">Total</th>
                                    <th class="col-percentage">%</th>
                                    <th class="col-date">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gradeData['assignments'] as $assignment): ?>
                                    <tr>
                                        <td class="col-name"><?php echo htmlspecialchars($assignment['name']); ?></td>
                                        <td class="col-score"><?php echo $assignment['score']; ?></td>
                                        <td class="col-total"><?php echo $assignment['total']; ?></td>
                                        <td class="col-percentage">
                                            <span class="percentage-badge">
                                                <?php echo number_format(($assignment['score'] / $assignment['total']) * 100, 1); ?>%
                                            </span>
                                        </td>
                                        <td class="col-date"><?php echo date('M d, Y', strtotime($assignment['date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grade-card">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fas fa-comments"></i>
                        <h2>Recitations</h2>
                    </div>
                    <div class="item-count"><?php echo count($gradeData['recitations']); ?> items</div>
                </div>
                <div class="card-body">
                    <div class="spreadsheet-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="col-name">Recitation</th>
                                    <th class="col-score">Score</th>
                                    <th class="col-total">Total</th>
                                    <th class="col-percentage">%</th>
                                    <th class="col-date">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gradeData['recitations'] as $recitation): ?>
                                    <tr>
                                        <td class="col-name"><?php echo htmlspecialchars($recitation['name']); ?></td>
                                        <td class="col-score"><?php echo $recitation['score']; ?></td>
                                        <td class="col-total"><?php echo $recitation['total']; ?></td>
                                        <td class="col-percentage">
                                            <span class="percentage-badge">
                                                <?php echo number_format(($recitation['score'] / $recitation['total']) * 100, 1); ?>%
                                            </span>
                                        </td>
                                        <td class="col-date"><?php echo date('M d, Y', strtotime($recitation['date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grade-card">
                <div class="card-header">
                    <div class="header-title">
                        <i class="fas fa-file-signature"></i>
                        <h2>Exams</h2>
                    </div>
                    <div class="item-count"><?php echo count($gradeData['exams']); ?> items</div>
                </div>
                <div class="card-body">
                    <div class="spreadsheet-table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="col-name">Exam Name</th>
                                    <th class="col-score">Score</th>
                                    <th class="col-total">Total</th>
                                    <th class="col-percentage">%</th>
                                    <th class="col-date">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gradeData['exams'] as $exam): ?>
                                    <tr>
                                        <td class="col-name"><?php echo htmlspecialchars($exam['name']); ?></td>
                                        <td class="col-score"><?php echo $exam['score'] ?? '-'; ?></td>
                                        <td class="col-total"><?php echo $exam['total']; ?></td>
                                        <td class="col-percentage">
                                            <?php if ($exam['score']): ?>
                                                <span class="percentage-badge">
                                                    <?php echo number_format(($exam['score'] / $exam['total']) * 100, 1); ?>%
                                                </span>
                                            <?php else: ?>
                                                <span class="pending-badge">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="col-date"><?php echo $exam['date']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grade-modal-overlay" id="gradeModalOverlay">
        <div class="grade-modal-container">
            <button class="grade-modal-close" onclick="closeGradeModal()">&times;</button>
            
            <div class="flip-container" id="cardFlipContainer">
                <div class="flipper">
                    <div class="front">
                        <div class="card-content">
                            <div class="header-section">
                                <table class="info-table">
                                    <tbody>
                                        <tr><th style="border-top: 1px solid var(--gray-dark);">NAME:</th><td></td></tr>
                                        <tr><th>COURSE, YEAR & SECTION:</th><td></td></tr>
                                        <tr><th>COURSE TITLE:</th><td></td></tr>
                                        <tr><th>COURSE CODE:</th><td></td></tr>
                                    </tbody>
                                </table>
                                <table style="width: 150px;">
                                    <tr>
                                        <td class="photo-cell photo-row-height" style="height: 100%;">
                                            <div class="image-container">
                                                <img src="" alt="1x1 Photo Placeholder" title="Insert 1x1 Photo Here">
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <table>
                                <tbody>
                                    <tr class="grade-row">
                                        <th colspan="2" style="background-color: var(--gray-light);">Midterm Grade:</th>
                                        <th colspan="2" class="interactive-header" onclick="flipCardToFinal()">Final Term Grade: (Click to flip)</th> 
                                    </tr>
                                </tbody>
                            </table>

                            <table>
                                <thead>
                                    <tr class="main-sections">
                                        <th rowspan="2" class="term-group">MIDTERMS</th>
                                        <th colspan="2">QUIZZES</th>
                                        <th colspan="2">ACTIVITIES</th>
                                        <th rowspan="2" class="special-header">PROJECT</th>
                                        <th rowspan="2" class="special-header">MIDTERM EXAM</th>
                                    </tr>
                                    <tr class="main-sections">
                                        <th class="score-label">Label</th><th class="score-input">Score</th>
                                        <th class="score-label">Label</th><th class="score-input">Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td rowspan="10" class="term-group"></td><td class="score-label">MQ1</td><td class="score-input"></td><td class="score-label">MACT1</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">MQ2</td><td class="score-input"></td><td class="score-label">MACT2</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">MQ3</td><td class="score-input"></td><td class="score-label">MACT3</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">MQ4</td><td class="score-input"></td><td class="score-label">MACT4</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">MQ5</td><td class="score-input"></td><td class="score-label">MACT5</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">MQ6</td><td class="score-input"></td><td class="score-label">MACT6</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">MQ7</td><td class="score-input"></td><td class="score-label">MACT7</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">MQ8</td><td class="score-input"></td><td class="score-label">MACT8</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">MQ9</td><td class="score-input"></td><td class="score-label">MACT9</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">MQ10</td><td class="score-input"></td><td class="score-label">MACT10</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>

                                    <tr class="total-row">
                                        <td class="combined-total-label">TOTAL / AVERAGE</td> 
                                        <td colspan="1" class="total-label">Quiz Total:</td><td colspan="1" class="total-input"></td> 
                                        <td colspan="1" class="total-label">Activity Total:</td><td colspan="1" class="total-input"></td> 
                                        <td class="special-score total-input"></td>
                                        <td class="special-score total-input"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="back">
                        <div class="card-content">
                            <div class="header-section">
                                <table class="info-table">
                                    <tbody>
                                        <tr><th style="border-top: 1px solid var(--gray-dark);">NAME:</th><td></td></tr>
                                        <tr><th>COURSE, YEAR & SECTION:</th><td></td></tr>
                                        <tr><th>COURSE TITLE:</th><td></td></tr>
                                        <tr><th>COURSE CODE:</th><td></td></tr>
                                    </tbody>
                                </table>
                                <table style="width: 150px;">
                                    <tr>
                                        <td class="photo-cell photo-row-height" style="height: 100%;">
                                            <div class="image-container">
                                                <img src="" alt="1x1 Photo Placeholder" title="Insert 1x1 Photo Here">
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <table>
                                <tbody>
                                    <tr class="grade-row">
                                        <th colspan="2" class="interactive-header" onclick="flipCardBack()">Midterm Grade: (Click to flip back)</th> 
                                        <th colspan="2" style="background-color: var(--gray-light);">Final Term Grade:</th>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <table>
                                <thead>
                                    <tr class="main-sections">
                                        <th rowspan="2" class="term-group">FINALS</th> 
                                        <th colspan="2">QUIZZES</th>
                                        <th colspan="2">ACTIVITIES</th>
                                        <th rowspan="2" class="special-header">PROJECT</th>
                                        <th rowspan="2" class="special-header">FINAL EXAM</th> 
                                    </tr>
                                    <tr class="main-sections">
                                        <th class="score-label">Label</th><th class="score-input">Score</th>
                                        <th class="score-label">Label</th><th class="score-input">Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td rowspan="10" class="term-group"></td><td class="score-label">FQ1</td><td class="score-input"></td><td class="score-label">FACT1</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">FQ2</td><td class="score-input"></td><td class="score-label">FACT2</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">FQ3</td><td class="score-input"></td><td class="score-label">FACT3</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">FQ4</td><td class="score-input"></td><td class="score-label">FACT4</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">FQ5</td><td class="score-input"></td><td class="score-label">FACT5</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">FQ6</td><td class="score-input"></td><td class="score-label">FACT6</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">FQ7</td><td class="score-input"></td><td class="score-label">FACT7</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">FQ8</td><td class="score-input"></td><td class="score-label">FACT8</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">FQ9</td><td class="score-input"></td><td class="score-label">FACT9</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>
                                    <tr><td class="score-label">FQ10</td><td class="score-input"></td><td class="score-label">FACT10</td><td class="score-input"></td><td class="special-score"></td><td class="special-score"></td></tr>

                                    <tr class="total-row">
                                        <td class="combined-total-label">TOTAL / AVERAGE</td> 
                                        <td colspan="1" class="total-label">Quiz Total:</td><td colspan="1" class="total-input"></td> 
                                        <td colspan="1" class="total-label">Activity Total:</td><td colspan="1" class="total-input"></td> 
                                        <td class="special-score total-input"></td>
                                        <td class="special-score total-input"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
    <?php include '../includes/footer.php'; ?>
<script src="/online-grading-system/assets/js/student-pages/student-index-card-modal.js"></script>
   
</body>
</html>
