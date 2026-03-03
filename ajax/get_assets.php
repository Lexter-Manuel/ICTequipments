<?php
/**
 * ajax/get_assets.php
 * Returns equipment assets, optionally filtered by type name.
 * Updated for unified tbl_equipment schema.
 */
require_once '../config/database.php';
header('Content-Type: application/json');

$db = getDB();
$type = $_GET['type'] ?? '';

try {
    // Map legacy dropdown values to typeName in the registry
    $typeMap = [
        'system_unit' => 'System Unit',
        'printer'     => 'Printer',
        'laptop'      => 'Laptop',
        'monitor'     => 'Monitor',
        'allinone'    => 'All-in-One',
    ];

    $where = "eq.is_archived = 0";
    $params = [];

    if ($type !== '' && isset($typeMap[$type])) {
        $where .= " AND r.typeName = :typeName";
        $params[':typeName'] = $typeMap[$type];
    } elseif ($type !== '') {
        // Allow passing a raw typeName
        $where .= " AND r.typeName = :typeName";
        $params[':typeName'] = $type;
    }

    $sql = "
        SELECT eq.equipment_id AS id,
               eq.brand AS name,
               eq.serial_number AS serial,
               CASE WHEN eq.employee_id IS NOT NULL 
                    THEN CONCAT('Owned by: ', eq.employee_id)
                    ELSE 'Unassigned'
               END AS location_info,
               r.typeName AS type
        FROM tbl_equipment eq
        INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
        WHERE $where
        ORDER BY r.typeName, eq.brand
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($assets);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>