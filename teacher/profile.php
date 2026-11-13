<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];
$teacher_info = null;
$flash = getFlashMessage();

try {
    // Get teacher information from database with separate name columns
    $stmt = $conn->prepare("
        SELECT 
            user_id,
            email,
            full_name,
            first_name,
            middle_name,
            last_name,
            contact_number,
            profile_picture,
            created_at,
            role,
            status
        FROM users 
        WHERE user_id = ? AND role = 'teacher'
    ");
    $stmt->execute([$teacher_id]);
    $teacher_info = $stmt->fetch();
    
    if (!$teacher_info) {
        redirectWithMessage(BASE_URL . 'teacher/dashboard.php', 'danger', 'Teacher information not found.');
    }
    
    // Use the separate name columns from database
    $first_name = $teacher_info['first_name'] ?? '';
    $middle_name = $teacher_info['middle_name'] ?? '';
    $last_name = $teacher_info['last_name'] ?? '';
    
    // Create middle initial if middle name exists
    $middle_initial = '';
    if (!empty($middle_name)) {
        $middle_initial = strtoupper(substr(trim($middle_name), 0, 1)) . '.';
    }
    
    // Format: Last Name, First Name M.
    $full_name_display = trim($last_name);
    if (!empty($first_name)) {
        $full_name_display .= ', ' . trim($first_name);
        if ($middle_initial) {
            $full_name_display .= ' ' . $middle_initial;
        }
    }
    
    // If somehow the separate columns are empty, fall back to full_name
    if (empty($full_name_display) || $full_name_display === ',') {
        $full_name_display = $teacher_info['full_name'] ?? 'Unknown';
    }
    
    // Get profile picture
    $profile_pic_url = getProfilePicture($teacher_info['profile_picture'] ?? null, $teacher_info['full_name']);
    
} catch (PDOException $e) {
    error_log("Profile Error: " . $e->getMessage());
    redirectWithMessage(BASE_URL . 'teacher/dashboard.php', 'danger', 'Error loading profile.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Online Grading System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>teacher-pages/teacher-profile.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/teacher-nav.php'; ?>
        
        <div class="main-content">
            <div class="dashboard-content">
                <div class="profile-container">
                    <div class="profile-wrapper">
                        <?php if ($flash): ?>
                            <div class="alert alert-<?php echo $flash['type']; ?>">
                                <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                                <span><?php echo htmlspecialchars($flash['message']); ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Main Profile Card -->
                        <div class="profile-main-card">
                            <div class="profile-content">
                                <!-- Profile Image -->
                                <div class="profile-image-container">
                                    <div class="profile-image-wrapper">
                                        <img 
                                            src="<?php echo $profile_pic_url; ?>" 
                                            alt="<?php echo htmlspecialchars($teacher_info['full_name']); ?>" 
                                            class="profile-image"
                                            id="profileImage">
                                        <div class="profile-status-badge">
                                            <i class="fas fa-circle"></i> Teacher
                                        </div>
                                    </div>
                                </div>

                                <!-- Profile Details -->
                                <div class="profile-details-container">
                                    <div class="profile-name-section">
                                        <h1 class="profile-name"><?php echo htmlspecialchars($full_name_display); ?></h1>
                                        <p class="profile-role">
                                            <i class="fas fa-user-tag"></i>
                                            Role: Teacher
                                        </p>
                                        <p class="profile-email">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($teacher_info['email']); ?>
                                        </p>
                                    </div>

                                    <!-- Contact Information -->
                                    <div class="profile-contact-section">
                                        <div class="profile-contact-item">
                                            <div class="profile-contact-icon">
                                                <i class="fas fa-phone"></i>
                                            </div>
                                            <span class="profile-contact-text">
                                                <?php echo htmlspecialchars($teacher_info['contact_number'] ?: 'No Contact Number'); ?>
                                            </span>
                                        </div>

                                        <div class="profile-contact-item">
                                            <div class="profile-contact-icon">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <span class="profile-contact-text">
                                                Member since <?php echo date('F Y', strtotime($teacher_info['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Compact Action Buttons -->
                            <div class="profile-actions-modern">
                                <a href="<?php echo BASE_URL; ?>teacher/account-settings.php" class="action-button-modern">
                                    <i class="fas fa-cog action-button-icon-modern"></i>
                                    <span class="action-button-label-modern">Account Settings</span>
                                </a>

                                <a href="<?php echo BASE_URL; ?>teacher/overview.php" class="action-button-modern">
                                    <i class="fas fa-th-large action-button-icon-modern"></i>
                                    <span class="action-button-label-modern">Overview</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>

    <script src="<?php echo JS_PATH; ?>main.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo JS_PATH; ?>teacher-pages/teacher-profile.js?v=<?php echo time(); ?>"></script>
</body>
</html>