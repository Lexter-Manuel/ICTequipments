<?php
session_start();

require_once '../config/database.php';
require_once '../config/session-check.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NIA UPRIIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">

    <!-- Custom CSS -->
    <script>
        // Prevent FOUC: apply saved theme before any CSS paints
        (function() {
            var t = localStorage.getItem('nia-theme');
            if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>
    <link rel="stylesheet" href="assets/css/root.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/tabs.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/dashboard.css?v=<?php echo time()?>">
    <link rel="stylesheet" href="assets/css/other_equipment.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/roster.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/dark-mode.css?v=<?php echo time(); ?>">
</head>
<body>
    <input type="checkbox" id="sidebar-mobile-input" class="sidebar-mobile-input">
    <label for="sidebar-mobile-input" class="sidebar-mobile-backdrop" aria-hidden="true"></label>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Toggler stays inside sidebar so it moves with it -->
        <label for="sidebar-mobile-input" class="sidebar-mobile-toggler" aria-label="Toggle navigation">
            <span class="sidebar-mobile-burger"></span>
        </label>
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo">
                    <img src="assets/images/nia-upriis-logo.jpg" alt="NIA Logo">
                </div>
                <div class="logo-text">
                    <h1>NIA UPRIIS</h1>
                    <p>ICT Inventory</p>
                </div>
            </div>
        </div>
        
        <nav class="nav-menu">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="#" class="nav-item active" data-page="home">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item" data-page="roster">
                    <i class="fas fa-users"></i>
                    <span>Employee Roster</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Inventory</div>
                <a href="#" class="nav-item" data-page="equipment">
                    <i class="fas fa-boxes-stacked"></i>
                    <span>Equipment</span>
                </a>
                <a href="#" class="nav-item" data-page="software">
                    <i class="fas fa-key"></i>
                    <span>Software Licenses</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Maintenance</div>
                <a href="#" class="nav-item" data-page="maintenance-schedule">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Maintenance Schedule</span>
                </a>
                <a href="#" class="nav-item" data-page="maintenance-templates">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Maintenance Templates</span>
                </a>
                <a href="#" class="nav-item" data-page="perform-maintenance">
                    <i class="fas fa-tools"></i>
                    <span>Perform Maintenance</span>
                </a>
                <a href="#" class="nav-item" data-page="maintenance-history">
                    <i class="fas fa-history"></i>
                    <span>Maintenance History</span>
                </a>
            </div>
            <?php if ($_SESSION['role'] === 'Super Admin'): ?>
            <div class="nav-section">
                <div class="nav-section-title">Reports</div>
                <a href="#" class="nav-item" data-page="equipment-summary">
                    <i class="fas fa-boxes"></i>
                    <span>Equipment Summary</span>
                </a>
                <a href="#" class="nav-item" data-page="maintenance-summary">
                    <i class="fas fa-tools"></i>
                    <span>Maintenance Summary</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Settings</div>
                <a href="#" class="nav-item" data-page="organization">
                    <i class="fas fa-sitemap"></i>
                    <span>Organization</span>
                </a>
                <a href="#" class="nav-item" data-page="employees">
                    <i class="fas fa-users"></i>
                    <span>Employees</span>
                </a>
                <a href="#" class="nav-item" data-page="accounts">
                    <i class="fas fa-user-shield"></i>
                    <span>Accounts</span>
                </a>
            </div>
            <?php endif; ?>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-top">
                <div class="header-title">
                    <h1 class="header-title">Inventory of ICT Equipment and Preventive Maintenance Scheduling</h1>
                    <p class="header-subtitle">NIA UPRIIS</p>
                </div>
                <div class="user-profile-dropdown" id="userProfileDropdown">
                    <button class="profile-toggle" id="profileToggle" aria-haspopup="true" aria-expanded="false">
                        <span class="avatar-initials"><?php
                            $parts = explode(' ', trim($_SESSION['user_name']));
                            echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                        ?></span>
                        <span class="username-text"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <i class="fas fa-chevron-down chevron-icon"></i>
                    </button>
                    <div class="profile-dropdown-menu" id="profileDropdownMenu" role="menu">
                        <!-- User Card -->
                        <div class="dropdown-user-card">
                            <div class="avatar-lg"><?php
                                echo strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                            ?></div>
                            <div class="dropdown-user-info">
                                <p class="dropdown-user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                                <p class="dropdown-user-email"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                                <span class="role-pill"><i class="fas fa-circle"></i> <?php echo htmlspecialchars($_SESSION['role']); ?></span>
                            </div>
                        </div>
                        <!-- Menu Items -->
                        <div class="dropdown-menu-section">
                            <a href="#" class="dropdown-menu-item" data-page="profile" role="menuitem">
                                <i class="fas fa-user-circle"></i>
                                <span class="item-label">My Profile</span>
                            </a>
                            <a href="#" class="dropdown-menu-item" id="dropdownThemeToggle" role="menuitem">
                                <i class="fas fa-moon" id="dropdownThemeIcon"></i>
                                <span class="item-label" id="dropdownThemeLabel">Dark Mode</span>
                            </a>
                             <?php if ($_SESSION['role'] === 'Super Admin'): ?>
                            <a href="#" class="dropdown-menu-item" data-page="accounts" role="menuitem">
                                <i class="fas fa-user-shield"></i>
                                <span class="item-label">Account Settings</span>
                            </a>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-divider"></div>
                        <?php if ($_SESSION['role'] === 'Super Admin'): ?>
                        <div class="dropdown-menu-section">
                            <a href="#" class="dropdown-menu-item" data-page="audit-trail" role="menuitem">
                                <i class="fas fa-list-ul"></i>
                                <span class="item-label">Activity Logs</span>
                            </a>
                            <a href="#" class="dropdown-menu-item" data-page="settings" role="menuitem">
                                <i class="fas fa-cog"></i>
                                <span class="item-label">System Settings</span>
                            </a>
                        </div>
                        <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <div class="dropdown-menu-section">
                            <a href="../modules/auth/logout.php" class="dropdown-menu-item item-danger" role="menuitem">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="item-label">Log Out</span>
                            </a>
                        </div>
                        <!-- Session Footer -->
                        <div class="dropdown-session-footer">
                            <i class="fas fa-circle"></i>
                            <span>Logged in since <?php
                                echo isset($_SESSION['logged_in_at'])
                                    ? date('M j, g:i A', $_SESSION['logged_in_at'])
                                    : 'this session';
                            ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-bottom">
                <div class="breadcrumb" id="breadcrumb">
                    <a href="#" data-page="home"><i class="fas fa-home"></i></a>
                    <span class="current">Dashboard</span>
                </div>
                <div class="header-actions">
                    <!-- <button class="theme-toggle-btn" id="themeToggle" title="Toggle dark mode" aria-label="Toggle dark mode">
                        <i class="fas fa-moon theme-icon-dark"></i>
                        <i class="fas fa-sun theme-icon-light"></i>
                    </button> -->
                    <div class="datetime-display">
                        <div class="date-info">
                            <i class="fas fa-calendar-alt"></i>
                            <span id="currentDate"></span>
                        </div>
                        <div class="time-info">
                            <i class="fas fa-clock"></i>
                            <span id="currentTime"></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Content Area (Dynamic Loading) -->
        <div class="content" id="contentArea">
            <!-- Loading Spinner -->
            <div class="loading-spinner" id="loadingSpinner" style="display: none;">
                <div class="spinner"></div>
                <p>Loading...</p>
            </div>
            
            <!-- Content will be loaded here -->
        </div>
    </main>
    
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle" id="mobileToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <script>
        var BASE_URL = window.location.origin + "/ictequipment/";
        var CURRENT_USER = {
            id:   <?php echo json_encode($_SESSION['user_id'] ?? 0); ?>,
            name: <?php echo json_encode($_SESSION['user_name'] ?? ''); ?>,
            role: <?php echo json_encode($_SESSION['role'] ?? ''); ?>
        };
    </script>
    <script src="assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../vendor/node_modules/cropperjs/dist/cropper.min.js"></script>
    <script src="assets/js/utils.js?v=<?php echo time()?>"></script>
    <script src="assets/js/dashboard.js?v=<?php echo time()?>"></script>
    <script src="assets/js/maintenance-conductor.js?v=<?php echo time(); ?>"></script>

    <!-- Dark mode toggle -->
    <script>
        (function () {
            var btn   = document.getElementById('themeToggle');
            var ddBtn = document.getElementById('dropdownThemeToggle');
            var ddIcon  = document.getElementById('dropdownThemeIcon');
            var ddLabel = document.getElementById('dropdownThemeLabel');
            var KEY  = 'nia-theme';

            function applyTheme(theme) {
                if (theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                }
                updateDropdownUI(theme);
            }

            function updateDropdownUI(theme) {
                if (!ddIcon || !ddLabel) return;
                if (theme === 'dark') {
                    ddIcon.className = 'fas fa-sun';
                    ddLabel.textContent = 'Light Mode';
                } else {
                    ddIcon.className = 'fas fa-moon';
                    ddLabel.textContent = 'Dark Mode';
                }
            }

            function toggle() {
                var cur = document.documentElement.getAttribute('data-theme');
                var next = cur === 'dark' ? 'light' : 'dark';
                applyTheme(next);
                localStorage.setItem(KEY, next);
            }

            // Apply saved theme on load
            var saved = localStorage.getItem(KEY) || 'light';
            applyTheme(saved);

            if (btn) btn.addEventListener('click', toggle);
            if (ddBtn) ddBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggle();
            });
        })();
    </script>

    <!-- Mobile sidebar: close on nav click and on content load -->
    <script>
        (function () {
            var sidebarInput = document.getElementById('sidebar-mobile-input');
            if (!sidebarInput) return;

            function closeSidebar() {
                sidebarInput.checked = false;
            }

            // Close when any nav item is clicked
            document.querySelectorAll('.nav-item').forEach(function (item) {
                item.addEventListener('click', closeSidebar);
            });

            // Close when new content is loaded into #contentArea
            var contentArea = document.getElementById('contentArea');
            if (contentArea) {
                new MutationObserver(function (mutations) {
                    mutations.forEach(function (m) {
                        if (m.addedNodes.length) closeSidebar();
                    });
                }).observe(contentArea, { childList: true });
            }
        })();
    </script>
</body>
</html>