(function() {
  'use strict';

  const validationState = {
    invitation_code: false,
    first_name: false,
    middle_name: false, 
    last_name: false,
    email: false,
    contact_number: false, 
    password: false,
    confirm_password: false
  };

  const form = document.getElementById('registerForm');
  const submitBtn = document.getElementById('submitBtn');

  function validateInvitationCode(input) {
    const value = input.value.trim();
    const errorElement = document.getElementById('invitation_code_error');

    input.classList.remove('valid', 'invalid');

    if (value === '') {
      errorElement.textContent = 'Invitation code is required';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    if (value.length < 6) {
      errorElement.textContent = 'Invitation code must be at least 6 characters';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    errorElement.textContent = '✓ Valid invitation code format';
    errorElement.className = 'validation-message success';
    input.classList.add('valid');
    return true;
  }

  function validateName(input, errorElementId, fieldName, isRequired = true) {
    const value = input.value.trim();
    const errorElement = document.getElementById(errorElementId);
    const nameRegex = /^[a-zA-Z\s\.\-']+$/;

    input.classList.remove('valid', 'invalid');

    if (value === '' && isRequired) {
      errorElement.textContent = `${fieldName} is required`;
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    if (value === '' && !isRequired) {
      errorElement.style.display = 'none';
      return true;
    }

    if (value !== '' && !nameRegex.test(value)) {
      errorElement.textContent = `${fieldName} must contain letters only`;
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    if (value !== '' && value.length < 2) {
      errorElement.textContent = `${fieldName} must be at least 2 characters`;
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    errorElement.textContent = `✓ Valid ${fieldName.toLowerCase()}`;
    errorElement.className = 'validation-message success';
    input.classList.add('valid');
    return true;
  }

  function validateEmail(input) {
    const value = input.value.trim();
    const errorElement = document.getElementById('email_error');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    input.classList.remove('valid', 'invalid');

    if (value === '') {
      errorElement.textContent = 'Email is required';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    if (!emailRegex.test(value)) {
      errorElement.textContent = 'Please enter a valid email address';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    errorElement.textContent = '✓ Valid email address';
    errorElement.className = 'validation-message success';
    input.classList.add('valid');
    return true;
  }

  function validateContactNumber(input) {
    const value = input.value.trim();
    const errorElement = document.getElementById('contact_number_error');
    const counterElement = document.getElementById('contact_number_counter');
    const digitRegex = /^\d+$/;
    const philippineRegex = /^09\d{9}$/;

    input.classList.remove('valid', 'invalid');
    counterElement.textContent = `${value.length}/11 digits`;

    if (value !== '' && !digitRegex.test(value)) {
      input.value = value.replace(/\D/g, '');
      errorElement.textContent = 'Contact number must contain numbers only';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    // Changed: Now required, not optional
    if (value === '') {
      errorElement.textContent = 'Contact number is required';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    if (value.length < 11) {
      errorElement.textContent = `Must be 11 digits (${11 - value.length} more needed)`;
      errorElement.className = 'validation-message warning';
      input.classList.add('invalid');
      return false;
    }

    if (!philippineRegex.test(value)) {
      errorElement.textContent = 'Must start with 09 (Philippine format)';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    errorElement.textContent = '✓ Valid contact number';
    errorElement.className = 'validation-message success';
    input.classList.add('valid');
    return true;
  }

  function validatePasswordStrength(input) {
    const value = input.value;
    const errorElement = document.getElementById('password_error');
    const strengthBar = document.getElementById('password_strength');
    const strengthText = document.getElementById('password_strength_text');

    input.classList.remove('valid', 'invalid');

    if (value === '') {
      errorElement.textContent = 'Password is required';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      strengthBar.className = 'password-strength';
      strengthText.textContent = '';
      return false;
    }

    if (value.length < 8) {
      errorElement.textContent = `Must be at least 8 characters (${8 - value.length} more needed)`;
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      strengthBar.className = 'password-strength';
      strengthText.textContent = '';
      return false;
    }

    let strength = 0;
    if (/[a-z]/.test(value)) strength++;
    if (/[A-Z]/.test(value)) strength++;
    if (/\d/.test(value)) strength++;
    if (/[^a-zA-Z0-9]/.test(value)) strength++;
    if (value.length >= 12) strength++;

    let strengthLevel, strengthClass;
    if (strength <= 2) {
      strengthLevel = 'Weak';
      strengthClass = 'weak';
    } else if (strength === 3) {
      strengthLevel = 'Medium';
      strengthClass = 'medium';
    } else {
      strengthLevel = 'Strong';
      strengthClass = 'strong';
    }

    strengthBar.className = `password-strength ${strengthClass}`;
    strengthText.textContent = `Password strength: ${strengthLevel}`;
    strengthText.className = `password-strength-text ${strengthClass}`;

    if (strengthLevel === 'Weak') {
      errorElement.textContent = 'Tip: Add uppercase, numbers, or symbols';
      errorElement.className = 'validation-message warning';
      input.classList.add('invalid');
      return false;
    }

    errorElement.style.display = 'none';
    input.classList.add('valid');
    return true;
  }

  function validateConfirmPassword(input) {
    const value = input.value;
    const password = document.getElementById('password').value;
    const errorElement = document.getElementById('confirm_password_error');

    input.classList.remove('valid', 'invalid');

    if (value === '') {
      errorElement.textContent = 'Please confirm your password';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    if (value !== password) {
      errorElement.textContent = 'Passwords do not match';
      errorElement.className = 'validation-message error';
      input.classList.add('invalid');
      return false;
    }

    errorElement.textContent = '✓ Passwords match';
    errorElement.className = 'validation-message success';
    input.classList.add('valid');
    return true;
  }

  function updateSubmitButton() {
    const allValid = Object.values(validationState).every(v => v === true);
    submitBtn.disabled = !allValid;
    
    if (allValid) {
      submitBtn.style.opacity = '1';
      submitBtn.style.cursor = 'pointer';
    } else {
      submitBtn.style.opacity = '0.6';
      submitBtn.style.cursor = 'not-allowed';
    }
  }

  function showToast(message, type) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    
    toast.textContent = message;
    toast.style.display = 'block';
    
    if (type === 'success') {
      toast.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    } else {
      toast.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
    }

    setTimeout(() => {
      toast.style.transition = 'opacity 0.3s ease';
      toast.style.opacity = '0';
      setTimeout(() => {
        toast.style.display = 'none';
        toast.style.opacity = '1';
        toast.style.transition = '';
      }, 300);
    }, 3000);
  }

  function initializeValidation() {
    document.getElementById('invitation_code').addEventListener('input', function() {
      validationState.invitation_code = validateInvitationCode(this);
      updateSubmitButton();
    });

    document.getElementById('first_name').addEventListener('input', function() {
      this.value = this.value.replace(/[^a-zA-Z\s\.\-']/g, '');
      validationState.first_name = validateName(this, 'first_name_error', 'First name', true);
      updateSubmitButton();
    });

    document.getElementById('middle_name').addEventListener('input', function() {
      this.value = this.value.replace(/[^a-zA-Z\s\.\-']/g, '');
      validationState.middle_name = validateName(this, 'middle_name_error', 'Middle name', true); // Changed to true (required)
      updateSubmitButton();
    });

    document.getElementById('last_name').addEventListener('input', function() {
      this.value = this.value.replace(/[^a-zA-Z\s\.\-']/g, '');
      validationState.last_name = validateName(this, 'last_name_error', 'Last name', true);
      updateSubmitButton();
    });

    document.getElementById('email').addEventListener('input', function() {
      validationState.email = validateEmail(this);
      updateSubmitButton();
    });

    document.getElementById('contact_number').addEventListener('input', function() {
      validationState.contact_number = validateContactNumber(this);
      updateSubmitButton();
    });

    document.getElementById('password').addEventListener('input', function() {
      validationState.password = validatePasswordStrength(this);
      
      const confirmPassword = document.getElementById('confirm_password');
      if (confirmPassword.value !== '') {
        validationState.confirm_password = validateConfirmPassword(confirmPassword);
      }
      
      updateSubmitButton();
    });

    document.getElementById('confirm_password').addEventListener('input', function() {
      validationState.confirm_password = validateConfirmPassword(this);
      updateSubmitButton();
    });

    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const finalValidation = {
        invitation_code: validateInvitationCode(document.getElementById('invitation_code')),
        first_name: validateName(document.getElementById('first_name'), 'first_name_error', 'First name', true),
        middle_name: validateName(document.getElementById('middle_name'), 'middle_name_error', 'Middle name', true), // Changed to true (required)
        last_name: validateName(document.getElementById('last_name'), 'last_name_error', 'Last name', true),
        email: validateEmail(document.getElementById('email')),
        contact_number: validateContactNumber(document.getElementById('contact_number')),
        password: validatePasswordStrength(document.getElementById('password')),
        confirm_password: validateConfirmPassword(document.getElementById('confirm_password'))
      };

      const allValid = Object.values(finalValidation).every(v => v === true);

      if (!allValid) {
        showToast('Please fix all validation errors before submitting', 'error');
        
        const firstError = document.querySelector('input.invalid, select.invalid');
        if (firstError) {
          firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstError.focus();
        }
        
        return false;
      }

      submitBtn.textContent = 'Creating Account...';
      submitBtn.disabled = true;
      
      this.submit();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      initializeValidation();
      updateSubmitButton();
    });
  } else {
    initializeValidation();
    updateSubmitButton();
  }

})();