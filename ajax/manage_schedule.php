<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$db = getDB();

try {
    // JOIN Schedule with Registry to get names
    // NOTE: This assumes your view_maintenance_master or registry is set up.
    // Simplified version:
    $sql = "
        SELECT 
            ms.scheduleId,
            ms.equipmentId,
            ms.nextDueDate,
            ms.lastMaintenanceDate,
            reg.typeName as type,
            DATEDIFF(ms.nextDueDate, CURDATE()) as days_due
        FROM tbl_maintenance_schedule ms
        JOIN tbl_equipment_type_registry reg ON ms.typeId = reg.typeId
        WHERE ms.isActive = 1
        ORDER BY ms.nextDueDate ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add 'status' logic in PHP for easy frontend coloring
    foreach($data as &$row) {
        if ($row['days_due'] < 0) $row['status'] = 'overdue';
        elseif ($row['days_due'] <= 7) $row['status'] = 'due_soon';
        else $row['status'] = 'scheduled';
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>