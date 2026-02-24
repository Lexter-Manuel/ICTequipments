<?php
// modules/dashboard/home.php
require_once '../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // ─── Equipment Counts ───
    $systemUnitCount = (int) $db->query("SELECT COUNT(*) FROM tbl_systemunit")->fetchColumn();
    $monitorCount    = (int) $db->query("SELECT COUNT(*) FROM tbl_monitor")->fetchColumn();
    $printerCount    = (int) $db->query("SELECT COUNT(*) FROM tbl_printer")->fetchColumn();
    $allinoneCount   = (int) $db->query("SELECT COUNT(*) FROM tbl_allinone")->fetchColumn();
    $otherCount      = (int) $db->query("SELECT COUNT(*) FROM tbl_otherequipment")->fetchColumn();
    $totalEquipment  = $systemUnitCount + $monitorCount + $printerCount + $allinoneCount + $otherCount;

    $assignedEquip = (int) $db->query("
        SELECT (
            (SELECT COUNT(*) FROM tbl_systemunit WHERE employeeId IS NOT NULL) +
            (SELECT COUNT(*) FROM tbl_monitor WHERE employeeId IS NOT NULL) +
            (SELECT COUNT(*) FROM tbl_printer WHERE employeeId IS NOT NULL) +
            (SELECT COUNT(*) FROM tbl_allinone WHERE employeeId IS NOT NULL) +
            (SELECT COUNT(*) FROM tbl_otherequipment WHERE employeeId IS NOT NULL)
        )
    ")->fetchColumn();
    $unassignedEquip = $totalEquipment - $assignedEquip;

    // ─── People ───
    $employeeCount = (int) $db->query("SELECT COUNT(*) FROM tbl_employee WHERE is_active = 1")->fetchColumn();
    $softwareCount = (int) $db->query("SELECT COUNT(*) FROM tbl_software")->fetchColumn();

    // ─── Maintenance Stats ───
    $activeSchedules = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_schedule WHERE isActive = 1")->fetchColumn();
    $overdueCount    = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_schedule WHERE isActive = 1 AND nextDueDate < CURDATE()")->fetchColumn();
    $dueSoonCount    = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_schedule WHERE isActive = 1 AND nextDueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
    $dueThisMonth    = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_schedule WHERE isActive = 1 AND nextDueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
    $completedMonth  = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_record WHERE MONTH(maintenanceDate) = MONTH(CURDATE()) AND YEAR(maintenanceDate) = YEAR(CURDATE())")->fetchColumn();
    $totalCompleted  = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_record")->fetchColumn();
    $complianceRate  = $activeSchedules > 0 ? round((($activeSchedules - $overdueCount) / $activeSchedules) * 100, 1) : 100;

    // ─── Alerts: Overdue ───
    $overdueList = $db->query("
        SELECT ms.scheduleId, ms.equipmentId, ms.nextDueDate,
               DATEDIFF(CURDATE(), ms.nextDueDate) as days_overdue,
               etr.typeName
        FROM tbl_maintenance_schedule ms
        LEFT JOIN tbl_equipment_type_registry etr ON ms.equipmentType = etr.typeId
        WHERE ms.isActive = 1 AND ms.nextDueDate < CURDATE()
        ORDER BY days_overdue DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ─── Alerts: Due Soon ───
    $dueSoonList = $db->query("
        SELECT ms.scheduleId, ms.equipmentId, ms.nextDueDate,
               DATEDIFF(ms.nextDueDate, CURDATE()) as days_until_due,
               etr.typeName
        FROM tbl_maintenance_schedule ms
        LEFT JOIN tbl_equipment_type_registry etr ON ms.equipmentType = etr.typeId
        WHERE ms.isActive = 1 AND ms.nextDueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY ms.nextDueDate ASC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ─── Alerts: Problem Equipment ───
    $problemEquipment = $db->query("
        SELECT mr.equipmentTypeId, mr.equipmentId, mr.overallStatus, 
               mr.conditionRating, mr.maintenanceDate, mr.remarks,
               etr.typeName
        FROM tbl_maintenance_record mr
        LEFT JOIN tbl_equipment_type_registry etr ON mr.equipmentTypeId = etr.typeId
        WHERE (mr.overallStatus IN ('For Replacement','Disposed') OR mr.conditionRating = 'Poor')
        AND mr.maintenanceDate = (
            SELECT MAX(mr2.maintenanceDate) FROM tbl_maintenance_record mr2
            WHERE mr2.scheduleId = mr.scheduleId
        )
        ORDER BY mr.maintenanceDate DESC LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ─── Alerts: Software Licenses ───
    $expiringLicenses = $db->query("
        SELECT s.licenseSoftware, s.expiryDate,
               DATEDIFF(s.expiryDate, CURDATE()) as days_until_expiry,
               CONCAT(e.firstName, ' ', e.lastName) as assignedTo
        FROM tbl_software s
        LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
        WHERE s.expiryDate IS NOT NULL AND s.expiryDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)
        ORDER BY s.expiryDate ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $expiredLicenses = $db->query("
        SELECT s.licenseSoftware, s.expiryDate,
               DATEDIFF(CURDATE(), s.expiryDate) as days_expired,
               CONCAT(e.firstName, ' ', e.lastName) as assignedTo
        FROM tbl_software s
        LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
        WHERE s.expiryDate IS NOT NULL AND s.expiryDate < CURDATE()
        ORDER BY s.expiryDate DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ─── Condition Breakdown ───
    $conditionBreakdown = $db->query("
        SELECT conditionRating, COUNT(*) as cnt 
        FROM (
            SELECT mr.conditionRating,
                   ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
            FROM tbl_maintenance_record mr WHERE mr.conditionRating != ''
        ) latest WHERE rn = 1 GROUP BY conditionRating
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // ─── Recent Maintenance ───
    $recentMaintenance = $db->query("
        SELECT mr.recordId, mr.maintenanceDate, mr.overallStatus, mr.conditionRating,
               mr.preparedBy, etr.typeName
        FROM tbl_maintenance_record mr
        LEFT JOIN tbl_equipment_type_registry etr ON mr.equipmentTypeId = etr.typeId
        ORDER BY mr.maintenanceDate DESC LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ─── Recent Activity ───
    $recentActivity = $db->query("
        SELECT action, module, description, timestamp, email, success
        FROM activity_log ORDER BY timestamp DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    // ─── Total alert count ───
    $totalAlerts = $overdueCount + count($problemEquipment) + count($expiredLicenses);

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $systemUnitCount = $monitorCount = $printerCount = $allinoneCount = $otherCount = 0;
    $totalEquipment = $assignedEquip = $unassignedEquip = $employeeCount = 0;
    $softwareCount = $activeSchedules = $overdueCount = $dueSoonCount = 0;
    $dueThisMonth = $completedMonth = $totalCompleted = $complianceRate = 0;
    $totalAlerts = 0;
    $overdueList = $dueSoonList = $problemEquipment = [];
    $expiringLicenses = $expiredLicenses = $recentMaintenance = $recentActivity = [];
    $conditionBreakdown = [];
}
?>

<link rel="stylesheet" href="assets/css/home.css">

<!-- ═══ Welcome Banner ═══ -->
<div class="welcome-banner">
    <div class="welcome-content">
        <h2>Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Administrator') ?>!</h2>
        <p>ICT Equipment Inventory &amp; Preventive Maintenance Overview</p>
    </div>
    <div class="welcome-badges">
        <div class="welcome-badge">
            <span class="wb-val"><?= number_format($totalEquipment) ?></span>
            <span class="wb-lbl">Equipment</span>
        </div>
        <div class="welcome-badge">
            <span class="wb-val"><?= number_format($employeeCount) ?></span>
            <span class="wb-lbl">Employees</span>
        </div>
        <div class="welcome-badge">
            <span class="wb-val"><?= $complianceRate ?>%</span>
            <span class="wb-lbl">Compliance</span>
        </div>
    </div>
</div>

<!-- ═══ Critical Alerts ═══ -->
<?php if ($overdueCount > 0): ?>
<div class="alert-banner danger">
    <i class="fas fa-exclamation-triangle"></i>
    <span><strong><?= $overdueCount ?> maintenance schedule<?= $overdueCount > 1 ? 's are' : ' is' ?> overdue!</strong> Immediate action required.</span>
    <div class="alert-actions">
        <a href="#" onclick="navigateToPage('maintenance-schedule'); return false;">View Schedules</a>
    </div>
</div>
<?php endif; ?>

<?php if ($dueSoonCount > 0): ?>
<div class="alert-banner warning">
    <i class="fas fa-clock"></i>
    <span><strong><?= $dueSoonCount ?> maintenance<?= $dueSoonCount > 1 ? 's' : '' ?> due within 7 days.</strong> Plan ahead to stay on schedule.</span>
    <div class="alert-actions">
        <a href="#" onclick="navigateToPage('perform-maintenance'); return false;">Start Maintenance</a>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($expiredLicenses)): ?>
<div class="alert-banner danger">
    <i class="fas fa-key"></i>
    <span><strong><?= count($expiredLicenses) ?> software license<?= count($expiredLicenses) > 1 ? 's have' : ' has' ?> expired!</strong> Renew to maintain compliance.</span>
    <div class="alert-actions">
        <a href="#" onclick="navigateToPage('software'); return false;">Manage Licenses</a>
    </div>
</div>
<?php endif; ?>

<!-- ═══ Quick Stats ═══ -->
<div class="dash-grid cols-4" style="animation: fadeInUp 0.4s ease-out;">
    <div class="stat-card-mini">
        <div class="stat-icon-sm green"><i class="fas fa-desktop"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= number_format($totalEquipment) ?></div>
            <div class="stat-lbl">Total Equipment</div>
        </div>
    </div>
    <div class="stat-card-mini">
        <div class="stat-icon-sm blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= number_format($employeeCount) ?></div>
            <div class="stat-lbl">Active Employees</div>
        </div>
    </div>
    <div class="stat-card-mini">
        <div class="stat-icon-sm <?= $overdueCount > 0 ? 'red' : 'green' ?>"><i class="fas fa-clipboard-check"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= number_format($activeSchedules) ?></div>
            <div class="stat-lbl">Active Schedules</div>
        </div>
    </div>
    <div class="stat-card-mini">
        <div class="stat-icon-sm <?= $complianceRate >= 80 ? 'green' : ($complianceRate >= 50 ? 'orange' : 'red') ?>"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= $complianceRate ?>%</div>
            <div class="stat-lbl">Compliance Rate</div>
        </div>
    </div>
</div>

<!-- ═══ Equipment Breakdown ═══ -->
<div class="dash-grid cols-4" style="animation: fadeInUp 0.5s ease-out;">
    <div class="stat-card-mini">
        <div class="stat-icon-sm green"><i class="fas fa-server"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= number_format($systemUnitCount) ?></div>
            <div class="stat-lbl">System Units</div>
        </div>
    </div>
    <div class="stat-card-mini">
        <div class="stat-icon-sm blue"><i class="fas fa-tv"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= number_format($monitorCount) ?></div>
            <div class="stat-lbl">Monitors</div>
        </div>
    </div>
    <div class="stat-card-mini">
        <div class="stat-icon-sm purple"><i class="fas fa-print"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= number_format($printerCount) ?></div>
            <div class="stat-lbl">Printers</div>
        </div>
    </div>
    <div class="stat-card-mini">
        <div class="stat-icon-sm teal"><i class="fas fa-laptop"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= number_format($allinoneCount + $otherCount) ?></div>
            <div class="stat-lbl">AIO &amp; Other</div>
        </div>
    </div>
</div>

<!-- ═══ Maintenance Indicators ═══ -->
<div class="dash-grid cols-4" style="animation: fadeInUp 0.55s ease-out;">
    <div class="stat-card-mini" style="border-left: 3px solid #dc2626;">
        <div class="stat-icon-sm red"><i class="fas fa-exclamation-circle"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= $overdueCount ?></div>
            <div class="stat-lbl">Overdue</div>
        </div>
    </div>
    <div class="stat-card-mini" style="border-left: 3px solid #d97706;">
        <div class="stat-icon-sm orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= $dueSoonCount ?></div>
            <div class="stat-lbl">Due in 7 Days</div>
        </div>
    </div>
    <div class="stat-card-mini" style="border-left: 3px solid #3b82f6;">
        <div class="stat-icon-sm blue"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= $dueThisMonth ?></div>
            <div class="stat-lbl">Due in 30 Days</div>
        </div>
    </div>
    <div class="stat-card-mini" style="border-left: 3px solid #16a34a;">
        <div class="stat-icon-sm green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-val"><?= $completedMonth ?></div>
            <div class="stat-lbl">Done This Month</div>
        </div>
    </div>
</div>

<!-- ═══ Main Content Panels ═══ -->
<div class="dash-grid cols-2" style="animation: fadeInUp 0.6s ease-out;">

    <!-- Alerts & Warnings Panel -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <h3><i class="fas fa-bell"></i> Alerts &amp; Warnings</h3>
            <?php if ($totalAlerts > 0): ?>
                <span class="badge-count"><?= $totalAlerts ?></span>
            <?php else: ?>
                <span class="badge-count ok">All Clear</span>
            <?php endif; ?>
        </div>
        <div class="dash-panel-body" style="max-height: 380px; overflow-y: auto;">
            <?php if (empty($overdueList) && empty($problemEquipment) && empty($expiredLicenses) && empty($dueSoonList) && empty($expiringLicenses)): ?>
                <div class="dash-empty">
                    <i class="fas fa-check-circle" style="color: #22c55e;"></i>
                    <p>No alerts or warnings. Everything looks good!</p>
                </div>
            <?php else: ?>
                <?php foreach ($overdueList as $item): ?>
                <div class="alert-item overdue">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <div class="alert-text">
                        <strong><?= htmlspecialchars($item['typeName'] ?? 'Equipment') ?> #<?= $item['equipmentId'] ?></strong> — maintenance overdue
                    </div>
                    <div class="alert-meta"><?= $item['days_overdue'] ?>d overdue</div>
                </div>
                <?php endforeach; ?>

                <?php foreach ($dueSoonList as $item): ?>
                <div class="alert-item due-soon">
                    <i class="fas fa-clock alert-icon"></i>
                    <div class="alert-text">
                        <strong><?= htmlspecialchars($item['typeName'] ?? 'Equipment') ?> #<?= $item['equipmentId'] ?></strong> — due <?= date('M j', strtotime($item['nextDueDate'])) ?>
                    </div>
                    <div class="alert-meta"><?= $item['days_until_due'] ?>d left</div>
                </div>
                <?php endforeach; ?>

                <?php foreach ($problemEquipment as $item): ?>
                <div class="alert-item problem">
                    <i class="fas fa-tools alert-icon"></i>
                    <div class="alert-text">
                        <strong><?= htmlspecialchars($item['typeName'] ?? 'Equipment') ?> #<?= $item['equipmentId'] ?></strong> — <?= $item['overallStatus'] ?> (<?= $item['conditionRating'] ?>)
                    </div>
                    <div class="alert-meta"><?= date('M j', strtotime($item['maintenanceDate'])) ?></div>
                </div>
                <?php endforeach; ?>

                <?php foreach ($expiredLicenses as $item): ?>
                <div class="alert-item license-expired">
                    <i class="fas fa-key alert-icon" style="color:#dc2626;"></i>
                    <div class="alert-text">
                        <strong><?= htmlspecialchars($item['licenseSoftware']) ?></strong> — expired
                    </div>
                    <div class="alert-meta"><?= $item['days_expired'] ?>d ago</div>
                </div>
                <?php endforeach; ?>

                <?php foreach ($expiringLicenses as $item): ?>
                <div class="alert-item license-warn">
                    <i class="fas fa-key alert-icon" style="color:#ea580c;"></i>
                    <div class="alert-text">
                        <strong><?= htmlspecialchars($item['licenseSoftware']) ?></strong> — expires <?= date('M j, Y', strtotime($item['expiryDate'])) ?>
                    </div>
                    <div class="alert-meta"><?= $item['days_until_expiry'] ?>d left</div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Equipment Overview Panel -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <h3><i class="fas fa-chart-bar"></i> Equipment Distribution</h3>
            <span class="refresh-indicator" id="equipRefresh">
                <i class="fas fa-sync-alt"></i> Live
            </span>
        </div>
        <div class="dash-panel-body">
            <?php
            $maxCount = max($systemUnitCount, $monitorCount, $printerCount, $allinoneCount, $otherCount, 1);
            $bars = [
                ['System Units', $systemUnitCount, 'green'],
                ['Monitors', $monitorCount, 'blue'],
                ['Printers', $printerCount, 'purple'],
                ['All-in-One', $allinoneCount, 'orange'],
                ['Other', $otherCount, 'teal'],
            ];
            foreach ($bars as $bar):
                $pct = $maxCount > 0 ? round(($bar[1] / $maxCount) * 100) : 0;
            ?>
            <div class="equip-bar-row">
                <div class="equip-bar-label"><?= $bar[0] ?></div>
                <div class="equip-bar-track">
                    <div class="equip-bar-fill <?= $bar[2] ?>" style="width: <?= max($pct, 5) ?>%;"><?= $bar[1] ?></div>
                </div>
                <div class="equip-bar-count"><?= $bar[1] ?></div>
            </div>
            <?php endforeach; ?>

            <div style="display: flex; justify-content: space-between; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #f3f4f6;">
                <div style="text-align: center; flex: 1;">
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); font-family: var(--font-mono);"><?= number_format($assignedEquip) ?></div>
                    <div style="font-size: 0.7rem; color: #16a34a; font-weight: 700; text-transform: uppercase;">Assigned</div>
                </div>
                <div style="text-align: center; flex: 1;">
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); font-family: var(--font-mono);"><?= number_format($unassignedEquip) ?></div>
                    <div style="font-size: 0.7rem; color: #d97706; font-weight: 700; text-transform: uppercase;">Unassigned</div>
                </div>
                <div style="text-align: center; flex: 1;">
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); font-family: var(--font-mono);"><?= number_format($softwareCount) ?></div>
                    <div style="font-size: 0.7rem; color: #7c3aed; font-weight: 700; text-transform: uppercase;">Licenses</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Recent Maintenance + Equipment Condition ═══ -->
<div class="dash-grid cols-2" style="animation: fadeInUp 0.7s ease-out;">

    <!-- Recent Maintenance -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <h3><i class="fas fa-wrench"></i> Recent Maintenance</h3>
            <a href="#" onclick="navigateToPage('maintenance-history'); return false;" style="font-size: 0.75rem; color: var(--primary-green); font-weight: 600; text-decoration: none;">View All →</a>
        </div>
        <div class="dash-panel-body" style="padding: 0;">
            <?php if (empty($recentMaintenance)): ?>
                <div class="dash-empty">
                    <i class="fas fa-wrench"></i>
                    <p>No maintenance records yet</p>
                </div>
            <?php else: ?>
            <table class="mini-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Condition</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentMaintenance as $maint): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($maint['typeName'] ?? 'N/A') ?></strong></td>
                        <td style="font-family: var(--font-mono); font-size: 0.75rem;"><?= date('M j, Y', strtotime($maint['maintenanceDate'])) ?></td>
                        <td>
                            <?php
                            $statusClass = 'badge-operational';
                            if ($maint['overallStatus'] === 'For Replacement') $statusClass = 'badge-replacement';
                            elseif ($maint['overallStatus'] === 'Disposed') $statusClass = 'badge-disposed';
                            ?>
                            <span class="badge-sm <?= $statusClass ?>"><?= $maint['overallStatus'] ?></span>
                        </td>
                        <td>
                            <?php
                            $condClass = 'badge-good';
                            if ($maint['conditionRating'] === 'Excellent') $condClass = 'badge-excellent';
                            elseif ($maint['conditionRating'] === 'Fair') $condClass = 'badge-fair';
                            elseif ($maint['conditionRating'] === 'Poor') $condClass = 'badge-poor';
                            ?>
                            <span class="badge-sm <?= $condClass ?>"><?= $maint['conditionRating'] ?></span>
                        </td>
                        <td style="font-size: 0.75rem;"><?= htmlspecialchars($maint['preparedBy'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Equipment Condition Overview -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <h3><i class="fas fa-heartbeat"></i> Equipment Condition</h3>
        </div>
        <div class="dash-panel-body">
            <?php if (empty($conditionBreakdown)): ?>
                <div class="dash-empty">
                    <i class="fas fa-heartbeat"></i>
                    <p>No condition data available yet. Perform maintenance to generate data.</p>
                </div>
            <?php else: ?>
            <?php
            $condTotal = array_sum($conditionBreakdown);
            $condColors = ['Excellent' => '#22c55e', 'Good' => '#3b82f6', 'Fair' => '#f59e0b', 'Poor' => '#ef4444'];
            $condIcons = ['Excellent' => 'fa-star', 'Good' => 'fa-thumbs-up', 'Fair' => 'fa-minus-circle', 'Poor' => 'fa-exclamation-triangle'];
            ?>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1rem;">
                <?php foreach (['Excellent', 'Good', 'Fair', 'Poor'] as $cond): ?>
                <?php $cnt = $conditionBreakdown[$cond] ?? 0; $pctC = $condTotal > 0 ? round(($cnt / $condTotal) * 100) : 0; ?>
                <div style="background: <?= $condColors[$cond] ?>10; border-radius: var(--radius-md); padding: 0.75rem; text-align: center;">
                    <i class="fas <?= $condIcons[$cond] ?>" style="color: <?= $condColors[$cond] ?>; font-size: 1.1rem; margin-bottom: 0.25rem; display: block;"></i>
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); font-family: var(--font-mono);"><?= $cnt ?></div>
                    <div style="font-size: 0.7rem; color: var(--text-medium); font-weight: 600;"><?= $cond ?> (<?= $pctC ?>%)</div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Compliance Ring -->
            <div style="text-align: center; padding-top: 0.5rem; border-top: 1px solid #f3f4f6;">
                <div style="display: inline-block; position: relative; width: 100px; height: 100px;">
                    <svg viewBox="0 0 36 36" style="width: 100px; height: 100px; transform: rotate(-90deg);">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none"
                              stroke="<?= $complianceRate >= 80 ? '#22c55e' : ($complianceRate >= 50 ? '#f59e0b' : '#ef4444') ?>"
                              stroke-width="3"
                              stroke-dasharray="<?= $complianceRate ?>, 100"
                              stroke-linecap="round"/>
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.1rem; font-weight: 800; color: var(--text-dark); font-family: var(--font-mono);">
                        <?= $complianceRate ?>%
                    </div>
                </div>
                <div style="font-size: 0.75rem; color: var(--text-medium); font-weight: 600; margin-top: 0.25rem;">Maintenance Compliance</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══ Recent Activity + Software Licenses ═══ -->
<div class="dash-grid cols-2" style="animation: fadeInUp 0.8s ease-out;">
    <div class="dash-panel">
        <div class="dash-panel-header">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
            <a href="#" onclick="navigateToPage('audit-trail'); return false;" style="font-size: 0.75rem; color: var(--primary-green); font-weight: 600; text-decoration: none;">View Audit Trail →</a>
        </div>
        <div class="dash-panel-body" style="max-height: 280px; overflow-y: auto;">
            <?php if (empty($recentActivity)): ?>
                <div class="dash-empty">
                    <i class="fas fa-history"></i>
                    <p>No recent activity logged yet</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentActivity as $act): ?>
                <?php
                $dotClass = 'other';
                $action = strtolower($act['action']);
                if (strpos($action, 'login') !== false) $dotClass = 'login';
                elseif (strpos($action, 'create') !== false || strpos($action, 'add') !== false) $dotClass = 'create';
                elseif (strpos($action, 'update') !== false || strpos($action, 'edit') !== false) $dotClass = 'update';
                elseif (strpos($action, 'delete') !== false) $dotClass = 'delete';
                ?>
                <div class="activity-item">
                    <div class="activity-dot <?= $dotClass ?>"></div>
                    <div style="flex: 1;">
                        <div class="activity-text"><?= htmlspecialchars($act['description'] ?? $act['action']) ?></div>
                        <div class="activity-time"><?= htmlspecialchars($act['email']) ?> · <?= date('M j, g:i A', strtotime($act['timestamp'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Software Licenses -->
    <div class="dash-panel">
        <div class="dash-panel-header">
            <h3><i class="fas fa-key"></i> Software Licenses</h3>
            <a href="#" onclick="navigateToPage('software'); return false;" style="font-size: 0.75rem; color: var(--primary-green); font-weight: 600; text-decoration: none;">Manage →</a>
        </div>
        <div class="dash-panel-body">
            <div style="display: flex; justify-content: space-around; text-align: center; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-dark); font-family: var(--font-mono);"><?= $softwareCount ?></div>
                    <div style="font-size: 0.7rem; color: var(--text-medium); font-weight: 600; text-transform: uppercase;">Total</div>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #dc2626; font-family: var(--font-mono);"><?= count($expiredLicenses) ?></div>
                    <div style="font-size: 0.7rem; color: #dc2626; font-weight: 600; text-transform: uppercase;">Expired</div>
                </div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #d97706; font-family: var(--font-mono);"><?= count($expiringLicenses) ?></div>
                    <div style="font-size: 0.7rem; color: #d97706; font-weight: 600; text-transform: uppercase;">Expiring Soon</div>
                </div>
            </div>
            <?php if (!empty($expiredLicenses) || !empty($expiringLicenses)): ?>
            <div style="max-height: 180px; overflow-y: auto;">
                <?php foreach ($expiredLicenses as $lic): ?>
                <div class="alert-item license-expired">
                    <i class="fas fa-times-circle alert-icon" style="color: #dc2626;"></i>
                    <div class="alert-text"><?= htmlspecialchars($lic['licenseSoftware']) ?><?= $lic['assignedTo'] ? ' — '.$lic['assignedTo'] : '' ?></div>
                    <div class="alert-meta" style="color: #dc2626;"><?= $lic['days_expired'] ?>d expired</div>
                </div>
                <?php endforeach; ?>
                <?php foreach ($expiringLicenses as $lic): ?>
                <div class="alert-item license-warn">
                    <i class="fas fa-exclamation-circle alert-icon" style="color: #ea580c;"></i>
                    <div class="alert-text"><?= htmlspecialchars($lic['licenseSoftware']) ?><?= $lic['assignedTo'] ? ' — '.$lic['assignedTo'] : '' ?></div>
                    <div class="alert-meta" style="color: #ea580c;"><?= $lic['days_until_expiry'] ?>d left</div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="dash-empty">
                    <i class="fas fa-check-circle" style="color: #22c55e;"></i>
                    <p>All licenses are current</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// ═══ Auto-Refresh Dashboard Every 60 Seconds ═══
(function() {
    var refreshInterval = 60000;
    var refreshTimer = setInterval(function() {
        var indicator = document.getElementById('equipRefresh');
        if (indicator) {
            indicator.classList.add('refreshing');
            indicator.innerHTML = '<i class="fas fa-sync-alt"></i> Updating...';
        }
        fetch('../ajax/get_dashboard_data.php')
            .then(function(r) { return r.json(); })
            .catch(function(e) { console.warn('Dashboard refresh failed:', e); })
            .finally(function() {
                if (indicator) {
                    indicator.classList.remove('refreshing');
                    indicator.innerHTML = '<i class="fas fa-sync-alt"></i> Live';
                }
            });
    }, refreshInterval);
    window.addEventListener('beforeunload', function() { clearInterval(refreshTimer); });
})();
</script>