<?php
// modules/dashboard/home.php
require_once '../../config/database.php';

// Get statistics from database
try {
    $db = Database::getInstance()->getConnection();
    
    // Count employees
    $stmt = $db->query("SELECT COUNT(*) as count FROM tbl_employee WHERE 1");
    $employeeCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count system units
    $stmt = $db->query("SELECT COUNT(*) as count FROM tbl_systemunit WHERE 1");
    $systemUnitCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count software licenses
    $stmt = $db->query("SELECT COUNT(*) as count FROM tbl_software WHERE 1");
    $softwareCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count sections
    $stmt = $db->query("SELECT COUNT(*) as count FROM location WHERE location_type_id = 2 AND is_deleted = '0'");
    $sectionCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count divisions
    $stmt = $db->query("SELECT COUNT(*) as count FROM location WHERE location_type_id = 1 AND is_deleted = '0'");
    $divisionCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count monitors
    $stmt = $db->query("SELECT COUNT(*) as count FROM tbl_monitor WHERE 1");
    $monitorCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count printers
    $stmt = $db->query("SELECT COUNT(*) as count FROM tbl_printer WHERE 1");
    $printerCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count all-in-one PCs
    $stmt = $db->query("SELECT COUNT(*) as count FROM tbl_allinone WHERE 1");
    $allinoneCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $employeeCount = $systemUnitCount = $softwareCount = $sectionCount = 0;
    $divisionCount = $monitorCount = $printerCount = $allinoneCount = 0;
}
?>

<style>
/* Modern Dashboard Styles */
.welcome-banner {
    background: linear-gradient(135deg, var(--primary-green), var(--primary-dark));
    border-radius: var(--radius-2xl);
    padding: 2.5rem;
    margin-bottom: 2rem;
    color: white;
    box-shadow: var(--shadow-xl);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out;
}

.welcome-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    pointer-events: none;
}

.welcome-content {
    position: relative;
    z-index: 1;
}

.welcome-content h2 {
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    color: white;
}

.welcome-content p {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
}

/* Modern Stats Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
    animation: fadeInUp 0.8s ease-out;
}

.stat-card {
    background: white;
    border-radius: var(--radius-xl);
    padding: 1.75rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    transition: all var(--transition-base);
    position: relative;
    overflow: hidden;
}

.stat-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-green), var(--accent-green));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform var(--transition-slow);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
}

.stat-card:hover::after {
    transform: scaleX(1);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    background: linear-gradient(135deg, var(--primary-green), var(--primary-dark));
    color: white;
    box-shadow: var(--shadow-md);
}

.stat-trend {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 700;
    font-family: var(--font-mono);
}

.stat-trend.up {
    background: rgba(34, 197, 94, 0.1);
    color: var(--success-600);
}

.stat-trend.down {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger-600);
}

.stat-value {
    font-size: 2.25rem;
    font-weight: 800;
    color: var(--text-dark);
    line-height: 1;
    margin-bottom: 0.5rem;
    font-family: var(--font-mono);
}

.stat-label {
    color: var(--text-medium);
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Overview Cards */
.overview-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
    animation: fadeInUp 1s ease-out;
}

.overview-card {
    background: white;
    padding: 1.5rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md);
    transition: all var(--transition-base);
}

.overview-card:hover {
    box-shadow: var(--shadow-xl);
    transform: translateY(-2px);
}

.overview-card h3 {
    font-size: 1.25rem;
    color: var(--text-dark);
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.overview-card h3 i {
    color: var(--primary-green);
}

.overview-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: var(--bg-light);
    border-radius: var(--radius-md);
    margin-bottom: 0.75rem;
    transition: all var(--transition-base);
}

.overview-item:hover {
    background: var(--neutral-100);
    transform: translateX(4px);
}

.overview-item:last-child {
    margin-bottom: 0;
}

.overview-item span:first-child {
    color: var(--text-medium);
    font-size: 0.875rem;
    font-weight: 500;
}

