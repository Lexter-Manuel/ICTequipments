<?php
/**
 * ajax/get_unassigned_equipment.php
 * Returns equipment not assigned to any employee, optionally filtered by type.
 * Updated for unified tbl_equipment schema.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

$typeId = filter_input(INPUT_GET, 'type_id', FILTER_VALIDATE_INT);
$typeName = trim($_GET['type'] ?? '');
$search = trim($_GET['search'] ?? '');

try {
    $where = "eq.employee_id IS NULL AND eq.is_archived = 0";
    $params = [];

    // Filter by type_id or type name
    if ($typeId) {
        $where .= " AND eq.type_id = :type_id";
        $params[':type_id'] = $typeId;
    } elseif ($typeName !== '') {
        $where .= " AND r.typeName = :typeName";
        $params[':typeName'] = $typeName;
    }

    // Optional search
    if ($search !== '') {
        $where .= " AND (eq.brand LIKE :search OR eq.model LIKE :search OR eq.serial_number LIKE :search OR eq.property_number LIKE :search)";
        $params[':search'] = "%$search%";
    }

    $sql = "
        SELECT eq.equipment_id, eq.type_id, eq.brand, eq.model, eq.serial_number,
               eq.property_number, eq.status, eq.year_acquired, eq.acquisition_date,
               r.typeName, r.context
        FROM tbl_equipment eq
        INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
        WHERE $where
        ORDER BY r.typeName, eq.brand, eq.model
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Bulk load specs
    $ids = array_column($equipment, 'equipment_id');
    $specsMap = [];
    if (!empty($ids)) {
        $ph = implode(',', array_fill(0, count($ids), '?'));
        $specStmt = $db->prepare("SELECT equipment_id, spec_key, spec_value FROM tbl_equipment_specs WHERE equipment_id IN ($ph)");
        $specStmt->execute($ids);
        while ($row = $specStmt->fetch(PDO::FETCH_ASSOC)) {
            $specsMap[$row['equipment_id']][$row['spec_key']] = $row['spec_value'];
        }
    }

    foreach ($equipment as &$eq) {
        $eq['specs'] = $specsMap[$eq['equipment_id']] ?? [];
    }
    unset($eq);

    echo json_encode(['success' => true, 'equipment' => $equipment]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
