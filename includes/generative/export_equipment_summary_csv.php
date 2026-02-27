<?php
/**
 * Equipment Summary — Excel Export
 * Exports all equipment summary data as a styled .xls file.
 */
require_once __DIR__ . '/../../config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // ---------- Filters ----------
    $filterDivision = isset($_GET['division']) ? trim($_GET['division']) : '';
    $filterEqType   = isset($_GET['eq_type'])  ? trim($_GET['eq_type'])  : '';
    $filterYear     = isset($_GET['year'])      ? trim($_GET['year'])     : '';

    // Load all queries from shared include
    require_once __DIR__ . '/../../includes/queries/equipment_summary_queries.php';

    // ---------- Excel Output ----------
    $filename = 'Equipment_Summary_' . date('Y-m-d_His') . '.xls';

    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Build active filter text
    $filterText = '';
    if ($filterDivision !== '' || $filterEqType !== '' || $filterYear !== '') {
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
        if ($filterYear !== '') $parts[] = 'Year: ' . htmlspecialchars($filterYear);
        $filterText = implode('  |  ', $parts);
    }

    $e = function($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); };
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<!--[if gte mso 9]><xml>
<x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
<x:Name>Equipment Summary</x:Name>
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
</style>
</head>
<body>

<table>
    <tr><td class="title" colspan="6">Equipment Summary Report</td></tr>
    <tr><td class="subtitle" colspan="6">Generated: <?= date('F d, Y — h:i A') ?></td></tr>
    <?php if ($filterText): ?>
    <tr><td class="filter-info" colspan="6">Filters: <?= $filterText ?></td></tr>
    <?php endif; ?>
    <tr class="spacer"><td colspan="6"></td></tr>

    <!-- OVERVIEW -->
    <tr class="section-header"><td colspan="6">Overview</td></tr>
    <tr><td class="th">Metric</td><td class="th th-right">Count</td><td colspan="4"></td></tr>
    <tr><td class="metric-label">Total Equipment</td><td class="metric-val"><?= number_format($total) ?></td><td colspan="4"></td></tr>
    <tr><td class="metric-label">Assigned</td><td class="metric-val"><?= number_format($assigned) ?></td><td colspan="4"></td></tr>
    <tr><td class="metric-label">Unassigned</td><td class="metric-val"><?= number_format($unassigned) ?></td><td colspan="4"></td></tr>
    <tr><td class="metric-label">Software Licenses</td><td class="metric-val"><?= number_format($softTotal) ?></td><td colspan="4"></td></tr>
    <tr class="spacer"><td colspan="6"></td></tr>

    <!-- TYPE BREAKDOWN -->
    <tr class="section-header"><td colspan="6">Equipment Type Breakdown</td></tr>
    <tr><td class="th">Type</td><td class="th th-right">Count</td><td colspan="4"></td></tr>
    <?php if ($showSU): ?><tr><td class="td">System Units</td><td class="td td-num"><?= number_format($suCount) ?></td><td colspan="4"></td></tr><?php endif; ?>
    <?php if ($showMO): ?><tr><td class="td">Monitors</td><td class="td td-num"><?= number_format($moCount) ?></td><td colspan="4"></td></tr><?php endif; ?>
    <?php if ($showPR): ?><tr><td class="td">Printers</td><td class="td td-num"><?= number_format($prCount) ?></td><td colspan="4"></td></tr><?php endif; ?>
    <?php if ($showAIO): ?><tr><td class="td">All-in-One</td><td class="td td-num"><?= number_format($aioCount) ?></td><td colspan="4"></td></tr><?php endif; ?>
    <?php if ($showOTH): ?><tr><td class="td">Other Equipment</td><td class="td td-num"><?= number_format($othCount) ?></td><td colspan="4"></td></tr><?php endif; ?>
    <tr class="spacer"><td colspan="6"></td></tr>

    <!-- BY DIVISION -->
    <tr class="section-header"><td colspan="6">Equipment by Division</td></tr>
    <tr>
        <td class="th">Division</td>
        <td class="th th-right">System Units</td>
        <td class="th th-right">Monitors</td>
        <td class="th th-right">Printers</td>
        <td class="th th-right">All-in-One</td>
        <td class="th th-right">Total</td>
    </tr>
    <?php $gTotal = 0; foreach ($byDivision as $div):
        $dTotal = $div['system_units'] + $div['monitors'] + $div['printers'] + $div['allinones'];
        $gTotal += $dTotal;
    ?>
    <tr>
        <td class="td td-bold"><?= $e($div['division']) ?></td>
        <td class="td td-num"><?= $div['system_units'] ?></td>
        <td class="td td-num"><?= $div['monitors'] ?></td>
        <td class="td td-num"><?= $div['printers'] ?></td>
        <td class="td td-num"><?= $div['allinones'] ?></td>
        <td class="td td-num td-bold"><?= $dTotal ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="footer-row">
        <td>Grand Total</td><td class="td-num">&nbsp;</td><td class="td-num">&nbsp;</td>
        <td class="td-num">&nbsp;</td><td class="td-num">&nbsp;</td><td class="td-num"><?= $gTotal ?></td>
    </tr>
    <tr class="spacer"><td colspan="6"></td></tr>

    <!-- CONDITION RATING -->
    <tr class="section-header"><td colspan="6">Equipment Condition Rating</td></tr>
    <tr><td class="th">Condition</td><td class="th th-right">Count</td><td class="th th-right">Percentage</td><td colspan="3"></td></tr>
    <?php $condTot = array_sum(array_column($conditionSummary, 'cnt'));
    foreach ($conditionSummary as $cond):
        $pct = $condTot > 0 ? round(($cond['cnt'] / $condTot) * 100, 1) : 0;
        $badge = 'badge-' . strtolower($cond['conditionRating']);
    ?>
    <tr>
        <td class="td"><span class="<?= $badge ?>"><?= $e($cond['conditionRating']) ?></span></td>
        <td class="td td-num"><?= $cond['cnt'] ?></td>
        <td class="td td-num"><?= $pct ?>%</td>
        <td colspan="3"></td>
    </tr>
    <?php endforeach; ?>
    <tr class="spacer"><td colspan="6"></td></tr>

    <!-- STATUS -->
    <tr class="section-header"><td colspan="6">Equipment Status</td></tr>
    <tr><td class="th">Status</td><td class="th th-right">Count</td><td colspan="4"></td></tr>
    <?php foreach ($statusSummary as $stat): ?>
    <tr>
        <td class="td td-bold"><?= $e($stat['overallStatus']) ?></td>
        <td class="td td-num"><?= $stat['cnt'] ?></td>
        <td colspan="4"></td>
    </tr>
    <?php endforeach; ?>
    <tr class="spacer"><td colspan="6"></td></tr>

    <!-- ACQUISITION TIMELINE -->
    <tr class="section-header"><td colspan="6">Acquisition Timeline</td></tr>
    <tr><td class="th">Year</td><td class="th th-right">Count</td><td class="th th-right">% of Total</td><td colspan="3"></td></tr>
    <?php foreach ($byYear as $yr):
        $pct = $total > 0 ? round(($yr['total'] / $total) * 100, 1) : 0;
    ?>
    <tr>
        <td class="td td-bold"><?= $e($yr['year_acquired']) ?></td>
        <td class="td td-num"><?= $yr['total'] ?></td>
        <td class="td td-num"><?= $pct ?>%</td>
        <td colspan="3"></td>
    </tr>
    <?php endforeach; ?>
    <tr class="spacer"><td colspan="6"></td></tr>

    <!-- SOFTWARE LICENSES -->
    <tr class="section-header"><td colspan="6">Software License Summary</td></tr>
    <tr><td class="th">Metric</td><td class="th th-right">Count</td><td colspan="4"></td></tr>
    <tr><td class="metric-label">Total Licenses</td><td class="metric-val"><?= number_format($softTotal) ?></td><td colspan="4"></td></tr>
    <tr><td class="metric-label">Perpetual</td><td class="metric-val"><?= number_format($softPerpetual) ?></td><td colspan="4"></td></tr>
    <tr><td class="metric-label">Expiring (90 days)</td><td class="metric-val"><?= number_format($softExpiring) ?></td><td colspan="4"></td></tr>
    <tr><td class="metric-label">Expired</td><td class="metric-val"><?= number_format($softExpired) ?></td><td colspan="4"></td></tr>
</table>

</body>
</html>
<?php
    exit;

} catch (Exception $ex) {
    error_log("Equipment Excel export error: " . $ex->getMessage());
    header('Content-Type: text/plain');
    echo "Error generating Excel export. Please try again.";
    exit(1);
}
