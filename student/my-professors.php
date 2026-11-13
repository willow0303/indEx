<?php
session_start();
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit();
}

$student_id = $_SESSION['user_id'];
$page_title = "My Professors";

$query = "
    SELECT DISTINCT
        c.class_id,
        c.class_code,
        c.class_name,
        c.subject,
        c.section,
        u.user_id as professor_id,
        u.full_name as professor_name,
        u.email as professor_email,
        u.contact_number as professor_contact,
        u.profile_picture as professor_picture,
        cs.day_of_week,
        cs.start_time,
        cs.end_time,
        cs.room
    FROM enrollments e
    INNER JOIN classes c ON e.class_id = c.class_id
    INNER JOIN users u ON c.teacher_id = u.user_id
    LEFT JOIN class_schedules cs ON c.class_id = cs.class_id
    WHERE e.student_id = :student_id
    AND e.status = 'active'
    AND c.status = 'active'
    ORDER BY c.class_code ASC
";

try {
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->execute();
    $professors_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group classes by professor with schedule info
    $professors_info = [];
    $professor_classes = [];
    
    foreach ($professors_data as $class) {
        $prof_id = $class['professor_id'];
        
        if (!isset($professors_info[$prof_id])) {
            $professors_info[$prof_id] = [
                'professor_id' => $class['professor_id'],
                'professor_name' => $class['professor_name'],
                'professor_email' => $class['professor_email'],
                'professor_contact' => $class['professor_contact'],
                'professor_picture' => $class['professor_picture']
            ];
            $professor_classes[$prof_id] = [];
        }
        
        $professor_classes[$prof_id][] = $class;
    }
    
} catch (PDOException $e) {
    error_log("Error fetching professors: " . $e->getMessage());
    $professors_data = [];
    $professors_info = [];
    $professor_classes = [];
}

// Function to format time
function formatTime($time) {
    if (empty($time)) return 'N/A';
    return date('g:i A', strtotime($time));
}

// Function to format schedule
function formatSchedule($day, $start, $end) {
    if (empty($day) || empty($start) || empty($end)) return 'To be announced';
    return $day . ', ' . formatTime($start) . ' - ' . formatTime($end);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - indEx</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/student-pages/student-main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/navigation.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/student-pages/my-professors.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include dirname(__DIR__) . '/includes/student-nav.php'; ?>
    
    <main class="main-content">
        <div class="professors-container">
            
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="page-title-section">
                        <h1 class="page-title">My Professors</h1>
                        <p class="page-subtitle">View your instructors for enrolled subjects</p>
                    </div>
                </div>
            </div>

            <div class="professors-content">
                <?php if (empty($professors_data)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <h3>No Professors Found</h3>
                        <p>You haven't enrolled in any subjects yet. Join a class to see your professors here.</p>
                        <a href="<?php echo BASE_URL; ?>student/join-class.php" class="btn-primary">
                            <i class="fas fa-plus"></i> Join a Class
                        </a>
                    </div>
                <?php else: ?>
                    <!-- OLD UI LAYOUT FOR CARDS -->
                    <div class="professors-grid">
                        <?php foreach ($professors_data as $data): ?>
                            <div class="professor-card" 
                                 data-professor-id="<?php echo $data['professor_id']; ?>"
                                 data-class-code="<?php echo htmlspecialchars($data['class_code']); ?>"
                                 data-subject="<?php echo htmlspecialchars($data['subject']); ?>"
                                 data-section="<?php echo htmlspecialchars($data['section']); ?>"
                                 data-class-name="<?php echo htmlspecialchars($data['class_name']); ?>"
                                 data-schedule="<?php echo htmlspecialchars(formatSchedule($data['day_of_week'], $data['start_time'], $data['end_time'])); ?>"
                                 data-room="<?php echo htmlspecialchars(!empty($data['room']) ? $data['room'] : 'To be announced'); ?>">
                                <div class="card-header">
                                    <h3 class="subject-title"><?php echo htmlspecialchars($data['subject']); ?></h3>
                                    <div class="subject-code"><?php echo htmlspecialchars($data['section']); ?></div>
                                </div>
                                
                                <div class="professor-info">
                                    <div class="professor-avatar">
                                        <img src="<?php echo getProfilePicture($data['professor_picture'], $data['professor_name']); ?>" 
                                             alt="<?php echo htmlspecialchars($data['professor_name']); ?>"
                                             onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.png'">
                                    </div>
                                    <div class="professor-details">
                                        <h4 class="professor-name"><?php echo htmlspecialchars($data['professor_name']); ?></h4>
                                        <div class="professor-email">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($data['professor_email']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- MODERN MODAL DESIGN -->
                    <?php foreach ($professors_info as $prof_id => $professor): ?>
                        <div class="professor-modal" id="modal-<?php echo $professor['professor_id']; ?>">
                            <div class="modal-content">
                                <span class="close-modal">&times;</span>
                                
                                <!-- Modal Header with Avatar -->
                                <div class="modal-header-section">
                                    <div class="modal-avatar-large">
                                        <img src="<?php echo getProfilePicture($professor['professor_picture'], $professor['professor_name']); ?>" 
                                             alt="<?php echo htmlspecialchars($professor['professor_name']); ?>"
                                             onerror="this.src='<?php echo BASE_URL; ?>assets/images/default-avatar.png'">
                                    </div>
                                    
                                    <div class="modal-professor-info">
                                        <h2 class="modal-prof-name"><?php echo htmlspecialchars($professor['professor_name']); ?></h2>
                                        
                                        <div class="modal-contact-group">
                                            <div class="modal-contact-item">
                                                <i class="fas fa-envelope"></i>
                                                <a href="mailto:<?php echo htmlspecialchars($professor['professor_email']); ?>">
                                                    <?php echo htmlspecialchars($professor['professor_email']); ?>
                                                </a>
                                            </div>
                                            
                                            <?php if (!empty($professor['professor_contact'])): ?>
                                            <div class="modal-contact-item">
                                                <i class="fas fa-phone"></i>
                                                <span><?php echo htmlspecialchars($professor['professor_contact']); ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Body - Class Details -->
                                <div class="modal-body" id="modal-info-<?php echo $professor['professor_id']; ?>">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include dirname(__DIR__) . '/includes/footer.php'; ?>
    
    <script>
        window.BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo BASE_URL; ?>assets/js/student-pages/my-professors.js?v=<?php echo time(); ?>"></script>
</body>
</html>