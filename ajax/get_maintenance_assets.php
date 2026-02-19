<?php
require_once '../config/database.php';
header('Content-Type: application/json');
$db = getDB();

$typeId = $_GET['type'] ?? '';

if ($typeId) {
    try {
        // MAGIC QUERY: Join Schedule with the Master View
        // This works for ANY equipment type (System Unit, CCTV, Mouse, etc.) automatically.
        $sql = "
            SELECT 
                ms.scheduleId, 
                ms.equipmentId, 
                ms.maintenanceFrequency, 
                ms.nextDueDate,
                v.brand as name,
                v.serial as serial,
                v.location_name,
                v.owner_name,       -- ADDED THIS LINE
                v.type_name         -- ADDED THIS LINE (Useful for display)
            FROM tbl_maintenance_schedule ms
            JOIN view_maintenance_master v 
              ON ms.equipmentType = v.type_id 
              AND ms.equipmentId = v.id
            WHERE ms.equipmentType = ? 
              AND ms.isActive = 1
            ORDER BY ms.nextDueDate ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$typeId]); 
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $data]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'data' => []]);
}
?>