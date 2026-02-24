<?php
// modules/auth/login.php
session_start();

require_once '../../config/database.php';
require_once '../../config/config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../../public/dashboard.php');
    exit();
}

// Try auto-login via "Remember Me" cookie
if (!empty($_COOKIE['remember_me']) && validateRememberToken()) {
    header('Location: ../../public/dashboard.php');
    exit();
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NIA UPRIIS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/assets/css/login.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="login-wrapper">
        <!-- Left Panel - Branding -->
        <div class="login-panel-left">
            <div class="branding">
                <img src="../../public/assets/images/nia-upriis-logo.jpg" alt="NIA Logo" class="logo">
                <h1>NIA UPRIIS</h1>
                <p class="tagline">ICT Equipment Inventory and Preventive Maintenance System</p>
                
            </div>
            <div class="decorative-pattern"></div>
        </div>
        
        <!-- Right Panel - Login Form -->
        <div class="login-panel-right">
            <div class="login-container">
                <div class="login-header">
                    <h2>Welcome Back</h2>
                    <p>Please sign in to your account</p>
                </div>
                
                <!-- Alert Container -->
                <div id="alertContainer"></div>
                
                <form id="loginForm" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            autocomplete="email"
                            placeholder="Enter your email"
                            aria-describedby="emailError"
                        >
                        <span class="error-message" id="emailError" role="alert"></span>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                aria-describedby="passwordError"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        <span class="error-message" id="passwordError" role="alert"></span>
                    </div>
                    
                    <!-- Remember Me & Forgot Password -->
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" value="1" id="rememberMe">
                            <span class="checkmark"></span>
                            <span class="checkbox-text">Remember me</span>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-login" id="submitBtn">
                        <span id="btnText">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In
                        </span>
                        <span id="btnLoader" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                            Signing in...
                        </span>
                    </button>
                </form>
                
                <!-- Footer Info -->
                <div class="login-footer">
                    <p class="help-text">
                        <i class="fas fa-info-circle"></i>
                        Having trouble logging in? Contact your system administrator.
                    </p>
                    <p class="copyright">
                        © <?php echo date('Y'); ?> NIA UPRIIS. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../../public/assets/js/login.js?v=<?php echo time(); ?>"></script>
</body>
</html>