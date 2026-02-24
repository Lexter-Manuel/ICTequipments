<?php
// modules/reports/maintenance-summary.php
require_once '../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Active schedules
    $activeSchedules = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_schedule WHERE isActive = 1")->fetchColumn();
    $totalSchedules  = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_schedule")->fetchColumn();

    // Overdue / Due Soon from views
    $overdue  = (int) $db->query("SELECT COUNT(*) FROM view_overdue_maintenance")->fetchColumn();
    $dueSoon  = (int) $db->query("SELECT COUNT(*) FROM view_due_soon_maintenance")->fetchColumn();

    // Total maintenance performed (all time)
    $totalRecords = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_record")->fetchColumn();

    // This month
    $thisMonthRecords = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_record WHERE MONTH(maintenanceDate) = MONTH(CURDATE()) AND YEAR(maintenanceDate) = YEAR(CURDATE())")->fetchColumn();

    // Compliance rate
    $dueThisMonth = (int) $db->query("SELECT COUNT(*) FROM tbl_maintenance_schedule WHERE isActive = 1 AND MONTH(nextDueDate) = MONTH(CURDATE()) AND YEAR(nextDueDate) = YEAR(CURDATE())")->fetchColumn();
    $completedThisMonth = $db->query("
    SELECT COUNT(DISTINCT ms.scheduleId) 
    FROM tbl_maintenance_schedule ms 
    INNER JOIN tbl_maintenance_record mr ON mr.scheduleId = ms.scheduleId 
    WHERE MONTH(ms.nextDueDate) = MONTH(CURDATE())
      AND YEAR(ms.nextDueDate) = YEAR(CURDATE())
      AND MONTH(mr.maintenanceDate) = MONTH(CURDATE()) 
      AND YEAR(mr.maintenanceDate) = YEAR(CURDATE())
")->fetchColumn();
    $compliance = ($dueThisMonth > 0) ? round(($completedThisMonth / $dueThisMonth) * 100) : ($activeSchedules > 0 ? 100 : 0);

    // Monthly records – last 12 months
    $monthly = $db->query("
        SELECT DATE_FORMAT(maintenanceDate, '%Y-%m') as month_key,
               DATE_FORMAT(maintenanceDate, '%b %Y') as label,
               COUNT(*) as cnt
        FROM tbl_maintenance_record
        WHERE maintenanceDate >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY month_key ORDER BY month_key
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Records by type
    $byType = $db->query("
        SELECT mt.templateName, COUNT(mr.recordId) as cnt
        FROM tbl_maintenance_record mr
        LEFT JOIN tbl_maintenance_template mt ON mt.templateId = mr.templateId
        GROUP BY mt.templateId, mt.templateName ORDER BY cnt DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Latest condition summary
    $conditionSummary = $db->query("
        SELECT conditionRating, COUNT(*) as cnt
        FROM (
            SELECT mr.conditionRating,
                   ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
            FROM tbl_maintenance_record mr WHERE mr.conditionRating != ''
        ) latest WHERE rn = 1 GROUP BY conditionRating ORDER BY FIELD(conditionRating, 'Excellent','Good','Fair','Poor')
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Status summary
    $statusSummary = $db->query("
        SELECT overallStatus, COUNT(*) as cnt
        FROM (
            SELECT mr.overallStatus,
                   ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
            FROM tbl_maintenance_record mr WHERE mr.overallStatus != ''
        ) latest WHERE rn = 1 GROUP BY overallStatus ORDER BY FIELD(overallStatus, 'Operational','For Replacement','Disposed')
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Overdue list (top 20)
    $overdueList = $db->query("
        SELECT v.scheduleId, v.equipmentType as typeId, v.equipmentId, v.maintenanceFrequency,
               v.nextDueDate, v.days_overdue,
               etr.typeName as equipment_type,
               CASE
                   WHEN etr.tableName = 'tbl_systemunit' THEN (SELECT CONCAT(systemUnitBrand, ' ', systemUnitCategory) FROM tbl_systemunit WHERE systemunitId = v.equipmentId)
                   WHEN etr.tableName = 'tbl_monitor' THEN (SELECT CONCAT(monitorBrand, ' ', monitorSize) FROM tbl_monitor WHERE monitorId = v.equipmentId)
                   WHEN etr.tableName = 'tbl_printer' THEN (SELECT CONCAT(printerBrand, ' ', printerModel) FROM tbl_printer WHERE printerId = v.equipmentId)
                   WHEN etr.tableName = 'tbl_allinone' THEN (SELECT CONCAT(allinoneBrand, ' AIO') FROM tbl_allinone WHERE allinoneId = v.equipmentId)
                   WHEN etr.tableName = 'tbl_otherequipment' THEN (SELECT CONCAT(brand, ' ', model) FROM tbl_otherequipment WHERE otherEquipmentId = v.equipmentId)
                   ELSE CONCAT('Equipment #', v.equipmentId)
               END as equipment_name
        FROM view_overdue_maintenance v
        LEFT JOIN tbl_equipment_type_registry etr ON etr.typeId = v.equipmentType
        ORDER BY v.days_overdue DESC LIMIT 20
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Technicians leaderboard
    $technicians = $db->query("
        SELECT preparedBy, COUNT(*) as cnt,
               ROUND(AVG(CASE WHEN conditionRating='Excellent' THEN 4 WHEN conditionRating='Good' THEN 3 WHEN conditionRating='Fair' THEN 2 WHEN conditionRating='Poor' THEN 1 ELSE 0 END),1) as avg_rating
        FROM tbl_maintenance_record
        WHERE preparedBy IS NOT NULL AND preparedBy != ''
        GROUP BY preparedBy ORDER BY cnt DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Maintenance summary error: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="../public/assets/css/maintenance_summary.css">

<div class="report-container">
    <div class="report-header">
        <h2><i class="fas fa-tools"></i> Maintenance Summary Report</h2>
        <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>
    </div>

    <!-- Key Metrics -->
    <div class="report-grid cols-4">
        <div class="ms-stat"><div class="ms-icon green"><i class="fas fa-calendar-check"></i></div><div><div class="ms-val"><?= $activeSchedules ?></div><div class="ms-lbl">Active Schedules</div></div></div>
        <div class="ms-stat"><div class="ms-icon red"><i class="fas fa-exclamation-circle"></i></div><div><div class="ms-val"><?= $overdue ?></div><div class="ms-lbl">Overdue</div></div></div>
        <div class="ms-stat"><div class="ms-icon orange"><i class="fas fa-clock"></i></div><div><div class="ms-val"><?= $dueSoon ?></div><div class="ms-lbl">Due Soon</div></div></div>
        <div class="ms-stat"><div class="ms-icon blue"><i class="fas fa-check-double"></i></div><div><div class="ms-val"><?= number_format($totalRecords) ?></div><div class="ms-lbl">Total Records</div></div></div>
    </div>

    <div class="report-grid cols-3">
        <!-- Compliance Ring -->
        <div class="ms-panel">
            <div class="ms-panel-hdr"><h3><i class="fas fa-chart-pie"></i> Compliance Rate</h3></div>
            <div class="ms-panel-body" style="text-align: center;">
                <?php
                    $pct = $compliance;
                    $color = $pct >= 80 ? '#16a34a' : ($pct >= 50 ? '#d97706' : '#dc2626');
                    $circ = 2 * M_PI * 42;
                    $dash = ($pct / 100) * $circ;
                ?>
                <div class="compliance-ring">
                    <svg viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" fill="none" stroke="#f3f4f6" stroke-width="8"/>
                        <circle cx="50" cy="50" r="42" fill="none" stroke="<?= $color ?>" stroke-width="8"
                                stroke-dasharray="<?= round($dash, 1) ?> <?= round($circ, 1) ?>"
                                stroke-linecap="round" transform="rotate(-90 50 50)"/>
                        <text x="50" y="50" text-anchor="middle" dominant-baseline="central"
                              font-size="18" font-weight="800" fill="<?= $color ?>" font-family="monospace">
                            <?= $pct ?>%
                        </text>
                    </svg>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-medium);">
                    <strong><?= $completedThisMonth ?></strong> of <strong><?= $dueThisMonth ?></strong> completed this month
                </div>
                <div style="font-size: 0.7rem; color: var(--text-light); margin-top: 0.25rem;">
                    <?= $thisMonthRecords ?> total records this month
                </div>
            </div>
        </div>

        <!-- Monthly Activity Chart -->
        <div class="ms-panel" style="grid-column: span 2;">
            <div class="ms-panel-hdr"><h3><i class="fas fa-chart-bar"></i> Monthly Maintenance Activity (Last 12 Months)</h3></div>
            <div class="ms-panel-body">
                <?php
                    $maxCnt = max(array_column($monthly, 'cnt') ?: [1]);
                    foreach ($monthly as $m): $barPct = round(($m['cnt'] / $maxCnt) * 100);
                ?>
                <div class="chart-bar-row">
                    <div class="chart-bar-label"><?= $m['label'] ?></div>
                    <div class="chart-bar-track">
                        <div class="chart-bar-fill" style="width: <?= max($barPct, 5) ?>%;">
                            <span class="chart-bar-val"><?= $m['cnt'] ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($monthly)): ?>
                <p style="text-align: center; color: var(--text-light); padding: 1rem;">No maintenance records in the last 12 months.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="report-grid cols-2">
        <!-- By Template / Type -->
        <div class="ms-panel">
            <div class="ms-panel-hdr"><h3><i class="fas fa-clipboard-list"></i> Records by Maintenance Type</h3></div>
            <div class="ms-panel-body" style="padding: 0;">
                <table class="ms-table">
                    <thead><tr><th>Maintenance Type</th><th>Records</th></tr></thead>
                    <tbody>
                        <?php foreach ($byType as $t): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($t['templateName'] ?? 'Unspecified') ?></td>
                            <td style="font-family: var(--font-mono); font-weight: 700;"><?= $t['cnt'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($byType)): ?>
                        <tr><td colspan="2" style="text-align: center; color: var(--text-light); padding: 1rem;">No records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Condition Summary -->
        <div class="ms-panel">
            <div class="ms-panel-hdr"><h3><i class="fas fa-heartbeat"></i> Latest Equipment Health</h3></div>
            <div class="ms-panel-body">
                <h4 style="font-size: 0.8rem; color: var(--text-medium); margin-bottom: 0.75rem; text-transform: uppercase;">Condition Rating</h4>
                <?php foreach ($conditionSummary as $cond):
                    $bgClass = 'bg-' . strtolower($cond['conditionRating']);
                    $condTot = array_sum(array_column($conditionSummary, 'cnt'));
                    $pct = $condTot > 0 ? round(($cond['cnt'] / $condTot) * 100) : 0;
                ?>
                <div style="display: flex; align-items: center; margin-bottom: 0.5rem; gap: 0.75rem;">
                    <span class="badge-sm <?= $bgClass ?>" style="min-width: 70px; text-align: center;"><?= $cond['conditionRating'] ?></span>
                    <div style="flex: 1; height: 20px; background: #f3f4f6; border-radius: 10px; overflow: hidden;">
                        <div style="width: <?= max($pct, 3) ?>%; height: 100%; background: var(--primary-green); border-radius: 10px;"></div>
                    </div>
                    <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem; min-width: 55px; text-align: right;"><?= $cond['cnt'] ?> (<?= $pct ?>%)</span>
                </div>
                <?php endforeach; ?>

                <h4 style="font-size: 0.8rem; color: var(--text-medium); margin: 1.25rem 0 0.75rem; text-transform: uppercase;">Overall Status</h4>
                <?php foreach ($statusSummary as $stat):
                    $bgClass = 'bg-operational';
                    if ($stat['overallStatus'] === 'For Replacement') $bgClass = 'bg-replacement';
                    elseif ($stat['overallStatus'] === 'Disposed') $bgClass = 'bg-disposed';
                ?>
                <div style="display: flex; align-items: center; margin-bottom: 0.5rem; gap: 0.75rem;">
                    <span class="badge-sm <?= $bgClass ?>" style="min-width: 100px; text-align: center;"><?= $stat['overallStatus'] ?></span>
                    <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem;"><?= $stat['cnt'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Overdue List -->
    <?php if (!empty($overdueList)): ?>
    <div class="ms-panel" style="margin-top: 1.5rem;">
        <div class="ms-panel-hdr">
            <h3><i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i> Overdue Maintenance</h3>
            <span class="badge-sm bg-overdue"><?= $overdue ?> items</span>
        </div>
        <div class="ms-panel-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
            <table class="ms-table">
                <thead><tr><th>Equipment</th><th>Type</th><th>Maintenance</th><th>Due Date</th><th>Days Overdue</th></tr></thead>
                <tbody>
                    <?php foreach ($overdueList as $od): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($od['equipment_name'] ?? 'Unknown') ?></td>
                        <td><?= htmlspecialchars($od['equipment_type'] ?? '') ?></td>
                        <td><?= htmlspecialchars($od['maintenanceFrequency'] ?? '') ?></td>
                        <td style="font-family: var(--font-mono); font-size: 0.8rem;"><?= date('M d, Y', strtotime($od['nextDueDate'])) ?></td>
                        <td>
                            <?php $days = $od['days_overdue'];
                                $cls = $days > 30 ? 'color:#dc2626;' : ($days > 14 ? 'color:#ea580c;' : 'color:#d97706;');
                            ?>
                            <span style="font-family: var(--font-mono); font-weight: 700; <?= $cls ?>"><?= $days ?> days</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Technicians Leaderboard -->
    <?php if (!empty($technicians)): ?>
    <div class="ms-panel" style="margin-top: 1.5rem;">
        <div class="ms-panel-hdr"><h3><i class="fas fa-user-cog"></i> Maintenance Technicians</h3></div>
        <div class="ms-panel-body" style="padding: 0;">
            <table class="ms-table">
                <thead><tr><th>#</th><th>Technician</th><th>Records</th><th>Avg Rating</th></tr></thead>
                <tbody>
                    <?php foreach ($technicians as $i => $tech): ?>
                    <tr>
                        <td style="font-weight: 700; font-family: var(--font-mono);"><?= $i + 1 ?></td>
                        <td style="font-weight: 600;"><?= htmlspecialchars($tech['preparedBy']) ?></td>
                        <td style="font-family: var(--font-mono);"><?= $tech['cnt'] ?></td>
                        <td>
                            <?php
                                $r = $tech['avg_rating'];
                                $stars = '';
                                for ($s = 1; $s <= 4; $s++) {
                                    $stars .= $s <= round($r) ? '<i class="fas fa-star" style="color:#f59e0b; font-size:0.7rem;"></i>' : '<i class="far fa-star" style="color:#d1d5db; font-size:0.7rem;"></i>';
                                }
                            ?>
                            <?= $stars ?> <span style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-medium);">(<?= $r ?>)</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
