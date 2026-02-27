<?php
/**
 * maintenanceMetrics.php
 * ──────────────────────
 * Pure library function for computing per-equipment maintenance metrics.
 * No side effects (no session, no headers, no output).
 * Safe to require_once from any context.
 */

/**
 * Compute maintenance metrics for a single piece of equipment.
 *
 * - Calculates average interval between maintenance events
 * - Counts off-schedule occurrences (>7 days from scheduled date)
 * - Suggests optimal frequency based on actual patterns
 * - Upserts result into tbl_maintenance_metrics
 *
 * @param PDO    $db          Database connection
 * @param mixed  $typeId      Equipment type ID (from registry)
 * @param int    $equipmentId Equipment primary key
 * @return array  Computed metrics
 */
function computeEquipmentMetrics($db, $typeId, $equipmentId) {
    // Get all maintenance records for this equipment, ordered by date
    $stmt = $db->prepare("
        SELECT 
            mr.recordId,
            mr.maintenanceDate,
            mr.scheduleId
        FROM tbl_maintenance_record mr
        WHERE mr.equipmentTypeId = ? AND mr.equipmentId = ?
        ORDER BY mr.maintenanceDate ASC
    ");
    $stmt->execute([$typeId, $equipmentId]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalRecords = count($records);

    if ($totalRecords < 1) {
        return ['total_records' => 0, 'suggested_frequency' => null];
    }

    // Calculate intervals between consecutive maintenance events
    $intervals = [];
    $offScheduleCount = 0;

    for ($i = 1; $i < $totalRecords; $i++) {
        $prev = strtotime($records[$i - 1]['maintenanceDate']);
        $curr = strtotime($records[$i]['maintenanceDate']);
        $daysBetween = ($curr - $prev) / 86400;

        if ($daysBetween > 0) {
            $intervals[] = $daysBetween;
        }
    }

    // Check how many times maintenance was done significantly off-schedule
    foreach ($records as $rec) {
        if (!empty($rec['scheduleId'])) {
            $stmtSched = $db->prepare(
                "SELECT nextDueDate FROM tbl_maintenance_schedule WHERE scheduleId = ?"
            );
            $stmtSched->execute([$rec['scheduleId']]);
            $schedDate = $stmtSched->fetchColumn();

            if ($schedDate) {
                $diff = abs(strtotime($rec['maintenanceDate']) - strtotime($schedDate));
                if ($diff > 7 * 86400) {
                    $offScheduleCount++;
                }
            }
        }
    }

    // Average interval
    $avgInterval = count($intervals) > 0 ? array_sum($intervals) / count($intervals) : null;

    // Suggest frequency based on average interval
    $suggested = null;
    if ($avgInterval !== null) {
        if ($avgInterval < 45) {
            $suggested = 'Monthly';
        } elseif ($avgInterval < 120) {
            $suggested = 'Quarterly';
        } elseif ($avgInterval < 270) {
            $suggested = 'Semi-Annual';
        } else {
            $suggested = 'Annual';
        }
    }

    // Upsert into metrics table
    $stmtUpsert = $db->prepare("
        INSERT INTO tbl_maintenance_metrics 
        (equipmentType, equipmentId, avg_interval_days, total_records, off_schedule_count, suggested_frequency)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            avg_interval_days = VALUES(avg_interval_days),
            total_records = VALUES(total_records),
            off_schedule_count = VALUES(off_schedule_count),
            suggested_frequency = VALUES(suggested_frequency),
            last_computed = CURRENT_TIMESTAMP
    ");
    $stmtUpsert->execute([
        $typeId, $equipmentId,
        $avgInterval ? round($avgInterval, 1) : null,
        $totalRecords,
        $offScheduleCount,
        $suggested
    ]);

    return [
        'equipmentType'       => $typeId,
        'equipmentId'         => $equipmentId,
        'avg_interval_days'   => $avgInterval ? round($avgInterval, 1) : null,
        'total_records'       => $totalRecords,
        'off_schedule_count'  => $offScheduleCount,
        'suggested_frequency' => $suggested
    ];
}
