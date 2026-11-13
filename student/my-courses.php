<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireStudent();

$student_id = $_SESSION['user_id'];

try {
    // Fixed query - removed problematic GROUP BY with HAVING
    $stmt = $conn->prepare("
        SELECT 
            e.enrollment_id,
            e.student_id,
            e.class_id,
            e.enrolled_at,
            e.status,
            c.class_name,
            c.subject,
            c.section,
            c.class_code,
            c.status as class_status,
            u.full_name as teacher_name,
            u.email as teacher_email
        FROM enrollments e
        INNER JOIN classes c ON e.class_id = c.class_id
        INNER JOIN users u ON c.teacher_id = u.user_id
        WHERE e.student_id = ? 
        AND e.status = 'active' 
        AND c.status = 'active'
        ORDER BY e.enrolled_at DESC
    ");
    
    $stmt->execute([$student_id]);
    $rawCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $courses = [];
    $seenClassIds = [];
    foreach ($rawCourses as $course) {
        if (!in_array($course['class_id'], $seenClassIds)) {
            $courses[] = $course;
            $seenClassIds[] = $course['class_id'];
        }
    }
    
    foreach ($courses as &$course) {
        $classmateStmt = $conn->prepare("
            SELECT COUNT(DISTINCT student_id) as count
            FROM enrollments WHERE class_id = ? AND status = 'active' AND student_id != ?
        ");
        $classmateStmt->execute([$course['class_id'], $student_id]);
        $classmateResult = $classmateStmt->fetch(PDO::FETCH_ASSOC);
        $course['classmate_count'] = $classmateResult['count'] ?? 0;
        
        $scheduleStmt = $conn->prepare("
            SELECT schedule_id, day_of_week, start_time, end_time, room
            FROM class_schedules WHERE class_id = ? 
            ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), start_time ASC
        ");
        $scheduleStmt->execute([$course['class_id']]);
        $course['schedules'] = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($course);
    
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $courses = [];
}

$flash = getFlashMessage();
$hasCourses = count($courses) > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - indEx</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>student-pages/student-main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>navigation.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>student-pages/my-courses.css?v=<?php echo time(); ?>">
    
    <style>
        /* View Grades Button */
        .view-grades-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #8B0000 0%, #660000 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .view-grades-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.4);
            background: linear-gradient(135deg, #660000 0%, #4d0000 100%);
        }
        
        .view-grades-btn i {
            font-size: 16px;
        }

        /* Course Menu Styles - FIXED */
        .course-menu-wrapper {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
        }

        .course-menu-toggle {
            width: 36px;
            height: 36px;
            border: none;
            background: transparent;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .course-menu-toggle:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: scale(1.1);
        }

        .course-menu-toggle i {
            font-size: 18px;
        }

        /* Dropdown Menu */
        .course-menu {
            position: absolute;
            top: 45px;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .course-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .course-menu-item {
            width: 100%;
            padding: 12px 16px;
            border: none;
            background: none;
            color: #333;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            border-bottom: 1px solid #f0f0f0;
        }

        .course-menu-item:last-child {
            border-bottom: none;
            border-radius: 0 0 8px 8px;
        }

        .course-menu-item:first-child {
            border-radius: 8px 8px 0 0;
        }

        .course-menu-item:hover {
            background: #f8f8f8;
            color: #8B0000;
        }

        .course-menu-item i {
            font-size: 14px;
            width: 16px;
        }

        /* Unenroll Modal Styles - MAROON THEME */
        .unenroll-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .unenroll-modal.active {
            display: flex;
            opacity: 1;
        }

        .unenroll-modal .modal-content {
            background: white;
            border-radius: 16px;
            padding: 40px;
            max-width: 480px;
            width: 90%;
            text-align: center;
            transform: scale(0.9);
            transition: transform 0.3s ease;
            box-shadow: 0 20px 60px rgba(139, 0, 0, 0.25);
            border: 2px solid #8B0000;
        }

        .unenroll-modal.active .modal-content {
            transform: scale(1);
        }

        .modal-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(139, 0, 0, 0.1) 0%, rgba(139, 0, 0, 0.05) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            border: 3px solid rgba(139, 0, 0, 0.2);
        }

        .modal-icon i {
            font-size: 36px;
            color: #8B0000;
        }

        .modal-title {
            font-size: 26px;
            font-weight: 700;
            color: #8B0000;
            margin-bottom: 14px;
        }

        .modal-message {
            font-size: 15px;
            color: #666;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .modal-class-name {
            font-weight: 700;
            color: #8B0000;
        }

        .modal-actions {
            display: flex;
            gap: 14px;
            justify-content: center;
        }

        .modal-btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }

        .modal-btn-cancel {
            background: #f5f5f5;
            color: #333;
            border: 2px solid #e0e0e0;
        }

        .modal-btn-cancel:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        .modal-btn-confirm {
            background: linear-gradient(135deg, #8B0000 0%, #660000 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.3);
        }

        .modal-btn-confirm:hover {
            background: linear-gradient(135deg, #6d0000 0%, #8B0000 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.4);
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/student-nav.php'; ?>
        
        <div class="main-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
                    <i class="fas fa-<?php echo $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                </div>
            <?php endif; ?>

            <section class="courses-section">
                <div class="page-header">
                    <div class="page-header-content">
                        <div class="page-icon">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="page-title-section">
                            <h1 class="page-title">My Courses</h1>
                            <p class="page-subtitle">Access your active classes and learning materials</p>
                        </div>
                    </div>
                </div>

                <div class="courses-wrapper">
                    <?php if ($hasCourses): ?>
                        <div class="courses-grid" id="coursesGrid">
                            <?php foreach ($courses as $course): ?>
                                <div class="course-card" data-course-id="<?php echo $course['class_id']; ?>">
                                    <div class="course-header">
                                        <div class="course-info-header">
                                            <div class="course-code"><?php echo htmlspecialchars($course['subject']); ?></div>
                                            <h3 class="course-name"><?php echo htmlspecialchars($course['class_name']); ?></h3>
                                            <div class="course-professor-header">
                                                <i class="fas fa-user-tie"></i>
                                                <span><?php echo htmlspecialchars($course['teacher_name']); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="course-menu-wrapper">
                                        <button class="course-menu-toggle" type="button">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="course-menu">
                                            <button type="button" class="course-menu-item open-unenroll-modal" 
                                                    data-course-id="<?php echo $course['class_id']; ?>"
                                                    data-class-name="<?php echo htmlspecialchars($course['class_name']); ?>">
                                                <i class="fas fa-sign-out-alt"></i> Unenroll from Class
                                            </button>
                                        </div>
                                    </div>

                                    <div class="course-body">
                                        <?php if (!empty($course['schedules'])): ?>
                                            <div class="course-schedules">
                                                <h4 class="schedules-title">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <span class="schedule-day"><?php echo htmlspecialchars($course['schedules'][0]['day_of_week']); ?></span>
                                                </h4>
                                                <div class="schedule-list">
                                                    <?php foreach ($course['schedules'] as $schedule): ?>
                                                        <div class="schedule-item">
                                                            <span class="schedule-time">
                                                                <i class="fas fa-clock"></i>
                                                                <?php echo date('g:i A', strtotime($schedule['start_time'])) . ' - ' . date('g:i A', strtotime($schedule['end_time'])); ?>
                                                            </span>
                                                            <?php if (!empty($schedule['room'])): ?>
                                                                <span class="schedule-room">
                                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($schedule['room']); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="course-info-list">
                                            <div class="course-info-item">
                                                <i class="fas fa-users"></i>
                                                <span><?php echo $course['classmate_count']; ?> Classmates</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="course-footer">
                                        <button type="button" class="view-grades-btn" data-class-id="<?php echo $course['class_id']; ?>">
                                            <i class="fas fa-eye"></i> View Grades
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <h3>No Classes Yet</h3>
                            <p>You haven't enrolled in any classes. Start your learning journey by joining a class with your instructor's code.</p>
                            <a href="join-class.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Join New Class
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            
            <?php include '../includes/footer.php'; ?>
        </div>
        
        <iframe id="gradeCardFrame" src="student-index-card-handler.php" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; border: none; z-index: 9999; background: transparent;"></iframe>
    </div>

    <!-- Unenroll Modal -->
    <div id="unenrollModal" class="unenroll-modal">
        <div class="modal-content">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="modal-title">Unenroll from Class?</h3>
            <p class="modal-message">
                Are you sure you want to unenroll from <span class="modal-class-name" id="modalClassName"></span>?
                <br><br>
                You will lose access to all course materials. You can rejoin anytime using the class code.
            </p>
            <form method="POST" action="<?php echo BASE_URL; ?>api/student/unenroll-handler.php" class="modal-actions">
                <input type="hidden" name="class_id" id="modalClassId">
                <button type="button" class="modal-btn modal-btn-cancel" id="closeModal">Cancel</button>
                <button type="submit" class="modal-btn modal-btn-confirm">Yes, Unenroll</button>
            </form>
        </div>
    </div>

    <script src="<?php echo JS_PATH; ?>main.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('My Courses page loaded');
        
        // ==========================================
        // COURSE MENU DROPDOWN FUNCTIONALITY
        // ==========================================
        
        const menuToggles = document.querySelectorAll('.course-menu-toggle');
        const menuDropdowns = document.querySelectorAll('.course-menu');
        
        console.log('Found', menuToggles.length, 'menu toggles');
        console.log('Found', menuDropdowns.length, 'menu dropdowns');
        
        // Toggle menu on button click
        menuToggles.forEach((toggle) => {
            toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                console.log('Menu toggle clicked');
                
                const menu = this.nextElementSibling;
                
                // Close all other menus
                menuDropdowns.forEach(m => {
                    if (m !== menu) {
                        m.classList.remove('active');
                    }
                });
                
                // Toggle current menu
                menu.classList.toggle('active');
                console.log('Menu is now:', menu.classList.contains('active') ? 'open' : 'closed');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.course-menu-wrapper')) {
                menuDropdowns.forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });
        
        // ==========================================
        // UNENROLL MODAL FUNCTIONALITY
        // ==========================================
        
        const modal = document.getElementById('unenrollModal');
        const modalClassId = document.getElementById('modalClassId');
        const modalClassName = document.getElementById('modalClassName');
        const closeModalBtn = document.getElementById('closeModal');
        const unenrollButtons = document.querySelectorAll('.open-unenroll-modal');
        
        console.log('Found', unenrollButtons.length, 'unenroll buttons');
        
        // Open modal when clicking "Unenroll from Class"
        unenrollButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const courseId = this.getAttribute('data-course-id');
                const className = this.getAttribute('data-class-name');
                
                console.log('Opening unenroll modal for:', className);
                
                modalClassId.value = courseId;
                modalClassName.textContent = className;
                
                modal.classList.add('active');
                
                // Close the dropdown menu
                const menu = this.closest('.course-menu');
                if (menu) {
                    menu.classList.remove('active');
                }
            });
        });
        
        // Close modal on cancel button
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', function() {
                console.log('Closing modal');
                modal.classList.remove('active');
            });
        }
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                console.log('Closing modal (clicked outside)');
                modal.classList.remove('active');
            }
        });
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                console.log('Closing modal (ESC key)');
                modal.classList.remove('active');
            }
        });
        
        // ==========================================
        // GRADE CARD FUNCTIONALITY
        // ==========================================
        
        const gradeCardFrame = document.getElementById('gradeCardFrame');
        const viewGradesBtns = document.querySelectorAll('.view-grades-btn');
        
        console.log('Found', viewGradesBtns.length, 'view grades buttons');
        
        viewGradesBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const classId = this.getAttribute('data-class-id');
                console.log('Opening grade card for class:', classId);
                
                gradeCardFrame.style.display = 'block';
                
                setTimeout(() => {
                    gradeCardFrame.contentWindow.postMessage({
                        action: 'openGradeCard',
                        classId: classId
                    }, '*');
                }, 100);
            });
        });
        
        // Listen for close message from iframe
        window.addEventListener('message', function(event) {
            if (event.data === 'closeGradeCardModal') {
                console.log('Closing grade card modal');
                setTimeout(() => {
                    gradeCardFrame.style.display = 'none';
                }, 300);
            }
        });
        
        console.log('All event listeners initialized successfully');
    });
    </script>
</body>
</html>