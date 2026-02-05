<?php
session_start();

require_once '../config/database.php';
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NIA UPRIIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
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
                <a href="#" class="nav-item" data-page="schedule">
                    <i class="fas fa-calendar-check"></i>
                    <span>Schedule</span>
                </a>
                <a href="#" class="nav-item" data-page="history">
                    <i class="fas fa-history"></i>
                    <span>History</span>
                </a>
                <a href="#" class="nav-item" data-page="notifications">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
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
                    <span>/</span>
                    <span class="current">Dashboard</span>
                </div>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalSearch" placeholder="Search equipment, employees...">
                    </div>
                    <div class="user-menu">
                        <div class="user-avatar"></div>
                        <div class="user-info">
                            <span class="user-name"></span>
                            <span class="user-role"></span>
                        </div>
                        <i class="fas fa-chevron-down" style="color: var(--text-light); font-size: 12px;"></i>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/bootstrap/bootstrap.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>