<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireTeacher();

$teacher_id = $_SESSION['user_id'];
$teacher_info = null;

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
    
    // If somehow the separate columns are empty, try to parse from full_name as fallback
    if (empty($first_name) && empty($last_name) && !empty($teacher_info['full_name'])) {
        $full_name = trim($teacher_info['full_name']);
        $name_parts = explode(' ', $full_name);
        
        if (count($name_parts) == 1) {
            $first_name = $name_parts[0];
        } elseif (count($name_parts) == 2) {
            $first_name = $name_parts[0];
            $last_name = $name_parts[1];
        } else {
            $first_name = array_shift($name_parts); 
            $last_name = array_pop($name_parts);     
            $middle_name = implode(' ', $name_parts); 
        }
    }
    
    // Get profile picture
    $profile_pic_url = getProfilePicture($teacher_info['profile_picture'] ?? null, $teacher_info['full_name']);
    
} catch (PDOException $e) {
    error_log("Profile Settings Error: " . $e->getMessage());
    redirectWithMessage(BASE_URL . 'teacher/dashboard.php', 'danger', 'Error loading settings.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="user-id" content="<?php echo $teacher_id; ?>">
    <title>Account Settings - Online Grading System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>teacher-pages/profile-settings.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/teacher-nav.php'; ?>
        
        <div class="main-content">
            <div class="dashboard-content">
                <div class="settings-container">
                    <div class="settings-grid">
                        <!-- Left Column: Profile Picture -->
                        <div class="left-column">
                            <div class="settings-card profile-card-highlight">
                                <div class="profile-picture-section-center">
                                    <div class="profile-picture-preview" id="profilePicturePreview">
                                        <div class="profile-picture-border">
                                            <img src="<?php echo $profile_pic_url; ?>" alt="Profile Picture" id="profilePictureImage">
                                        </div>
                                        <div class="profile-picture-overlay" id="uploadTrigger">
                                            <i class="fas fa-camera"></i>
                                            <span>Change Photo</span>
                                        </div>
                                    </div>
                                    <div class="profile-picture-info">
                                        <h3 class="profile-picture-name"><?php echo htmlspecialchars($teacher_info['full_name']); ?></h3>
                                        <p class="profile-picture-role">
                                            <i class="fas fa-chalkboard-teacher"></i> Teacher
                                        </p>
                                    </div>
                                    <div class="profile-picture-actions-center">
                                        <button type="button" class="btn-upload-picture" id="btnUploadPicture">
                                            <i class="fas fa-upload"></i> Upload New Photo
                                        </button>
                                        <button type="button" class="btn-change-password" id="btnOpenPasswordModal">
                                            <i class="fas fa-key"></i> Change Password
                                        </button>
                                    </div>
                                    <input type="file" id="profilePictureInput" accept="image/jpeg,image/png,image/jpg" style="display: none;">
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Edit Profile -->
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <div class="settings-card-icon">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <h2 class="settings-card-title">Profile Information</h2>
                            </div>

                            <form id="profileForm">
                                <div class="form-row-3">
                                    <div class="form-group">
                                        <label class="form-label required">First Name</label>
                                        <input type="text" class="form-input" id="firstName" name="first_name" 
                                               value="<?php echo htmlspecialchars($first_name); ?>" required>
                                        <div class="form-error" id="firstNameError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-input" id="middleName" name="middle_name" 
                                               value="<?php echo htmlspecialchars($middle_name); ?>">
                                        <div class="form-error" id="middleNameError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required">Last Name</label>
                                        <input type="text" class="form-input" id="lastName" name="last_name" 
                                               value="<?php echo htmlspecialchars($last_name); ?>" required>
                                        <div class="form-error" id="lastNameError"></div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label required">Contact Number</label>
                                        <input type="tel" class="form-input" id="contactNumber" name="contact_number" 
                                               value="<?php echo htmlspecialchars($teacher_info['contact_number'] ?? ''); ?>" 
                                               placeholder="+63 912 345 6789" required>
                                        <div class="form-error" id="contactNumberError"></div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label required">Email Address</label>
                                        <input type="email" class="form-input" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($teacher_info['email']); ?>" required>
                                        <div class="form-error" id="emailError"></div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-input" value="Teacher" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Member Since</label>
                                        <input type="text" class="form-input" 
                                               value="<?php echo formatDateTime($teacher_info['created_at']); ?>" disabled>
                                    </div>
                                </div>

                                <div class="settings-actions">
                                    <button type="button" class="btn-cancel" id="btnCancelProfile">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    <button type="submit" class="btn-save" id="btnSaveProfile">
                                        <i class="fas fa-check"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php include '../includes/footer.php'; ?>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div class="modal-overlay" id="passwordModal">
        <div class="modal-content">
            <button class="modal-close" id="btnClosePasswordModal">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="modal-title">Change Password</h3>
                <p class="modal-subtitle">Enter your current password and choose a new secure password</p>
            </div>

            <div class="modal-body">
                <form id="passwordForm">
                    <div class="form-group">
                        <label class="form-label required">Current Password</label>
                        <input type="password" class="form-input" id="currentPassword" name="current_password" 
                               autocomplete="current-password" required>
                        <div class="form-error" id="currentPasswordError"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">New Password</label>
                        <input type="password" class="form-input" id="newPassword" name="new_password" 
                               autocomplete="new-password" required>
                        <div class="form-error" id="newPasswordError"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Confirm New Password</label>
                        <input type="password" class="form-input" id="confirmPassword" name="confirm_password" 
                               autocomplete="new-password" required>
                        <div class="form-error" id="confirmPasswordError"></div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" id="btnCancelPassword">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-save" id="btnSavePassword">
                            <i class="fas fa-check"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
    <script src="<?php echo JS_PATH; ?>main.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo JS_PATH; ?>teacher-pages/account-settings.js?v=<?php echo time(); ?>"></script>
</body>
</html>