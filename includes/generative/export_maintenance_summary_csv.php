<?php
/**
 * Maintenance Summary — Excel Export
 * Exports all maintenance summary data as a styled .xls file.
 */
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // ---------- Filters ----------
    $filterDivision = isset($_GET['division']) ? trim($_GET['division']) : '';
    $filterEqType   = isset($_GET['eq_type'])  ? trim($_GET['eq_type'])  : '';
    $filterDateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
    $filterDateTo   = isset($_GET['date_to'])   ? trim($_GET['date_to'])   : '';

    // Load all queries from shared include
    require_once __DIR__ . '/../../includes/queries/maintenance_summary_queries.php';

    // ---------- Excel Output ----------
    $filename = 'Maintenance_Summary_' . date('Y-m-d_His') . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Build active filter text
    $filterText = '';
    if ($filterDivision !== '' || $filterEqType !== '' || $filterDateFrom !== '' || $filterDateTo !== '') {
        $parts = [];
        if ($filterDivision !== '') {
            $divName = '';
            foreach ($divisions as $d) { if ($d['location_id'] == $filterDivision) { $divName = $d['location_name']; break; } }
            $parts[] = 'Division: ' . htmlspecialchars($divName);
        }
        if ($filterEqType !== '') {
            $etName = '';
            foreach ($equipmentTypes as $et) { if ($et['typeId'] == $filterEqType) { $etName = $et['typeName']; break; } }
            $parts[] = 'Type: ' . htmlspecialchars($etName);
        }
        if ($filterDateFrom !== '') $parts[] = 'From: ' . htmlspecialchars($filterDateFrom);
        if ($filterDateTo   !== '') $parts[] = 'To: ' . htmlspecialchars($filterDateTo);
        $filterText = implode('  |  ', $parts);
    }

    $e = function($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); };
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<!--[if gte mso 9]><xml>
<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
<x:Name>Maintenance Summary</x:Name>
<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
</xml><![endif]-->
<style>
    body { font-family: Calibri, Arial, sans-serif; }
    .title { font-size: 16pt; font-weight: bold; color: #1a5632; padding: 8px 4px; }
    .subtitle { font-size: 10pt; color: #666; padding: 4px; }
    .filter-info { font-size: 9pt; color: #888; padding: 4px; font-style: italic; }
    .section-header td {
        font-size: 11pt; font-weight: bold; color: #fff; background: #1a5632;
        padding: 6px 10px; border: 1px solid #146b3a;
    }
    .th {
        font-weight: bold; background: #e8f5e9; color: #1a5632;
        padding: 6px 12px; border: 1px solid #c8e6c9; font-size: 10pt;
        text-align: left;
    }
    .th-right { text-align: right; }
    .td {
        padding: 5px 12px; border: 1px solid #e0e0e0; font-size: 10pt;
        vertical-align: middle;
    }
    .td-num { text-align: right; font-family: Consolas, monospace; }
    .td-bold { font-weight: bold; }
    .footer-row td {
        font-weight: bold; background: #f5f5f5; padding: 5px 12px;
        border: 1px solid #e0e0e0; font-size: 10pt;
    }
    .spacer td { height: 20px; border: none; }
    .metric-label { padding: 5px 12px; border: 1px solid #e0e0e0; font-size: 10pt; font-weight: 600; background: #fafafa; }
    .metric-val { padding: 5px 12px; border: 1px solid #e0e0e0; font-size: 10pt; font-weight: bold; text-align: right; font-family: Consolas, monospace; }
    .badge-excellent { background: #dcfce7; color: #166534; padding: 3px 8px; font-weight: 600; }
    .badge-good { background: #dbeafe; color: #1e40af; padding: 3px 8px; font-weight: 600; }
    .badge-fair { background: #fef9c3; color: #854d0e; padding: 3px 8px; font-weight: 600; }
    .badge-poor { background: #fee2e2; color: #991b1b; padding: 3px 8px; font-weight: 600; }
    .overdue-high { color: #dc2626; font-weight: bold; }
    .overdue-med { color: #ea580c; font-weight: bold; }
    .overdue-low { color: #d97706; font-weight: bold; }
</style>
</head>
<body>

<table>
    <tr><td class="title" colspan="5">Maintenance Summary Report</td></tr>
    <tr><td class="subtitle" colspan="5">Generated: <?= date('F d, Y — h:i A') ?></td></tr>
    <?php if ($filterText): ?>
    <tr><td class="filter-info" colspan="5">Filters: <?= $filterText ?></td></tr>
    <?php endif; ?>
    <tr class="spacer"><td colspan="5"></td></tr>

    <!-- KEY METRICS -->
    <tr class="section-header"><td colspan="5">Key Metrics</td></tr>
    <tr><td class="th">Metric</td><td class="th th-right">Value</td><td colspan="3"></td></tr>
    <tr><td class="metric-label">Active Schedules</td><td class="metric-val"><?= number_format($activeSchedules) ?></td><td colspan="3"></td></tr>
    <tr><td class="metric-label">Total Schedules</td><td class="metric-val"><?= number_format($totalSchedules) ?></td><td colspan="3"></td></tr>
    <tr><td class="metric-label">Overdue</td><td class="metric-val"><?= number_format($overdue) ?></td><td colspan="3"></td></tr>
    <tr><td class="metric-label">Due Soon (7 days)</td><td class="metric-val"><?= number_format($dueSoon) ?></td><td colspan="3"></td></tr>
    <tr><td class="metric-label">Total Records</td><td class="metric-val"><?= number_format($totalRecords) ?></td><td colspan="3"></td></tr>
    <tr><td class="metric-label">Records This Month</td><td class="metric-val"><?= number_format($thisMonthRecords) ?></td><td colspan="3"></td></tr>
    <tr><td class="metric-label">Due This Month</td><td class="metric-val"><?= number_format($dueThisMonth) ?></td><td colspan="3"></td></tr>
    <tr><td class="metric-label">Completed This Month</td><td class="metric-val"><?= number_format($completedThisMonth) ?></td><td colspan="3"></td></tr>
    <tr><td class="metric-label">Compliance Rate</td><td class="metric-val"><?= $compliance ?>%</td><td colspan="3"></td></tr>
    <tr class="spacer"><td colspan="5"></td></tr>

    <!-- MONTHLY ACTIVITY -->
    <?php if (!empty($monthly)): ?>
    <tr class="section-header"><td colspan="5">Monthly Maintenance Activity (Last 12 Months)</td></tr>
    <tr><td class="th">Month</td><td class="th th-right">Records</td><td colspan="3"></td></tr>
    <?php foreach ($monthly as $m): ?>
    <tr>
        <td class="td td-bold"><?= $e($m['label']) ?></td>
        <td class="td td-num"><?= $m['cnt'] ?></td>
        <td colspan="3"></td>
    </tr>
    <?php endforeach; ?>
    <tr class="spacer"><td colspan="5"></td></tr>
    <?php endif; ?>

    <!-- BY MAINTENANCE TYPE -->
    <?php if (!empty($byType)): ?>
    <tr class="section-header"><td colspan="5">Records by Maintenance Type</td></tr>
    <tr><td class="th">Maintenance Type</td><td class="th th-right">Records</td><td colspan="3"></td></tr>
    <?php foreach ($byType as $t): ?>
    <tr>
        <td class="td td-bold"><?= $e($t['templateName'] ?? 'Unspecified') ?></td>
        <td class="td td-num"><?= $t['cnt'] ?></td>
        <td colspan="3"></td>
    </tr>
    <?php endforeach; ?>
    <tr class="spacer"><td colspan="5"></td></tr>
    <?php endif; ?>

    <!-- CONDITION RATING -->
    <?php if (!empty($conditionSummary)): ?>
    <tr class="section-header"><td colspan="5">Equipment Condition Rating</td></tr>
    <tr><td class="th">Condition</td><td class="th th-right">Count</td><td class="th th-right">Percentage</td><td colspan="2"></td></tr>
    <?php $condTot = array_sum(array_column($conditionSummary, 'cnt'));
    foreach ($conditionSummary as $cond):
        $pct = $condTot > 0 ? round(($cond['cnt'] / $condTot) * 100, 1) : 0;
        $badge = 'badge-' . strtolower($cond['conditionRating']);
    ?>
    <tr>
        <td class="td"><span class="<?= $badge ?>"><?= $e($cond['conditionRating']) ?></span></td>
        <td class="td td-num"><?= $cond['cnt'] ?></td>
        <td class="td td-num"><?= $pct ?>%</td>
        <td colspan="2"></td>
    </tr>
    <?php endforeach; ?>
    <tr class="spacer"><td colspan="5"></td></tr>
    <?php endif; ?>

    <!-- STATUS -->
    <?php if (!empty($statusSummary)): ?>
    <tr class="section-header"><td colspan="5">Equipment Status</td></tr>
    <tr><td class="th">Status</td><td class="th th-right">Count</td><td colspan="3"></td></tr>
    <?php foreach ($statusSummary as $stat): ?>
    <tr>
        <td class="td td-bold"><?= $e($stat['overallStatus']) ?></td>
        <td class="td td-num"><?= $stat['cnt'] ?></td>
        <td colspan="3"></td>
    </tr>
    <?php endforeach; ?>
    <tr class="spacer"><td colspan="5"></td></tr>
    <?php endif; ?>

    <!-- OVERDUE LIST -->
    <?php if (!empty($overdueList)): ?>
    <tr class="section-header"><td colspan="5">Overdue Maintenance</td></tr>
    <tr>
        <td class="th">Equipment</td>
        <td class="th">Type</td>
        <td class="th">Frequency</td>
        <td class="th">Due Date</td>
        <td class="th th-right">Days Overdue</td>
    </tr>
    <?php foreach ($overdueList as $od):
        $days = $od['days_overdue'];
        $cls = $days > 30 ? 'overdue-high' : ($days > 14 ? 'overdue-med' : 'overdue-low');
    ?>
    <tr>
        <td class="td td-bold"><?= $e($od['equipment_name'] ?? 'Unknown') ?></td>
        <td class="td"><?= $e($od['equipment_type'] ?? '') ?></td>
        <td class="td"><?= $e($od['maintenanceFrequency'] ?? '') ?></td>
        <td class="td"><?= date('M d, Y', strtotime($od['nextDueDate'])) ?></td>
        <td class="td td-num <?= $cls ?>"><?= $days ?> days</td>
    </tr>
    <?php endforeach; ?>
    <tr class="spacer"><td colspan="5"></td></tr>
    <?php endif; ?>

    <!-- TECHNICIANS -->
    <?php if (!empty($technicians)): ?>
    <tr class="section-header"><td colspan="5">Maintenance Technicians</td></tr>
    <tr>
        <td class="th">#</td>
        <td class="th">Technician</td>
        <td class="th th-right">Records</td>
        <td class="th th-right">Avg Rating</td>
        <td></td>
    </tr>
    <?php foreach ($technicians as $i => $tech): ?>
    <tr>
        <td class="td td-num"><?= $i + 1 ?></td>
        <td class="td td-bold"><?= $e($tech['preparedBy']) ?></td>
        <td class="td td-num"><?= $tech['cnt'] ?></td>
        <td class="td td-num"><?= $tech['avg_rating'] ?></td>
        <td></td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
</table>

</body>
</html>
<?php
    exit;

} catch (Exception $ex) {
    error_log("Maintenance Excel export error: " . $ex->getMessage());
    header('Content-Type: text/plain');
    echo "Error generating Excel export. Please try again.";
    exit(1);
}
