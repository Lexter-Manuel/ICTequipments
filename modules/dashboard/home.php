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
    $stmt = $db->query("SELECT COUNT(*) as count FROM tbl_section WHERE 1");
    $sectionCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count divisions
    $stmt = $db->query("SELECT COUNT(*) as count FROM tbl_division WHERE 1");
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
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-top: 32px;">
    <!-- Recent Equipment Additions -->
    <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 8px var(--shadow-soft);">
        <h3 style="font-family: 'Crimson Pro', serif; font-size: 20px; color: var(--text-dark); margin-bottom: 20px;">
            <i class="fas fa-clock" style="color: var(--primary-green); margin-right: 8px;"></i>
            Recent Activity
        </h3>
        <div style="color: var(--text-medium); font-size: 14px; padding: 40px 20px; text-align: center;">
            <i class="fas fa-info-circle" style="font-size: 32px; color: var(--text-light); margin-bottom: 12px; display: block;"></i>
            No recent activity to display
        </div>
    </div>
    
    <!-- Equipment Status Overview -->
    <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 2px 8px var(--shadow-soft);">
        <h3 style="font-family: 'Crimson Pro', serif; font-size: 20px; color: var(--text-dark); margin-bottom: 20px;">
            <i class="fas fa-chart-pie" style="color: var(--primary-green); margin-right: 8px;"></i>
            Equipment Overview
        </h3>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg-light); border-radius: 8px;">
                <span style="color: var(--text-medium); font-size: 14px;">Total Equipment</span>
                <span style="font-weight: 600; color: var(--text-dark);">
                    <?php echo number_format($systemUnitCount + $monitorCount + $printerCount + $allinoneCount); ?>
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg-light); border-radius: 8px;">
                <span style="color: var(--text-medium); font-size: 14px;">Assigned</span>
                <span style="font-weight: 600; color: var(--accent-green);">
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
                    echo number_format($stmt->fetch(PDO::FETCH_ASSOC)['count']);
                    ?>
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--bg-light); border-radius: 8px;">
                <span style="color: var(--text-medium); font-size: 14px;">Unassigned</span>
                <span style="font-weight: 600; color: var(--text-light);">
                    <?php 
                    $totalEquipment = $systemUnitCount + $monitorCount + $printerCount + $allinoneCount;
                    $assignedEquipment = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
                    // Note: This is a simplified calculation, you may want to count actual unassigned items
                    echo number_format(max(0, $totalEquipment - $assignedEquipment));
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns: repeat(auto-fit, minmax(400px, 1fr))"] {
        grid-template-columns: 1fr !important;
    }
}
</style>