<?php
// modules/reports/equipment-summary.php
require_once '../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Equipment counts
    $suCount  = (int) $db->query("SELECT COUNT(*) FROM tbl_systemunit")->fetchColumn();
    $moCount  = (int) $db->query("SELECT COUNT(*) FROM tbl_monitor")->fetchColumn();
    $prCount  = (int) $db->query("SELECT COUNT(*) FROM tbl_printer")->fetchColumn();
    $aioCount = (int) $db->query("SELECT COUNT(*) FROM tbl_allinone")->fetchColumn();
    $othCount = (int) $db->query("SELECT COUNT(*) FROM tbl_otherequipment")->fetchColumn();
    $total    = $suCount + $moCount + $prCount + $aioCount + $othCount;

    // Assigned/unassigned
    $assigned = (int) $db->query("SELECT (
        (SELECT COUNT(*) FROM tbl_systemunit WHERE employeeId IS NOT NULL) +
        (SELECT COUNT(*) FROM tbl_monitor WHERE employeeId IS NOT NULL) +
        (SELECT COUNT(*) FROM tbl_printer WHERE employeeId IS NOT NULL) +
        (SELECT COUNT(*) FROM tbl_allinone WHERE employeeId IS NOT NULL) +
        (SELECT COUNT(*) FROM tbl_otherequipment WHERE employeeId IS NOT NULL)
    )")->fetchColumn();
    $unassigned = $total - $assigned;

    // Equipment by division
    $byDivision = $db->query("
        SELECT l.location_name as division,
               COUNT(DISTINCT su.systemunitId) as system_units,
               COUNT(DISTINCT mo.monitorId) as monitors,
               COUNT(DISTINCT pr.printerId) as printers,
               COUNT(DISTINCT aio.allinoneId) as allinones
        FROM location l
        LEFT JOIN location sec ON sec.parent_location_id = l.location_id AND sec.is_deleted = '0'
        LEFT JOIN location unit ON unit.parent_location_id = sec.location_id AND unit.is_deleted = '0'
        LEFT JOIN tbl_employee e ON (e.location_id = l.location_id OR e.location_id = sec.location_id OR e.location_id = unit.location_id) AND e.is_active = 1
        LEFT JOIN tbl_systemunit su ON su.employeeId = e.employeeId
        LEFT JOIN tbl_monitor mo ON mo.employeeId = e.employeeId
        LEFT JOIN tbl_printer pr ON pr.employeeId = e.employeeId
        LEFT JOIN tbl_allinone aio ON aio.employeeId = e.employeeId
        WHERE l.location_type_id = 1 AND l.is_deleted = '0'
        GROUP BY l.location_id, l.location_name
        ORDER BY l.location_name
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Equipment by year acquired
    $byYear = $db->query("
        SELECT year_acquired, SUM(cnt) as total FROM (
            SELECT yearAcquired as year_acquired, COUNT(*) as cnt FROM tbl_systemunit WHERE yearAcquired IS NOT NULL GROUP BY yearAcquired
            UNION ALL
            SELECT yearAcquired, COUNT(*) FROM tbl_monitor WHERE yearAcquired IS NOT NULL GROUP BY yearAcquired
            UNION ALL
            SELECT yearAcquired, COUNT(*) FROM tbl_printer WHERE yearAcquired IS NOT NULL GROUP BY yearAcquired
            UNION ALL
            SELECT yearAcquired, COUNT(*) FROM tbl_otherequipment WHERE yearAcquired IS NOT NULL GROUP BY yearAcquired
        ) combined GROUP BY year_acquired ORDER BY year_acquired DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Latest condition from maintenance records
    $conditionSummary = $db->query("
        SELECT conditionRating, COUNT(*) as cnt 
        FROM (
            SELECT mr.conditionRating,
                   ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
            FROM tbl_maintenance_record mr WHERE mr.conditionRating != ''
        ) latest WHERE rn = 1 GROUP BY conditionRating ORDER BY FIELD(conditionRating, 'Excellent','Good','Fair','Poor')
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Status from maintenance records
    $statusSummary = $db->query("
        SELECT overallStatus, COUNT(*) as cnt 
        FROM (
            SELECT mr.overallStatus,
                   ROW_NUMBER() OVER (PARTITION BY mr.scheduleId ORDER BY mr.maintenanceDate DESC) as rn
            FROM tbl_maintenance_record mr WHERE mr.overallStatus != ''
        ) latest WHERE rn = 1 GROUP BY overallStatus ORDER BY FIELD(overallStatus, 'Operational','For Replacement','Disposed')
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Software license summary
    $softTotal     = (int) $db->query("SELECT COUNT(*) FROM tbl_software")->fetchColumn();
    $softExpired   = (int) $db->query("SELECT COUNT(*) FROM tbl_software WHERE expiryDate IS NOT NULL AND expiryDate < CURDATE()")->fetchColumn();
    $softExpiring  = (int) $db->query("SELECT COUNT(*) FROM tbl_software WHERE expiryDate IS NOT NULL AND expiryDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 90 DAY)")->fetchColumn();
    $softPerpetual = (int) $db->query("SELECT COUNT(*) FROM tbl_software WHERE licenseType = 'Perpetual'")->fetchColumn();

} catch (PDOException $e) {
    error_log("Equipment summary error: " . $e->getMessage());
}
?>

<style>
.report-container { animation: fadeInUp 0.4s ease-out; }
.report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.report-header h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.report-header h2 i { color: var(--primary-green); }

.report-grid { display: grid; gap: 1.25rem; margin-bottom: 1.5rem; }
.report-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
.report-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
.report-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }

.rpt-stat {
    background: #fff; border-radius: var(--radius-xl); padding: 1.25rem 1.5rem;
    box-shadow: var(--shadow-md); border: 1px solid var(--border-color);
    display: flex; align-items: center; gap: 1rem;
}
.rpt-stat .rpt-icon {
    width: 44px; height: 44px; border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}
.rpt-stat .rpt-icon.green { background: rgba(34,197,94,0.1); color: #16a34a; }
.rpt-stat .rpt-icon.blue { background: rgba(59,130,246,0.1); color: #2563eb; }
.rpt-stat .rpt-icon.purple { background: rgba(139,92,246,0.1); color: #7c3aed; }
.rpt-stat .rpt-icon.orange { background: rgba(249,115,22,0.1); color: #ea580c; }
.rpt-stat .rpt-icon.red { background: rgba(239,68,68,0.1); color: #dc2626; }
.rpt-stat .rpt-icon.teal { background: rgba(20,184,166,0.1); color: #0d9488; }
.rpt-stat .rpt-val { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); font-family: var(--font-mono); }
.rpt-stat .rpt-lbl { font-size: 0.75rem; color: var(--text-medium); font-weight: 600; text-transform: uppercase; }

.rpt-panel {
    background: #fff; border-radius: var(--radius-xl); border: 1px solid var(--border-color);
    box-shadow: var(--shadow-md); overflow: hidden;
}
.rpt-panel-header {
    padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color);
    display: flex; justify-content: space-between; align-items: center;
}
.rpt-panel-header h3 { font-size: 0.95rem; font-weight: 700; color: var(--text-dark); margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.rpt-panel-header h3 i { color: var(--primary-green); font-size: 0.9rem; }
.rpt-panel-body { padding: 1rem 1.25rem; }

.rpt-table { width: 100%; border-collapse: collapse; }
.rpt-table thead th {
    padding: 0.65rem 0.75rem; font-size: 0.7rem; text-transform: uppercase;
    font-weight: 700; color: var(--text-medium); background: #f9fafb;
    border-bottom: 2px solid var(--border-color); text-align: left;
}
.rpt-table tbody td { padding: 0.6rem 0.75rem; font-size: 0.825rem; border-bottom: 1px solid #f3f4f6; }
.rpt-table tbody tr:last-child td { border-bottom: none; }
.rpt-table tfoot td { padding: 0.65rem 0.75rem; font-weight: 700; border-top: 2px solid var(--border-color); background: #f9fafb; }

.badge-sm { padding: 0.15rem 0.5rem; border-radius: 8px; font-size: 0.65rem; font-weight: 700; }
.bg-excellent { background: #dcfce7; color: #166534; }
.bg-good { background: #dbeafe; color: #1e40af; }
.bg-fair { background: #fef3c7; color: #92400e; }
.bg-poor { background: #fef2f2; color: #991b1b; }
.bg-operational { background: #dcfce7; color: #166534; }
.bg-replacement { background: #fef2f2; color: #991b1b; }
.bg-disposed { background: #f3f4f6; color: #6b7280; }

.print-btn {
    padding: 0.5rem 1rem; border-radius: var(--radius-md); border: none;
    background: var(--primary-green); color: #fff; font-weight: 600; font-size: 0.825rem;
    cursor: pointer; display: flex; align-items: center; gap: 0.35rem;
}
.print-btn:hover { background: var(--primary-dark); }

@media (max-width: 1200px) { .report-grid.cols-4 { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) { .report-grid.cols-4, .report-grid.cols-3, .report-grid.cols-2 { grid-template-columns: 1fr; } }
@media print {
    .report-header button, .print-btn { display: none !important; }
    .rpt-panel, .rpt-stat { box-shadow: none; border: 1px solid #ddd; }
}
</style>

<div class="report-container">
    <div class="report-header">
        <h2><i class="fas fa-boxes"></i> Equipment Summary Report</h2>
        <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>
    </div>

    <!-- Totals -->
    <div class="report-grid cols-4">
        <div class="rpt-stat">
            <div class="rpt-icon green"><i class="fas fa-cubes"></i></div>
            <div><div class="rpt-val"><?= number_format($total) ?></div><div class="rpt-lbl">Total Equipment</div></div>
        </div>
        <div class="rpt-stat">
            <div class="rpt-icon blue"><i class="fas fa-user-check"></i></div>
            <div><div class="rpt-val"><?= number_format($assigned) ?></div><div class="rpt-lbl">Assigned</div></div>
        </div>
        <div class="rpt-stat">
            <div class="rpt-icon orange"><i class="fas fa-box-open"></i></div>
            <div><div class="rpt-val"><?= number_format($unassigned) ?></div><div class="rpt-lbl">Unassigned</div></div>
        </div>
        <div class="rpt-stat">
            <div class="rpt-icon purple"><i class="fas fa-key"></i></div>
            <div><div class="rpt-val"><?= number_format($softTotal) ?></div><div class="rpt-lbl">Software Licenses</div></div>
        </div>
    </div>

    <!-- Type Breakdown -->
    <div class="report-grid cols-4" style="margin-bottom: 1.5rem;">
        <div class="rpt-stat"><div class="rpt-icon green"><i class="fas fa-server"></i></div><div><div class="rpt-val"><?= $suCount ?></div><div class="rpt-lbl">System Units</div></div></div>
        <div class="rpt-stat"><div class="rpt-icon blue"><i class="fas fa-tv"></i></div><div><div class="rpt-val"><?= $moCount ?></div><div class="rpt-lbl">Monitors</div></div></div>
        <div class="rpt-stat"><div class="rpt-icon purple"><i class="fas fa-print"></i></div><div><div class="rpt-val"><?= $prCount ?></div><div class="rpt-lbl">Printers</div></div></div>
        <div class="rpt-stat"><div class="rpt-icon teal"><i class="fas fa-laptop"></i></div><div><div class="rpt-val"><?= $aioCount + $othCount ?></div><div class="rpt-lbl">AIO & Other</div></div></div>
    </div>

    <div class="report-grid cols-2">
        <!-- By Division -->
        <div class="rpt-panel">
            <div class="rpt-panel-header"><h3><i class="fas fa-building"></i> Equipment by Division</h3></div>
            <div class="rpt-panel-body" style="padding: 0;">
                <table class="rpt-table">
                    <thead><tr><th>Division</th><th>SU</th><th>Monitors</th><th>Printers</th><th>AIO</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php $gTotal = 0; foreach ($byDivision as $div):
                            $dTotal = $div['system_units'] + $div['monitors'] + $div['printers'] + $div['allinones'];
                            $gTotal += $dTotal;
                        ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($div['division']) ?></td>
                            <td style="font-family: var(--font-mono);"><?= $div['system_units'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $div['monitors'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $div['printers'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $div['allinones'] ?></td>
                            <td style="font-family: var(--font-mono); font-weight: 700;"><?= $dTotal ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot><tr><td>Grand Total</td><td colspan="4"></td><td style="font-family: var(--font-mono);"><?= $gTotal ?></td></tr></tfoot>
                </table>
            </div>
        </div>

        <!-- Condition & Status -->
        <div class="rpt-panel">
            <div class="rpt-panel-header"><h3><i class="fas fa-heartbeat"></i> Equipment Health</h3></div>
            <div class="rpt-panel-body">
                <h4 style="font-size: 0.8rem; color: var(--text-medium); margin-bottom: 0.75rem; text-transform: uppercase;">Condition Rating</h4>
                <?php foreach ($conditionSummary as $cond):
                    $bgClass = 'bg-' . strtolower($cond['conditionRating']);
                ?>
                <div style="display: flex; align-items: center; margin-bottom: 0.5rem; gap: 0.75rem;">
                    <span class="badge-sm <?= $bgClass ?>" style="min-width: 70px; text-align: center;"><?= $cond['conditionRating'] ?></span>
                    <div style="flex: 1; height: 20px; background: #f3f4f6; border-radius: 10px; overflow: hidden;">
                        <?php $condTot = array_sum(array_column($conditionSummary, 'cnt')); $pct = $condTot > 0 ? round(($cond['cnt'] / $condTot) * 100) : 0; ?>
                        <div style="width: <?= max($pct, 3) ?>%; height: 100%; background: var(--primary-green); border-radius: 10px;"></div>
                    </div>
                    <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.85rem; min-width: 45px; text-align: right;"><?= $cond['cnt'] ?> (<?= $pct ?>%)</span>
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

    <div class="report-grid cols-2" style="margin-top: 1.5rem;">
        <!-- Year Acquired -->
        <div class="rpt-panel">
            <div class="rpt-panel-header"><h3><i class="fas fa-calendar"></i> Acquisition Timeline</h3></div>
            <div class="rpt-panel-body" style="padding: 0;">
                <table class="rpt-table">
                    <thead><tr><th>Year</th><th>Count</th><th>% of Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($byYear as $yr): $pct = $total > 0 ? round(($yr['total'] / $total) * 100, 1) : 0; ?>
                        <tr>
                            <td style="font-weight: 600; font-family: var(--font-mono);"><?= $yr['year_acquired'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $yr['total'] ?></td>
                            <td style="font-family: var(--font-mono);"><?= $pct ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Software Licenses -->
        <div class="rpt-panel">
            <div class="rpt-panel-header"><h3><i class="fas fa-key"></i> Software License Summary</h3></div>
            <div class="rpt-panel-body">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem;">
                    <div style="background: #f0fdf4; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-mono); color: #16a34a;"><?= $softTotal ?></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #16a34a; text-transform: uppercase;">Total</div>
                    </div>
                    <div style="background: #f0f9ff; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-mono); color: #2563eb;"><?= $softPerpetual ?></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #2563eb; text-transform: uppercase;">Perpetual</div>
                    </div>
                    <div style="background: #fffbeb; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-mono); color: #d97706;"><?= $softExpiring ?></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #d97706; text-transform: uppercase;">Expiring (90d)</div>
                    </div>
                    <div style="background: #fef2f2; border-radius: var(--radius-md); padding: 1rem; text-align: center;">
                        <div style="font-size: 1.5rem; font-weight: 800; font-family: var(--font-mono); color: #dc2626;"><?= $softExpired ?></div>
                        <div style="font-size: 0.7rem; font-weight: 600; color: #dc2626; text-transform: uppercase;">Expired</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
