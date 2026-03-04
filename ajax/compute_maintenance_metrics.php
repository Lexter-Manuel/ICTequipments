<?php
/**
 * compute_maintenance_metrics.php
 * ────────────────────────────────
 * Computes per-equipment maintenance frequency metrics.
 *
require_once '../config/session-guard.php';
 * Can be called:
 *   - On-demand via GET ?action=compute (admin only)
 *   - After recording maintenance via POST with equipmentType + equipmentId
 *   - As a cron job
 *
 * Returns updated metrics and frequency suggestions.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../includes/maintenanceMetrics.php';
header('Content-Type: application/json');

$db = getDB();

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? 'compute_single';

    // ── Compute metrics for a SINGLE equipment ──
    if ($action === 'compute_single') {
        $input = json_decode(file_get_contents('php://input'), true);
        $typeId = $input['equipmentType'] ?? $_GET['equipmentType'] ?? null;
        $eqId   = $input['equipmentId']   ?? $_GET['equipmentId']   ?? null;

        if (!$typeId || !$eqId) {
            throw new Exception('equipmentType and equipmentId required');
        }

        $metrics = computeEquipmentMetrics($db, $typeId, $eqId);
        echo json_encode(['success' => true, 'data' => $metrics]);
        exit;
    }

    // ── Compute metrics for ALL equipment with records ──
    if ($action === 'compute') {
        // Get distinct equipment from maintenance records
        $stmt = $db->query("
            SELECT DISTINCT equipmentTypeId, equipmentId 
            FROM tbl_maintenance_record
            ORDER BY equipmentTypeId, equipmentId
        ");
        $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $updated = 0;
        foreach ($equipment as $eq) {
            computeEquipmentMetrics($db, $eq['equipmentTypeId'], $eq['equipmentId']);
            $updated++;
        }

        echo json_encode([
            'success' => true,
            'message' => "Computed metrics for {$updated} equipment.",
            'updated' => $updated
        ]);
        exit;
    }

    // ── Get metrics summary (for dashboard / reports) ──
    if ($action === 'summary') {
        $stmt = $db->query("
            SELECT 
                m.equipmentType,
                m.equipmentId,
                m.avg_interval_days,
                m.total_records,
                m.off_schedule_count,
                m.suggested_frequency,
                ms.maintenanceFrequency AS current_frequency,
                r.typeName,
                v.brand,
                v.serial,
                v.location_name
            FROM tbl_maintenance_metrics m
            JOIN tbl_maintenance_schedule ms 
              ON ms.equipmentType = m.equipmentType AND ms.equipmentId = m.equipmentId AND ms.isActive = 1
            JOIN tbl_equipment_type_registry r ON m.equipmentType = r.typeId
            LEFT JOIN view_maintenance_master v ON m.equipmentType = v.type_id AND m.equipmentId = v.id
            WHERE m.suggested_frequency IS NOT NULL 
              AND m.suggested_frequency != ms.maintenanceFrequency
            ORDER BY m.off_schedule_count DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    throw new Exception('Invalid action. Use: compute_single, compute, or summary');

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
