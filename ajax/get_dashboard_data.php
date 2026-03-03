<?php
/**
 * ajax/get_dashboard_data.php
 * Returns dashboard metrics and summary data.
 * Updated for unified tbl_equipment schema.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

try {
    // ---- Total equipment count by type ----
    $eqCountStmt = $db->query("
        SELECT r.typeName, COUNT(*) AS cnt
        FROM tbl_equipment eq
        INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
        WHERE eq.is_archived = 0
        GROUP BY r.typeId, r.typeName
    ");
    $typeCounts = $eqCountStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $totalEquipment = array_sum($typeCounts);

    // ---- Software count ----
    $swCount = (int) $db->query("SELECT COUNT(*) FROM tbl_software")->fetchColumn();

    // ---- Employee counts ----
    $empTotal    = (int) $db->query("SELECT COUNT(*) FROM tbl_employee WHERE is_archive = 0")->fetchColumn();
    $empArchived = (int) $db->query("SELECT COUNT(*) FROM tbl_employee WHERE is_archive = 1")->fetchColumn();

    // ---- Equipment by status ----
    $statusStmt = $db->query("
        SELECT status, COUNT(*) AS cnt
        FROM tbl_equipment
        WHERE is_archived = 0
        GROUP BY status
    ");
    $statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // ---- Equipment by division (top-level location) ----
    $divStmt = $db->query("
        SELECT 
            COALESCE(
                grandparent.location_name,
                parent.location_name,
                l.location_name,
                'Unassigned'
            ) AS division,
            COUNT(*) AS count
        FROM tbl_equipment eq
        LEFT JOIN location l            ON eq.location_id = l.location_id
        LEFT JOIN location parent       ON l.parent_location_id = parent.location_id
        LEFT JOIN location grandparent  ON parent.parent_location_id = grandparent.location_id
        WHERE eq.is_archived = 0
        GROUP BY division
        ORDER BY count DESC
    ");
    $equipmentByDivision = $divStmt->fetchAll(PDO::FETCH_ASSOC);

    // ---- Maintenance upcoming (next 30 days) ----
    $maintStmt = $db->query("
        SELECT COUNT(*) FROM tbl_maintenance_schedule
        WHERE nextMaintenanceDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
          AND isActive = 1
    ");
    $maintenanceDue = (int) $maintStmt->fetchColumn();

    // ---- Recently added equipment (last 10) ----
    $recentStmt = $db->query("
        SELECT eq.equipment_id, eq.brand, eq.model, eq.serial_number, eq.created_at,
               r.typeName
        FROM tbl_equipment eq
        INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
        WHERE eq.is_archived = 0
        ORDER BY eq.created_at DESC
        LIMIT 10
    ");
    $recentEquipment = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data'    => [
            'totalEquipment'     => $totalEquipment,
            'equipmentByType'    => $typeCounts,
            'totalSoftware'      => $swCount,
            'totalEmployees'     => $empTotal,
            'archivedEmployees'  => $empArchived,
            'statusCounts'       => $statusCounts,
            'equipmentByDivision'=> $equipmentByDivision,
            'maintenanceDue'     => $maintenanceDue,
            'recentEquipment'    => $recentEquipment,
        ],
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
