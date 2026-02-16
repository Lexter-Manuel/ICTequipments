/**
 * Login Page JavaScript
 * NIA UPRIIS ICT Inventory System
 */

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    // Real-time validation
    emailInput.addEventListener('blur', validateEmail);
    passwordInput.addEventListener('blur', validatePassword);
    
    // Form submission
    loginForm.addEventListener('submit', handleSubmit);
});

/**
 * Validate email field
 */
function validateEmail() {
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    const email = emailInput.value.trim();
    
    if (!email) {
        showFieldError(emailInput, emailError, 'Email is required');
        return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showFieldError(emailInput, emailError, 'Please enter a valid email address');
        return false;
    }
    
    clearFieldError(emailInput, emailError);
    return true;
}

/**
 * Validate password field
 */
function validatePassword() {
    const passwordInput = document.getElementById('password');
    const passwordError = document.getElementById('passwordError');
    const password = passwordInput.value;
    
    if (!password) {
        showFieldError(passwordInput, passwordError, 'Password is required');
        return false;
    }
    
    if (password.length < 8) {
        showFieldError(passwordInput, passwordError, 'Password must be at least 8 characters');
        return false;
    }
    
    clearFieldError(passwordInput, passwordError);
    return true;
}

/**
 * Show field error
 */
function showFieldError(input, errorElement, message) {
    input.classList.add('error');
    errorElement.textContent = message;
}

/**
 * Clear field error
 */
function clearFieldError(input, errorElement) {
    input.classList.remove('error');
    errorElement.textContent = '';
}

/**
 * Handle form submission
 */
async function handleSubmit(e) {
    e.preventDefault();
    
    // Validate all fields
    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();
    
    if (!isEmailValid || !isPasswordValid) {
        return;
    }
    
    // Get form elements
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');
    
    // Disable button and show loading
    submitBtn.disabled = true;
    btnText.style.display = 'none';
    btnLoader.style.display = 'inline-flex';
    
    try {
        const formData = new FormData(document.getElementById('loginForm'));
        
        const response = await fetch('process-login.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Login successful! Redirecting...', 'success');
            
            // Small delay before redirect for better UX
            setTimeout(() => {
                window.location.href = result.redirect || '../../public/dashboard.php';
            }, 1000);
        } else {
            showAlert(result.message || 'Login failed. Please try again.', 'error');
            
            // Re-enable button
            submitBtn.disabled = false;
            btnText.style.display = 'inline-flex';
            btnLoader.style.display = 'none';
            
            // Clear password field on error
            document.getElementById('password').value = '';
            document.getElementById('password').focus();
        }
    } catch (error) {
        console.error('Login error:', error);
        showAlert('Connection error. Please check your internet and try again.', 'error');
        
        // Re-enable button
        submitBtn.disabled = false;
        btnText.style.display = 'inline-flex';
        btnLoader.style.display = 'none';
    }
}

/**
 * Toggle password visibility
 */
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

/**
 * Show alert message
 */
function showAlert(message, type = 'error') {
    const container = document.getElementById('alertContainer');
    
    // Clear existing alerts
    container.innerHTML = '';
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    alert.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(alert);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alert.style.animation = 'slideUp 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

// Prevent multiple form submissions
let isSubmitting = false;
document.getElementById('loginForm').addEventListener('submit', function(e) {
    if (isSubmitting) {
        e.preventDefault();
        return false;
    }
    isSubmitting = true;
});