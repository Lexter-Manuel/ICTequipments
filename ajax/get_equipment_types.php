<?php
require_once '../config/database.php';
header('Content-Type: application/json');
$db = getDB();

try {
    // Fetch only types that are actually defined in your registry
    $stmt = $db->query("SELECT typeId, typeName FROM tbl_equipment_type_registry ORDER BY typeName ASC");
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $types]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>