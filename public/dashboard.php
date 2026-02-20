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
    <link rel="stylesheet" href="assets/css/root.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/dashboard.css?v=<?php echo time()?>">
    <link rel="stylesheet" href="assets/css/other_equipment.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/roster.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
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
                    <span>Emplopyee Roster</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Inventory</div>
                <a href="#" class="nav-item" data-page="employees">
                    <i class="fas fa-users"></i>
                    <span>Employees</span>
                </a>
                <a href="#" class="nav-item" data-page="computer">
                    <i class="fas fa-desktop"></i>
                    <span>Computer</span>
                </a>
                <a href="#" class="nav-item" data-page="printer">
                    <i class="fas fa-print"></i>
                    <span>Printers</span>
                </a>
                <a href="#" class="nav-item" data-page="software">
                    <i class="fas fa-key"></i>
                    <span>Software Licenses</span>
                </a>
                <a href="#" class="nav-item" data-page="otherequipment">
                    <i class="fas fa-server"></i>
                    <span>Other ICT Equipment</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Organization</div>
                <a href="#" class="nav-item" data-page="divisions">
                    <i class="fas fa-building"></i>
                    <span>Divisions</span>
                </a>
                <a href="#" class="nav-item" data-page="sections">
                    <i class="fas fa-sitemap"></i>
                    <span>Sections</span>
                </a>
                <a href="#" class="nav-item" data-page="units">
                    <i class="fas fa-th-large"></i>
                    <span>Units</span>
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
            
            <div class="nav-section">
                <div class="nav-section-title">Reports</div>
                <a href="#" class="nav-item" data-page="inventory-report">
                    <i class="fas fa-file-alt"></i>
                    <span>Inventory Report</span>
                </a>
                <a href="#" class="nav-item" data-page="analytics">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics</span>
                </a>
            </div>
            
            <div class="nav-section">
                <div class="nav-section-title">Settings</div>
                <a href="#" class="nav-item" data-page="accounts">
                    <i class="fas fa-user-shield"></i>
                    <span>Accounts</span>
                </a>
                <a href="#" class="nav-item" data-page="settings">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
                </a>
            </div>
            
            <div class="nav-section">
                <a href="../modules/auth/logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-top">
                <h1 class="header-title">Inventory of ICT Equipment and Preventive Maintenance Scheduling</h1>
                <p class="header-subtitle">NIA UPRIIS</p>
            </div>
            <div class="header-bottom">
                <div class="breadcrumb" id="breadcrumb">
                    <a href="#" data-page="home"><i class="fas fa-home"></i></a>
                    <span class="current">Dashboard</span>
                </div>
                <div class="header-actions">
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
    <script src="../node_modules/cropperjs/dist/cropper.min.js"></script>
    <script src="assets/js/dashboard.js?v=<?php echo time()?>"></script>
    <script src="assets/js/maintenance-conductor.js?v=<?php echo time(); ?>"></script>
</body>
</html>