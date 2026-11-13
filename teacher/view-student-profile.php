<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireTeacher();

$student_id = $_GET['student_id'] ?? null;
$class_id = $_GET['class_id'] ?? null;

if (!$student_id || !$class_id) {
    die('Invalid parameters');
}

$teacher_id = $_SESSION['user_id'];
$verifyStmt = $conn->prepare("SELECT class_id FROM classes WHERE class_id = :class_id AND teacher_id = :teacher_id");
$verifyStmt->execute([':class_id' => $class_id, ':teacher_id' => $teacher_id]);
if (!$verifyStmt->fetch()) {
    die('Unauthorized access');
}

$studentStmt = $conn->prepare("
    SELECT 
        u.user_id,
        u.full_name,
        u.first_name,
        u.middle_name,
        u.last_name,
        u.email,
        u.program,
        u.year_section,
        u.profile_picture,
        e.enrolled_at,
        c.class_name,
        c.subject,
        c.section
    FROM users u
    JOIN enrollments e ON u.user_id = e.student_id
    JOIN classes c ON e.class_id = c.class_id
    WHERE u.user_id = :student_id 
    AND e.class_id = :class_id 
    AND e.status = 'active'
");
$studentStmt->execute([':student_id' => $student_id, ':class_id' => $class_id]);
$student = $studentStmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die('Student not found');
}

$nameParts = explode(' ', trim($student['full_name']));
if (count($nameParts) >= 2) {
    $lastname = trim($student['last_name'] ?? array_pop($nameParts));
    $firstname = trim($student['first_name'] ?? implode(' ', $nameParts));
    $middleInitial = '';
    
    if (!empty($student['middle_name'])) {
        $middleInitial = ' ' . strtoupper(substr(trim($student['middle_name']), 0, 1)) . '.';
    }
    
    $formattedName = strtoupper($lastname) . ', ' . $firstname . $middleInitial;
} else {
    $formattedName = strtoupper($student['full_name']);
}