.overview-item span:last-child {
    font-weight: 700;
    color: var(--text-dark);
    font-family: var(--font-mono);
}

.overview-item.highlight span:last-child {
    color: var(--accent-green);
}

.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
    color: var(--text-light);
}

.empty-state i {
    font-size: 3rem;
    color: var(--neutral-300);
    margin-bottom: 1rem;
    display: block;
}

.empty-state p {
    margin: 0;
    font-size: 0.875rem;
}

/* Responsive */
@media (max-width: 768px) {
    .welcome-content h2 {
        font-size: 1.5rem;
    }
    
    .dashboard-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .stat-value {
        font-size: 1.75rem;
    }
    
    .overview-section {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 1.25rem;
    }
}
</style>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-content">
        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Administrator'); ?>!</h2>
        <p>Here's an overview of your ICT inventory and maintenance schedule</p>
    </div>
</div>

<!-- Dashboard Stats -->
<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>12%</span>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($employeeCount); ?></div>
        <div class="stat-label">Total Employees</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-desktop"></i>
            </div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>8%</span>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($systemUnitCount); ?></div>
        <div class="stat-label">System Units</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-tv"></i>
            </div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>5%</span>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($monitorCount); ?></div>
        <div class="stat-label">Monitors</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-print"></i>
            </div>
            <div class="stat-trend down">
                <i class="fas fa-arrow-down"></i>
                <span>2%</span>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($printerCount); ?></div>
        <div class="stat-label">Printers</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-laptop"></i>
            </div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>3%</span>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($allinoneCount); ?></div>
        <div class="stat-label">All-in-One PCs</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-key"></i>
            </div>
            <div class="stat-trend down">
                <i class="fas fa-arrow-down"></i>
                <span>1%</span>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($softwareCount); ?></div>
        <div class="stat-label">Software Licenses</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-sitemap"></i>
            </div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>5%</span>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($sectionCount); ?></div>
        <div class="stat-label">Sections</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-trend up">
                <i class="fas fa-arrow-up"></i>
                <span>0%</span>
            </div>
        </div>
        <div class="stat-value"><?php echo number_format($divisionCount); ?></div>
        <div class="stat-label">Divisions</div>
    </div>
</div>

<!-- Recent Activity / Quick Stats -->
<div class="overview-section">
    <!-- Recent Equipment Additions -->
    <div class="overview-card">
        <h3>
            <i class="fas fa-clock"></i>
            Recent Activity
        </h3>
        <div class="empty-state">
            <i class="fas fa-info-circle"></i>
            <p>No recent activity to display</p>
        </div>
    </div>
    
    <!-- Equipment Status Overview -->
    <div class="overview-card">
        <h3>
            <i class="fas fa-chart-pie"></i>
            Equipment Overview
        </h3>
        <div class="overview-item">
            <span>Total Equipment</span>
            <span><?php echo number_format($systemUnitCount + $monitorCount + $printerCount + $allinoneCount); ?></span>
        </div>
        <div class="overview-item highlight">
            <span>Assigned</span>
            <span>
                <?php 
                $stmt = $db->query("SELECT COUNT(DISTINCT employeeId) as count FROM (
                    SELECT employeeId FROM tbl_systemunit WHERE employeeId IS NOT NULL
                    UNION ALL
                    SELECT employeeId FROM tbl_monitor WHERE employeeId IS NOT NULL
                    UNION ALL
                    SELECT employeeId FROM tbl_printer WHERE employeeId IS NOT NULL
                    UNION ALL
                    SELECT employeeId FROM tbl_allinone WHERE employeeId IS NOT NULL
                ) as assigned_equipment");
                $assignedCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo number_format($assignedCount);
                ?>
            </span>
        </div>
        <div class="overview-item">
            <span>Unassigned</span>
            <span>
                <?php 
                $totalEquipment = $systemUnitCount + $monitorCount + $printerCount + $allinoneCount;
                echo number_format(max(0, $totalEquipment - $assignedCount));
                ?>
            </span>
        </div>
    </div>
</div>