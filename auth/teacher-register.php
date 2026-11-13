<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirectToDashboard();
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Teacher Registration | PSU</title>

<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
  href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
  rel="stylesheet"
/>

<link rel="stylesheet" href="../assets/css/register.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../assets/css/validation.css?v=<?php echo time(); ?>">

<style>
.password-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-wrapper input {
  padding-right: 45px;
  width: 100%;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  transition: color 0.2s ease;
  z-index: 10;
}

.password-toggle:hover {
  color: #374151;
}

.password-toggle svg {
  width: 20px;
  height: 20px;
  stroke: currentColor;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  fill: none;
}
</style>

</head>
<body>

  <div class="header">
    <div class="headerLeft">
      <img src="../assets/images/psu-logo.png" alt="University Logo" class="logo">
    </div>
    <div class="headerCenter">
      <h1>indEx</h1>
    </div>
    <div class="headerRight"></div>
  </div>

  <div class="signup-container">
    <div class="form-section">
      <h1>Teacher Registration</h1>
      <p>Create your teacher account to get started.</p>

      <div id="toast" style="display: none; position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 8px; color: white; font-weight: 500; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.3); animation: slideIn 0.3s ease;"></div>

      <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 8px; border: 1px solid;">
          <?php echo htmlspecialchars($flash['message']); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="../api/auth/register-handler.php" id="registerForm" novalidate>
        <input type="hidden" name="role" value="teacher">

        <!-- Invitation Code -->
        <div class="input-group">
          <label for="invitation_code">🎟️ Teacher Invitation Code</label>
          <input 
            type="text" 
            id="invitation_code" 
            name="invitation_code" 
            placeholder="Enter invitation code" 
            required
          />
          <div class="validation-message" id="invitation_code_error"></div>
          <small style="display: block; margin-top: 0.5rem; color: var(--gray-medium); font-size: 0.875rem;">
            Contact the admin to get your teacher code.
          </small>
        </div>

        <!-- Name Fields -->
        <div class="name-row">
          <div class="input-group">
            <label for="first_name">👤 First Name</label>
            <input 
              type="text" 
              id="first_name" 
              name="first_name" 
              placeholder="First Name" 
              required
            />
            <div class="validation-message" id="first_name_error"></div>
          </div>

          <div class="input-group">
            <label for="middle_name">Middle Name</label>
            <input 
              type="text" 
              id="middle_name" 
              name="middle_name" 
              placeholder="Middle Name"
              required
            />
            <div class="validation-message" id="middle_name_error"></div>
          </div>

          <div class="input-group">
            <label for="last_name">Last Name</label>
            <input 
              type="text" 
              id="last_name" 
              name="last_name" 
              placeholder="Last Name" 
              required
            />
            <div class="validation-message" id="last_name_error"></div>
          </div>
        </div>

        <!-- Email -->
        <div class="input-group">
          <label for="email">📧 Email</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            placeholder="Email" 
            required
          />
          <div class="validation-message" id="email_error"></div>
        </div>

        <!-- Contact Number -->
        <div class="input-group">
          <label for="contact_number">📞 Contact Number</label>
          <input 
            type="text" 
            id="contact_number" 
            name="contact_number" 
            placeholder="09XXXXXXXXX"
            maxlength="11"
            required
          />
          <div class="char-counter" id="contact_number_counter">0/11 digits</div>
          <div class="validation-message" id="contact_number_error"></div>
        </div>

        <!-- Password Fields -->
        <div class="input-group">
          <label for="password">🔒 Password</label>
          <div class="password-wrapper">
            <input 
              type="password" 
              id="password" 
              name="password" 
              placeholder="Password (min. 8 characters)" 
              required
              minlength="8"
            />
            <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
              <svg id="eyeIcon" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <svg id="eyeOffIcon" viewBox="0 0 24 24" style="display: none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </button>
          </div>
          <div class="password-strength" id="password_strength">
            <span></span>
            <span></span>
            <span></span>
          </div>
          <div class="password-strength-text" id="password_strength_text"></div>
          <div class="validation-message" id="password_error"></div>
        </div>

        <div class="input-group">
          <label for="confirm_password">🔒 Confirm Password</label>
          <div class="password-wrapper">
            <input 
              type="password" 
              id="confirm_password" 
              name="confirm_password" 
              placeholder="Confirm your Password" 
              required
              minlength="8"
            />
            <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Toggle confirm password visibility">
              <svg id="eyeIconConfirm" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <svg id="eyeOffIconConfirm" viewBox="0 0 24 24" style="display: none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
            </button>
          </div>
          <div class="validation-message" id="confirm_password_error"></div>
        </div>

        <!-- Submit Buttons -->
        <div class="btn-row">
          <button type="button" class="back-btn" onclick="window.location.href='login.php'">Back</button>
          <button type="submit" class="next-btn" id="submitBtn" disabled>Confirm</button>
        </div>

      </form>
    </div>

    <img src="../assets/images/signUp.png" alt="Sign Up Illustration" class="side-img">
  </div>

  <div class="footer">
    <p>&copy; 2025 Pampanga State University. All rights reserved.</p>
    <a href="#">FAQs</a>
  </div>

  <script src="../assets/js/teacher-register-validation.js?v=<?php echo time(); ?>"></script>
  <script>
    // Password visibility toggle
    document.getElementById('togglePassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      const eyeOffIcon = document.getElementById('eyeOffIcon');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
      } else {
        passwordInput.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
      }
    });

    // Confirm password visibility toggle
    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
      const confirmPasswordInput = document.getElementById('confirm_password');
      const eyeIconConfirm = document.getElementById('eyeIconConfirm');
      const eyeOffIconConfirm = document.getElementById('eyeOffIconConfirm');
      
      if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        eyeIconConfirm.style.display = 'none';
        eyeOffIconConfirm.style.display = 'block';
      } else {
        confirmPasswordInput.type = 'password';
        eyeIconConfirm.style.display = 'block';
        eyeOffIconConfirm.style.display = 'none';
      }
    });
  </script>
</body>
</html>