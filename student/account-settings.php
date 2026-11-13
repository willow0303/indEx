<?php

require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

requireStudent();

$student_id = $_SESSION['user_id'];
$student_info = null;

try {
    // Select individual name columns from database
    $stmt = $conn->prepare("
        SELECT 
            user_id,
            email,
            full_name,
            first_name,
            middle_name,
            last_name,
            student_number,
            program,
            year_section,
            contact_number,
            profile_picture,
            created_at
        FROM users 
        WHERE user_id = ? AND role = 'student'
    ");
    $stmt->execute([$student_id]);
    $student_info = $stmt->fetch();
    
    if (!$student_info) {
        redirectWithMessage(BASE_URL . 'student/dashboard.php', 'danger', 'Student information not found.');
    }
    
    // Use database columns directly, DO NOT parse full_name
    $first_name = $student_info['first_name'] ?? '';
    $middle_name = $student_info['middle_name'] ?? '';
    $last_name = $student_info['last_name'] ?? '';
    
    // Only trim whitespace, preserve multi-word names
    $first_name = trim($first_name);
    $middle_name = trim($middle_name);
    $last_name = trim($last_name);
    
    error_log("=== Account Settings Display ===");
    error_log("User ID: $student_id");
    error_log("First Name: '$first_name'");
    error_log("Middle Name: '$middle_name'");
    error_log("Last Name: '$last_name'");
    error_log("Full Name: '" . $student_info['full_name'] . "'");
    
    $profile_pic_url = getProfilePicture($student_info['profile_picture'] ?? null, $student_info['full_name']);
    
} catch (PDOException $e) {
    error_log("Account Settings Error: " . $e->getMessage());
    redirectWithMessage(BASE_URL . 'student/dashboard.php', 'danger', 'Error loading settings.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="user-id" content="<?php echo $student_id; ?>">
    <title>Account Settings - indEx</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>navigation.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>student-pages/account-settings.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/student-nav.php'; ?>
        
        <div class="main-content">
            <div class="settings-container">
                <div class="settings-grid">
                    <div class="left-column">
                        <!-- Profile Picture Card -->
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
                                    <h3 class="profile-picture-name"><?php echo htmlspecialchars($student_info['full_name']); ?></h3>
                                    <p class="profile-picture-role">
                                        <i class="fas fa-user-graduate"></i> Student
                                    </p>
                                </div>
                                <div class="profile-picture-actions-center">
                                    <button type="button" class="btn-upload-picture" id="btnUploadPicture">
                                        <i class="fas fa-upload"></i> Upload New Photo
                                    </button>
                                    <button type="button" class="btn-remove-picture" id="btnRemovePicture">
                                        <i class="fas fa-trash-alt"></i> Remove Photo
                                    </button>
                                </div>
                                <input type="file" id="profilePictureInput" accept="image/jpeg,image/png,image/jpg" style="display: none;">
                            </div>
                        </div>

                        <!-- Security Card -->
                        <div class="settings-card security-card">
                            <div class="security-header">
                                <div class="security-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3 class="security-title">Security</h3>
                            </div>
                            <p class="security-description">
                                Keep your account secure by regularly updating your password
                            </p>
                            <button type="button" class="btn-change-password" id="btnOpenPasswordModal">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </div>

                    <div class="right-column">
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
                                               value="<?php echo htmlspecialchars($first_name); ?>" 
                                               placeholder="Enter first name" required>
                                        <div class="form-error" id="firstNameError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Middle Name</label>
                                        <input type="text" class="form-input" id="middleName" name="middle_name" 
                                               value="<?php echo htmlspecialchars($middle_name); ?>"
                                               placeholder="Enter middle name (optional)">
                                        <div class="form-error" id="middleNameError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required">Last Name</label>
                                        <input type="text" class="form-input" id="lastName" name="last_name" 
                                               value="<?php echo htmlspecialchars($last_name); ?>" 
                                               placeholder="Enter last name" required>
                                        <div class="form-error" id="lastNameError"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Student Number</label>
                                    <input type="text" class="form-input" id="studentNumber" name="student_number" 
                                           value="<?php echo htmlspecialchars($student_info['student_number'] ?? 'N/A'); ?>" 
                                           disabled>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label required">Course/Program</label>
                                        <input type="text" class="form-input" id="program" name="program" 
                                               value="<?php echo htmlspecialchars($student_info['program'] ?? ''); ?>" 
                                               placeholder="e.g., BS Computer Science" required>
                                        <div class="form-error" id="programError"></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required">Year & Section</label>
                                        <input type="text" class="form-input" id="yearSection" name="year_section" 
                                               value="<?php echo htmlspecialchars($student_info['year_section'] ?? ''); ?>" 
                                               placeholder="e.g., 3-A" required>
                                        <div class="form-error" id="yearSectionError"></div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label required">Contact Number</label>
                                        <input type="tel" class="form-input" id="contactNumber" name="contact_number" 
                                               value="<?php echo htmlspecialchars($student_info['contact_number'] ?? ''); ?>" 
                                               placeholder="+63 912 345 6789" required>
                                        <div class="form-error" id="contactNumberError"></div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label required">Email Address</label>
                                        <input type="email" class="form-input" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($student_info['email']); ?>" 
                                               placeholder="your.email@example.com" required>
                                        <div class="form-error" id="emailError"></div>
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
                               placeholder="Enter current password" autocomplete="current-password" required>
                        <div class="form-error" id="currentPasswordError"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">New Password</label>
                        <input type="password" class="form-input" id="newPassword" name="new_password" 
                               placeholder="Enter new password (min. 8 characters)" autocomplete="new-password" required>
                        <div class="form-error" id="newPasswordError"></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Confirm New Password</label>
                        <input type="password" class="form-input" id="confirmPassword" name="confirm_password" 
                               placeholder="Re-enter new password" autocomplete="new-password" required>
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
        console.log('=== Account Settings Debug ===');
        console.log('First Name:', '<?php echo addslashes($first_name); ?>');
        console.log('Middle Name:', '<?php echo addslashes($middle_name); ?>');
        console.log('Last Name:', '<?php echo addslashes($last_name); ?>');
        console.log('Full Name:', '<?php echo addslashes($student_info['full_name']); ?>');
    </script>
    <script src="<?php echo JS_PATH; ?>main.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo JS_PATH; ?>student-pages/account-settings.js?v=<?php echo time(); ?>"></script>
</body>
</html>