$gradesStmt = $conn->prepare("
    SELECT 
        activity_name,
        activity_type,
        score,
        max_score,
        grading_period
    FROM grades
    WHERE student_id = :student_id AND class_id = :class_id
    ORDER BY grading_period, activity_name
");
$gradesStmt->execute([':student_id' => $student_id, ':class_id' => $class_id]);
$allGrades = $gradesStmt->fetchAll(PDO::FETCH_ASSOC);

$midtermGrades = array_filter($allGrades, fn($g) => $g['grading_period'] === 'midterm');
$finalsGrades = array_filter($allGrades, fn($g) => $g['grading_period'] === 'finals');

function organizeGrades($grades) {
    $organized = [
        'quizzes' => [],
        'activities' => [],
        'project' => null,
        'exam' => null
    ];
    
    $quizzes = [];
    $activities = [];
    
    foreach ($grades as $grade) {
        $type = strtolower(trim($grade['activity_type']));
        $name = trim($grade['activity_name']);
        
        if ($type === 'quiz') {
            preg_match('/(\d+)/', $name, $matches);
            if (isset($matches[1])) {
                $number = intval($matches[1]) - 1;
                if ($number >= 0 && $number < 10) {
                    $quizzes[$number] = $grade;
                }
            }
        } elseif ($type === 'assignment' || $type === 'activity' || $type === '' || empty($type)) {
            if (stripos($name, 'Activity') !== false) {
                preg_match('/(\d+)/', $name, $matches);
                if (isset($matches[1])) {
                    $number = intval($matches[1]) - 1;
                    if ($number >= 0 && $number < 10) {
                        $activities[$number] = $grade;
                    }
                }
            }
        } elseif ($type === 'project') {
            if ($organized['project'] === null) {
                $organized['project'] = $grade;
            }
        } elseif ($type === 'exam') {
            if ($organized['exam'] === null) {
                $organized['exam'] = $grade;
            }
        }
    }
    
    for ($i = 0; $i < 10; $i++) {
        $organized['quizzes'][$i] = $quizzes[$i] ?? null;
        $organized['activities'][$i] = $activities[$i] ?? null;
    }
    
    return $organized;
}

$midtermOrganized = organizeGrades($midtermGrades);
$finalsOrganized = organizeGrades($finalsGrades);

function calculateAverage($grades) {
    if (empty($grades)) return '0';
    $totalPercentage = 0;
    $count = 0;
    
    foreach ($grades as $grade) {
        $maxScore = $grade['max_score'] > 0 ? $grade['max_score'] : 1;
        $percentage = ($grade['score'] / $maxScore) * 100;
        $totalPercentage += $percentage;
        $count++;
    }
    
    $avg = $count > 0 ? $totalPercentage / $count : 0;
    return $avg == floor($avg) ? number_format($avg, 0) : number_format($avg, 2);
}

function formatScore($score, $maxScore) {
    $score = $score == floor($score) ? number_format($score, 0) : number_format($score, 2);
    $maxScore = $maxScore == floor($maxScore) ? number_format($maxScore, 0) : number_format($maxScore, 2);
    return $score . '/' . $maxScore;
}

$midtermAvg = calculateAverage($midtermGrades);
$finalsAvg = calculateAverage($finalsGrades);

$defaultAvatar = '../assets/images/default-avatar.jpg';

if (!empty($student['profile_picture']) && trim($student['profile_picture']) !== '') {
    $profilePicture = BASE_URL . 'uploads/profiles/' . $student['profile_picture'];
} else {
    $profilePicture = $defaultAvatar;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($formattedName); ?> - Grade Card</title>
    <style>
        :root {
            --cream: #FFF8E7;
            --gray-dark: #333;
            --gray-medium: #666;
            --gray-light: #E8E8E8;
            --maroon-light: #8B0000;
            --maroon-dark: #660000;
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: transparent;
            overflow: hidden;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
            overflow-y: auto;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-close {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background-color: var(--maroon-light);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            z-index: 10002;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background-color: var(--maroon-dark);
            transform: rotate(90deg);
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            text-align: left; 
            font-size: 0.85em; 
        }

        th, td { 
            padding: 5px; 
            border: 1px solid var(--gray-dark);
            height: 15px; 
            vertical-align: middle; 
        }

        .flip-container {
            width: 95%; 
            max-width: 900px;
            height: auto;
            min-height: 550px;
            margin: auto;
            perspective: 2000px; 
            position: relative;
            padding: 60px 0;
        }

        .flipper {
            transition: transform 0.8s;
            transform-style: preserve-3d;
            position: relative;
            width: 100%;
            min-height: 100%;
        }

        .flip-container.flipped .flipper {
            transform: rotateX(180deg);
        }

        .front, .back {
            width: 100%;
            min-height: 100%;
            backface-visibility: hidden;
            background-color: var(--cream); 
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2); 
            border: 2px solid var(--gray-dark); 
            border-radius: 5px; 
            overflow: visible;
        }

        .back {
            transform: rotateX(180deg);
            position: absolute;
            top: 0;
            left: 0;
        }

        .front {
            position: relative;
        }

        .card-content {
            width: 100%;
            padding-bottom: 20px;
        }

        .header-section { 
            display: flex; 
            width: 100%; 
            border-bottom: 2px solid var(--gray-dark); 
        }

        .info-table { 
            flex-grow: 1; 
            border: none; 
        }

        .info-table th { 
            width: 30%; 
            font-weight: bold; 
            background-color: var(--cream); 
            border-top: none; 
            border-left: none; 
            border-right: 1px solid var(--gray-dark); 
        }

        .info-table td { 
            background-color: var(--cream); 
        }

        .photo-cell { 
            width: 150px; 
            text-align: center; 
            border-left: 2px solid var(--gray-dark); 
            border-top: none; 
            padding: 10px;
            background-color: var(--cream); 
        }

        .image-container {
            width: 100%; 
            height: 100%;
            display: flex; 
            align-items: center; 
            justify-content: center;
            border: 1px solid var(--gray-medium);
            background-color: var(--gray-light); 
            overflow: hidden;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-row-height { 
            height: 120px; 
        }

        .grade-row th, .main-sections th, .term-group { 
            text-align: center; 
            font-weight: bold; 
            background-color: var(--gray-light); 
            padding: 10px 5px; 
        }

        .term-group { 
            width: 10%; 
        }

        .special-header { 
            width: 15%; 
            text-align: center; 
        }

        .score-label, .score-input, .special-score { 
            background-color: var(--cream); 
        }

        .score-label { 
            width: 5%; 
            text-align: center; 
            font-weight: normal; 
            background-color: var(--cream); 
        }

        .score-input { 
            width: 5%; 
            text-align: center; 
            font-size: 0.8em; 
            background-color: var(--cream); 
        }

        .special-score { 
            width: 15%; 
            text-align: center; 
            background-color: var(--cream); 
        }

        .total-row td { 
            font-weight: bold; 
            background-color: var(--gray-light); 
        }

        .total-row .total-label { 
            text-align: right; 
            padding-right: 10px; 
            background-color: var(--gray-light); 
        }

        .total-row .total-input { 
            background-color: var(--cream);
            font-size: 0.85em; 
        }

        .combined-total-label { 
            text-align: center; 
        }

        .interactive-header {
            cursor: pointer; 
            user-select: none;
            background-color: var(--maroon-light);
            color: var(--white);
            transition: background-color 0.3s;
            position: relative;
        }

        .interactive-header:hover {
            background-color: var(--maroon-dark);
        }

        .interactive-header::after {
            content: '▼';
            position: absolute;
            right: 10px;
            font-size: 12px;
        }

        .flip-container.flipped .front .interactive-header::after {
            content: '▲';
        }

        @media (max-width: 768px) {
            .flip-container {
                width: 100%;
                font-size: 0.9em;
                padding: 80px 10px 20px 10px;
            }

            .photo-cell {
                width: 100px;
            }

            .modal-close {
                top: 10px;
                right: 10px;
            }
            
            .modal-overlay {
                padding: 10px;
            }
        }

        @media (max-height: 700px) {
            .flip-container {
                min-height: auto;
            }
        }
    </style>
</head>
<body>
    <div class="modal-overlay">
        <button class="modal-close" onclick="closeModal()">×</button>
        
        <div class="flip-container" id="cardFlipContainer">
            <div class="flipper">
                <div class="front">
                    <div class="card-content">
                        <div class="header-section">
                            <table class="info-table">
                                <tbody>
                                    <tr><th style="border-top: 1px solid var(--gray-dark);">NAME:</th><td><?php echo htmlspecialchars($formattedName); ?></td></tr>
                                    <tr><th>COURSE, YEAR & SECTION:</th><td><?php echo htmlspecialchars($student['year_section'] ?? 'N/A'); ?></td></tr>
                                    <tr><th>COURSE TITLE:</th><td><?php echo htmlspecialchars($student['class_name']); ?></td></tr>
                                    <tr><th>COURSE CODE:</th><td><?php echo htmlspecialchars($student['subject']); ?></td></tr>
                                </tbody>
                            </table>
                            <table style="width: 150px;">
                                <tr>
                                    <td class="photo-cell photo-row-height" style="height: 100%;">
                                        <div class="image-container">
                                            <img src="<?php echo htmlspecialchars($profilePicture); ?>" 
                                            alt="Student Photo"
                                            onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($defaultAvatar); ?>';">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <table>
                            <tbody>
                                <tr class="grade-row">
                                    <th colspan="2" style="background-color: var(--gray-light);">Midterm Grade: <?php echo $midtermAvg; ?>%</th>
                                    <th colspan="2" class="interactive-header" onclick="flipToFinals()">Final Term Grade: (Click to flip)</th> 
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
                                <?php for ($i = 0; $i < 10; $i++): ?>
                                <tr>
                                    <?php if ($i === 0): ?>
                                        <td rowspan="10" class="term-group"></td>
                                    <?php endif; ?>
                                    
                                    <td class="score-label">MQ<?php echo $i + 1; ?></td>
                                    <td class="score-input">
                                        <?php 
                                        echo isset($midtermOrganized['quizzes'][$i]) 
                                            ? formatScore($midtermOrganized['quizzes'][$i]['score'], $midtermOrganized['quizzes'][$i]['max_score'])
                                            : '—';
                                        ?>
                                    </td>
                                    
                                    <td class="score-label">MACT<?php echo $i + 1; ?></td>
                                    <td class="score-input">
                                        <?php 
                                        echo isset($midtermOrganized['activities'][$i]) 
                                            ? formatScore($midtermOrganized['activities'][$i]['score'], $midtermOrganized['activities'][$i]['max_score'])
                                            : '—';
                                        ?>
                                    </td>
                                    
                                    <?php if ($i === 0): ?>
                                        <td class="special-score" rowspan="10">
                                            <?php 
                                            echo $midtermOrganized['project'] 
                                                ? formatScore($midtermOrganized['project']['score'], $midtermOrganized['project']['max_score'])
                                                : '—';
                                            ?>
                                        </td>
                                        <td class="special-score" rowspan="10">
                                            <?php 
                                            echo $midtermOrganized['exam'] 
                                                ? formatScore($midtermOrganized['exam']['score'], $midtermOrganized['exam']['max_score'])
                                                : '—';
                                            ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endfor; ?>

                                <tr class="total-row">
                                    <td class="combined-total-label">TOTAL / AVERAGE</td> 
                                    <td colspan="1" class="total-label">Total:</td>
                                    <td colspan="1" class="total-input">
                                        <?php 
                                        $qTotal = array_sum(array_column(array_filter($midtermOrganized['quizzes']), 'score'));
                                        $qMax = array_sum(array_column(array_filter($midtermOrganized['quizzes']), 'max_score'));
                                        echo $qMax > 0 ? formatScore($qTotal, $qMax) : '—';
                                        ?>
                                    </td> 
                                    <td colspan="1" class="total-label">Total:</td>
                                    <td colspan="1" class="total-input">
                                        <?php 
                                        $aTotal = array_sum(array_column(array_filter($midtermOrganized['activities']), 'score'));
                                        $aMax = array_sum(array_column(array_filter($midtermOrganized['activities']), 'max_score'));
                                        echo $aMax > 0 ? formatScore($aTotal, $aMax) : '—';
                                        ?>
                                    </td> 
                                    <td class="special-score total-input">
                                        <?php 
                                        echo $midtermOrganized['project'] 
                                            ? formatScore($midtermOrganized['project']['score'], $midtermOrganized['project']['max_score'])
                                            : '—';
                                        ?>
                                    </td>
                                    <td class="special-score total-input">
                                        <?php 
                                        echo $midtermOrganized['exam'] 
                                            ? formatScore($midtermOrganized['exam']['score'], $midtermOrganized['exam']['max_score'])
                                            : '—';
                                        ?>
                                    </td>
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
                                    <tr><th style="border-top: 1px solid var(--gray-dark);">NAME:</th><td><?php echo htmlspecialchars($formattedName); ?></td></tr>
                                    <tr><th>COURSE, YEAR & SECTION:</th><td><?php echo htmlspecialchars($student['year_section'] ?? 'N/A'); ?></td></tr>
                                    <tr><th>COURSE TITLE:</th><td><?php echo htmlspecialchars($student['class_name']); ?></td></tr>
                                    <tr><th>COURSE CODE:</th><td><?php echo htmlspecialchars($student['subject']); ?></td></tr>
                                </tbody>
                            </table>
                            <table style="width: 150px;">
                                <tr>
                                    <td class="photo-cell photo-row-height" style="height: 100%;">
                                        <div class="image-container">
                                            <img src="<?php echo htmlspecialchars($profilePicture); ?>" 
                                            alt="Student Photo"
                                            onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($defaultAvatar); ?>';">
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <table>
                            <tbody>
                                <tr class="grade-row">
                                    <th colspan="2" class="interactive-header" onclick="flipToMidterm()">Midterm Grade: (Click to flip back)</th> 
                                    <th colspan="2" style="background-color: var(--gray-light);">Final Term Grade: <?php echo $finalsAvg; ?>%</th>
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
                                <?php for ($i = 0; $i < 10; $i++): ?>
                                <tr>
                                    <?php if ($i === 0): ?>
                                        <td rowspan="10" class="term-group"></td>
                                    <?php endif; ?>
                                    
                                    <td class="score-label">FQ<?php echo $i + 1; ?></td>
                                    <td class="score-input">
                                        <?php 
                                        echo isset($finalsOrganized['quizzes'][$i]) 
                                            ? formatScore($finalsOrganized['quizzes'][$i]['score'], $finalsOrganized['quizzes'][$i]['max_score'])
                                            : '—';
                                        ?>
                                    </td>
                                    
                                    <td class="score-label">FACT<?php echo $i + 1; ?></td>
                                    <td class="score-input">
                                        <?php 
                                        echo isset($finalsOrganized['activities'][$i]) 
                                            ? formatScore($finalsOrganized['activities'][$i]['score'], $finalsOrganized['activities'][$i]['max_score'])
                                            : '—';
                                        ?>
                                    </td>
                                    
                                    <?php if ($i === 0): ?>
                                        <td class="special-score" rowspan="10">
                                            <?php 
                                            echo $finalsOrganized['project'] 
                                                ? formatScore($finalsOrganized['project']['score'], $finalsOrganized['project']['max_score'])
                                                : '—';
                                            ?>
                                        </td>
                                        <td class="special-score" rowspan="10">
                                            <?php 
                                            echo $finalsOrganized['exam'] 
                                                ? formatScore($finalsOrganized['exam']['score'], $finalsOrganized['exam']['max_score'])
                                                : '—';
                                            ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endfor; ?>

                                <tr class="total-row">
                                    <td class="combined-total-label">TOTAL / AVERAGE</td> 
                                    <td colspan="1" class="total-label">Total:</td>
                                    <td colspan="1" class="total-input">
                                        <?php 
                                        $qTotal = array_sum(array_column(array_filter($finalsOrganized['quizzes']), 'score'));
                                        $qMax = array_sum(array_column(array_filter($finalsOrganized['quizzes']), 'max_score'));
                                        echo $qMax > 0 ? formatScore($qTotal, $qMax) : '—';
                                        ?>
                                    </td> 
                                    <td colspan="1" class="total-label">Total:</td>
                                    <td colspan="1" class="total-input">
                                        <?php 
                                        $aTotal = array_sum(array_column(array_filter($finalsOrganized['activities']), 'score'));
                                        $aMax = array_sum(array_column(array_filter($finalsOrganized['activities']), 'max_score'));
                                        echo $aMax > 0 ? formatScore($aTotal, $aMax) : '—';
                                        ?>
                                    </td> 
                                    <td class="special-score total-input">
                                        <?php 
                                        echo $finalsOrganized['project'] 
                                            ? formatScore($finalsOrganized['project']['score'], $finalsOrganized['project']['max_score'])
                                            : '—';
                                        ?>
                                    </td>
                                    <td class="special-score total-input">
                                        <?php 
                                        echo $finalsOrganized['exam'] 
                                            ? formatScore($finalsOrganized['exam']['score'], $finalsOrganized['exam']['max_score'])
                                            : '—';
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const card = document.getElementById('cardFlipContainer');
        
        function closeModal() {
            if (window.parent !== window) {
                window.parent.postMessage('closeIndexCard', '*');
            } else {
                window.close();
            }
        }
        
        function flipToFinals() {
            card.classList.add('flipped');
        }
        
        function flipToMidterm() {
            card.classList.remove('flipped');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>