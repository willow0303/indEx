<?php
header('Content-Type: text/html; charset=UTF-8');

require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../api/teacher/view-sections-handler.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];

if (!isset($_GET['course']) || !isset($_GET['name'])) {
    header('Location: my-courses.php');
    exit();
}

$course_subject = $_GET['course'];
$course_name = $_GET['name'];

$course_info = getCourseInfo($teacher_id, $course_subject, $course_name);
$sections = getCourseSections($teacher_id, $course_subject, $course_name);

if (!$course_info) {
    header('Location: my-courses.php');
    exit();
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course_subject . ' - ' . $course_name); ?> - indEx</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>navigation.css">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>teacher-pages/view-section.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/teacher-nav.php'; ?>
        
        <div class="main-content">
            <div class="content-wrapper">
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
                        <i class="fas fa-<?php echo $flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                <?php endif; ?>

                <section class="sections-page">
                    <!-- COMPACT HEADER -->
                    <div class="sections-header">
                        <div class="header-content">
                            <a href="my-courses.php" class="back-link">
                                <i class="fas fa-arrow-left"></i>
                                <span>Back to Courses</span>
                            </a>
                            <h3 class="section-title">
                                <?php echo htmlspecialchars($course_subject); ?> - <?php echo htmlspecialchars($course_name); ?>
                            </h3>
                        </div>
                        <div class="header-actions">
                            <button type="button" class="btn btn-sort" id="sortSectionsBtn">
                                <i class="fas fa-sort"></i>
                                <span>Sort</span>
                            </button>
                            <button type="button" class="btn btn-primary" id="openAddSectionModal">
                                <i class="fas fa-plus"></i>
                                <span>Add Section</span>
                            </button>
                            
                            <!-- Sort Dropdown -->
                            <div class="sort-dropdown" id="sortDropdown">
                                <div class="sort-dropdown-header">
                                    <i class="fas fa-sort-amount-down"></i>
                                    <span>Sort Sections By</span>
                                </div>
                                <button class="sort-option active" data-sort="default">
                                    <i class="fas fa-list-ol"></i>
                                    <span>Default Order</span>
                                </button>
                                <button class="sort-option" data-sort="name-asc">
                                    <i class="fas fa-sort-alpha-down"></i>
                                    <span>Name (A-Z)</span>
                                </button>
                                <button class="sort-option" data-sort="name-desc">
                                    <i class="fas fa-sort-alpha-up"></i>
                                    <span>Name (Z-A)</span>
                                </button>
                                <button class="sort-option" data-sort="students-desc">
                                    <i class="fas fa-users"></i>
                                    <span>Most Students</span>
                                </button>
                                <button class="sort-option" data-sort="students-asc">
                                    <i class="fas fa-user"></i>
                                    <span>Least Students</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if (count($sections) > 0): ?>
                        <!-- HORIZONTAL SCROLL CONTAINER -->
                        <div class="sections-scroll-container">
                            <button class="scroll-btn scroll-left" id="scrollLeft" style="display: none;">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            
                            <div class="sections-wrapper" id="sectionsWrapper">
                                <?php foreach ($sections as $section): ?>
                                    <div class="section-card">
                                        <!-- CARD HEADER -->
                                        <div class="section-header">
                                            <div class="section-header-left">
                                                <h3 class="section-name">Section <?php echo htmlspecialchars($section['section']); ?></h3>
                                                <p class="section-students">
                                                    <i class="fas fa-users"></i>
                                                    <span><?php echo $section['student_count']; ?> Student<?php echo $section['student_count'] != 1 ? 's' : ''; ?></span>
                                                </p>
                                            </div>
                                            <div class="section-menu">
                                                <button class="btn-menu" onclick="toggleMenu(event, <?php echo $section['class_id']; ?>)">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="menu-dropdown" id="menu-<?php echo $section['class_id']; ?>">
                                                    <button class="menu-item archive" onclick="showArchiveModal(<?php echo $section['class_id']; ?>, '<?php echo htmlspecialchars($section['section'], ENT_QUOTES); ?>', <?php echo $section['student_count']; ?>)">
                                                        <i class="fas fa-archive"></i>
                                                        <span>Archive Section</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- CARD BODY -->
                                        <div class="section-body">
                                            <!-- CLASS CODE -->
                                            <div class="section-code-display">
                                                <div class="code-label">Class Code</div>
                                                <div class="code-value"><?php echo htmlspecialchars($section['class_code']); ?></div>
                                                <button class="btn-copy-code" data-code="<?php echo htmlspecialchars($section['class_code']); ?>" title="Copy code">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- SCHEDULES -->
                                            <?php if (!empty($section['schedules'])): ?>
                                                <div class="section-schedules">
                                                    <h4 class="schedules-title">
                                                        <span class="schedules-title-left">
                                                            <i class="fas fa-calendar-alt"></i>
                                                            <span>Schedule</span>
                                                        </span>
                                                        <?php 
                                                        $rooms = array_filter(array_unique(array_column($section['schedules'], 'room')));
                                                        if (!empty($rooms)): 
                                                        ?>
                                                            <span class="schedule-room">
                                                                <i class="fas fa-map-marker-alt"></i>
                                                                <span><?php echo htmlspecialchars(implode(', ', $rooms)); ?></span>
                                                            </span>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <div class="schedule-list">
                                                        <?php foreach ($section['schedules'] as $schedule): ?>
                                                            <div class="schedule-item-display">
                                                                <span class="schedule-day"><?php echo htmlspecialchars($schedule['day_of_week']); ?></span>
                                                                <span class="schedule-time">
                                                                    <?php 
                                                                    echo date('g:i A', strtotime($schedule['start_time'])) . ' - ' . 
                                                                         date('g:i A', strtotime($schedule['end_time'])); 
                                                                    ?>
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="no-schedules">
                                                    <i class="fas fa-calendar-times"></i>
                                                    <span>No schedule set</span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- ACTION BUTTON -->
                                            <div class="section-actions">
                                                <a href="view-grades.php?class_id=<?php echo $section['class_id']; ?>" class="course-btn">
                                                    <i class="fas fa-eye"></i>
                                                    <span>View Grades</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <button class="scroll-btn scroll-right" id="scrollRight" style="display: none;">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- EMPTY STATE -->
                        <div class="empty-state">
                            <i class="fas fa-book-open"></i>
                            <h3>No Sections Yet</h3>
                            <p>Create your first section for this course</p>
                            <button type="button" class="btn btn-primary" id="openAddSectionModalEmpty">
                                <i class="fas fa-plus"></i>
                                <span>Add Section</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>

        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- ADD SECTION MODAL -->
    <div class="modal-overlay" id="addSectionModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-content">
                    <div class="modal-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h2 class="modal-title">Add New Section</h2>
                        <p class="modal-subtitle"><?php echo htmlspecialchars($course_subject . ' - ' . $course_name); ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" id="closeModalBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="addSectionForm" action="<?php echo BASE_URL; ?>api/teacher/create-class-handler.php" method="POST">
                <!-- Hidden fields for course info -->
                <input type="hidden" name="class_name" value="<?php echo htmlspecialchars($course_name); ?>">
                <input type="hidden" name="subject" value="<?php echo htmlspecialchars($course_subject); ?>">

                <div class="modal-body">
                    <!-- Section Name -->
                    <div class="form-group-modal">
                        <label class="form-label-modal">
                            <span>Section Name</span>
                            <span class="label-required">*</span>
                        </label>
                        <div class="input-wrapper-modal">
                            <input 
                                type="text" 
                                name="section" 
                                class="form-input-modal" 
                                placeholder="e.g., Section A, 1-A"
                                required
                                maxlength="50"
                            >
                            <i class="fas fa-users input-icon-modal"></i>
                        </div>
                    </div>

                    <!-- Schedule Section -->
                    <div class="schedule-section-modal">
                        <div class="schedule-header-modal">
                            <h3 class="schedule-title-modal">Class Schedule (Optional)</h3>
                            <button type="button" class="btn-add-schedule-modal" id="addScheduleBtnModal">
                                <i class="fas fa-plus"></i>
                                Add
                            </button>
                        </div>
                        <div id="schedulesContainerModal"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal btn-modal-cancel" id="cancelModalBtn">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn-modal btn-modal-submit" id="submitModalBtn">
                        <i class="fas fa-check"></i>
                        Add Section
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ARCHIVE CONFIRMATION MODAL -->
    <div class="archive-modal-overlay" id="archiveModal">
        <div class="archive-modal-content">
            <div class="archive-modal-header">
                <div class="archive-modal-icon">
                    <i class="fas fa-archive"></i>
                </div>
                <div class="archive-modal-title-section">
                    <h3>Archive Section?</h3>
                    <p>This action will move the section to archive</p>
                </div>
            </div>

            <div class="archive-modal-body">
                <div class="archive-info-box">
                    <div class="archive-section-name">
                        <i class="fas fa-bookmark"></i>
                        <span id="archiveSectionName"></span>
                    </div>
                    <div class="archive-section-details" id="archiveSectionDetails"></div>
                </div>

                <div class="archive-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="archive-warning-text">
                        <strong>What happens when you archive?</strong>
                        <p>â€¢ Section will be moved to archive<br>
                        â€¢ Students can no longer access this class<br>
                        â€¢ All grades and attendance records will be preserved<br>
                        â€¢ You can restore it later from the archive</p>
                    </div>
                </div>
            </div>

            <div class="archive-modal-footer">
                <button type="button" class="btn-archive-modal btn-archive-cancel" onclick="closeArchiveModal()">
                    <i class="fas fa-times"></i>
                    Cancel
                </button>
                <button type="button" class="btn-archive-modal btn-archive-confirm" id="confirmArchiveBtn">
                    <i class="fas fa-archive"></i>
                    Archive Section
                </button>
            </div>
        </div>
    </div>

    <script>
    // Global variable for current archive class
    let currentArchiveClassId = null;

    // Three-dots menu functionality
    function toggleMenu(event, classId) {
        event.stopPropagation();
        const menu = document.getElementById('menu-' + classId);
        const allMenus = document.querySelectorAll('.menu-dropdown');
        
        // Close all other menus
        allMenus.forEach(m => {
            if (m.id !== 'menu-' + classId) {
                m.classList.remove('show');
            }
        });
        
        // Toggle current menu
        menu.classList.toggle('show');
    }

    // Close menus when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.section-menu')) {
            document.querySelectorAll('.menu-dropdown').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Archive modal functions
    function showArchiveModal(classId, sectionName, studentCount) {
        currentArchiveClassId = classId;
        
        document.getElementById('archiveSectionName').textContent = 'Section ' + sectionName;
        document.getElementById('archiveSectionDetails').innerHTML = 
            '<i class="fas fa-users"></i> ' + studentCount + ' student' + (studentCount != 1 ? 's' : '') + ' enrolled';
        
        document.getElementById('archiveModal').classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Close the dropdown menu
        document.querySelectorAll('.menu-dropdown').forEach(menu => {
            menu.classList.remove('show');
        });
    }

    function closeArchiveModal() {
        document.getElementById('archiveModal').classList.remove('show');
        document.body.style.overflow = '';
        currentArchiveClassId = null;
    }

    // Archive confirmation
    document.getElementById('confirmArchiveBtn').addEventListener('click', function() {
        if (!currentArchiveClassId) return;
        
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Archiving...';
        
        // Send archive request
        fetch('<?php echo BASE_URL; ?>api/teacher/archive-section-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'class_id=' + currentArchiveClassId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and reload page
                closeArchiveModal();
                window.location.href = '<?php echo BASE_URL; ?>teacher/view-sections.php?course=<?php echo urlencode($course_subject); ?>&name=<?php echo urlencode($course_name); ?>&archived=1';
            } else {
                alert('Error: ' + (data.message || 'Failed to archive section'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-archive"></i> Archive Section';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Network error. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-archive"></i> Archive Section';
        });
    });

    // Close archive modal on outside click
    document.getElementById('archiveModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeArchiveModal();
        }
    });

    // Modal functionality for add section
    const modal = document.getElementById('addSectionModal');
    const openBtns = [
        document.getElementById('openAddSectionModal'),
        document.getElementById('openAddSectionModalEmpty')
    ].filter(Boolean);
    const closeBtns = [
        document.getElementById('closeModalBtn'),
        document.getElementById('cancelModalBtn')
    ];

    openBtns.forEach(btn => {
        if (btn) {
            btn.onclick = () => {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            };
        }
    });

    closeBtns.forEach(btn => {
        btn.onclick = () => {
            modal.classList.remove('show');
            document.body.style.overflow = '';
            document.getElementById('addSectionForm').reset();
            document.getElementById('schedulesContainerModal').innerHTML = '';
            scheduleCount = 0;
        };
    });

    // Close on outside click
    modal.onclick = (e) => {
        if (e.target === modal) {
            closeBtns[0].click();
        }
    };

    // Schedule management
    let scheduleCount = 0;
    const addScheduleBtn = document.getElementById('addScheduleBtnModal');
    const container = document.getElementById('schedulesContainerModal');

    addScheduleBtn.onclick = function(e) {
        e.preventDefault();
        addSchedule();
    };

    function addSchedule() {
        scheduleCount++;
        
        const html = `
            <div class="schedule-item-modal" data-schedule-id="${scheduleCount}">
                <div class="schedule-item-header-modal">
                    <span class="schedule-badge-modal">Schedule ${scheduleCount}</span>
                    <button type="button" class="btn-remove-schedule-modal" onclick="removeSchedule(${scheduleCount})">
                        <i class="fas fa-trash-alt"></i>
                        Remove
                    </button>
                </div>
                
                <div class="schedule-row-modal">
                    <div>
                        <label class="schedule-label-modal">Day</label>
                        <select name="schedules[${scheduleCount}][day]" class="schedule-select-modal" required>
                            <option value="">Select</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="schedule-label-modal">Start Time</label>
                        <input 
                            type="time" 
                            name="schedules[${scheduleCount}][start_time]" 
                            class="schedule-input-modal"
                            required
                        >
                    </div>
                    
                    <div>
                        <label class="schedule-label-modal">End Time</label>
                        <input 
                            type="time" 
                            name="schedules[${scheduleCount}][end_time]" 
                            class="schedule-input-modal"
                            required
                        >
                    </div>
                </div>
                
                <div>
                    <label class="schedule-label-modal">Room (Optional)</label>
                    <input 
                        type="text" 
                        name="schedules[${scheduleCount}][room]" 
                        class="schedule-input-modal"
                        placeholder="e.g., Room 301"
                        maxlength="100"
                    >
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', html);
    }

    window.removeSchedule = function(id) {
        const item = document.querySelector(`[data-schedule-id="${id}"]`);
        if (item) {
            item.style.opacity = '0';
            item.style.transform = 'translateY(-10px)';
            setTimeout(() => item.remove(), 300);
        }
    };

    // Form submission
    document.getElementById('addSectionForm').onsubmit = function() {
        const btn = document.getElementById('submitModalBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    };

    // Show success message if redirected after archiving
    <?php if (isset($_GET['archived']) && $_GET['archived'] == '1'): ?>
        const successAlert = document.createElement('div');
        successAlert.className = 'alert alert-success';
        successAlert.innerHTML = '<i class="fas fa-check-circle"></i><span>Section archived successfully</span>';
        const contentWrapper = document.querySelector('.content-wrapper');
        contentWrapper.insertBefore(successAlert, contentWrapper.firstChild);
        
        // Remove archived parameter from URL
        const url = new URL(window.location);
        url.searchParams.delete('archived');
        window.history.replaceState({}, '', url);
        
        // Remove alert after 3 seconds
        setTimeout(() => {
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 300);
        }, 3000);
    <?php endif; ?>

    // Sort functionality
    const sortBtn = document.getElementById('sortSectionsBtn');
    const sortDropdown = document.getElementById('sortDropdown');
    let currentSort = 'default';

    if (sortBtn && sortDropdown) {
        sortBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sortDropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.header-actions')) {
                sortDropdown.classList.remove('show');
            }
        });

        // Sort options click handler
        document.querySelectorAll('.sort-option').forEach(option => {
            option.addEventListener('click', function() {
                const sortType = this.dataset.sort;
                
                // Update active state
                document.querySelectorAll('.sort-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                
                // Sort sections
                sortSections(sortType);
                
                // Close dropdown
                sortDropdown.classList.remove('show');
            });
        });

        function sortSections(sortType) {
            const wrapper = document.getElementById('sectionsWrapper');
            const cards = Array.from(wrapper.querySelectorAll('.section-card'));
            
            cards.sort((a, b) => {
                switch(sortType) {
                    case 'name-asc':
                        return a.querySelector('.section-name').textContent.localeCompare(
                            b.querySelector('.section-name').textContent
                        );
                    case 'name-desc':
                        return b.querySelector('.section-name').textContent.localeCompare(
                            a.querySelector('.section-name').textContent
                        );
                    case 'students-desc':
                        return parseInt(b.querySelector('.section-students span').textContent) - 
                               parseInt(a.querySelector('.section-students span').textContent);
                    case 'students-asc':
                        return parseInt(a.querySelector('.section-students span').textContent) - 
                               parseInt(b.querySelector('.section-students span').textContent);
                    default:
                        return 0;
                }
            });
            
            // Re-append sorted cards
            cards.forEach(card => wrapper.appendChild(card));
        }
    }
    </script>

    <script src="<?php echo JS_PATH; ?>teacher-pages/view-sections.js?v=<?php echo time(); ?>"></script>
</body>
</html>