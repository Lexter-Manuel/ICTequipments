<?php
/**
 * ajax/get_unassigned_equipment.php
 * Returns a list of items with employeeId IS NULL for the given type.
 */
require_once '../config/session-guard.php';

if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$db   = Database::getInstance()->getConnection();
$type = $_GET['type'] ?? '';

try {
    switch ($type) {
        case 'systemunit':
            $stmt = $db->query("
                SELECT e.equipment_id AS id,
                       e.brand,
                       e.serial_number AS serial,
                       CONCAT_WS(' / ', 
                           NULLIF(MAX(CASE WHEN s.spec_key = 'Processor' THEN s.spec_value END), ''),
                           NULLIF(MAX(CASE WHEN s.spec_key = 'Memory' THEN s.spec_value END), '')
                       ) AS extra
                FROM tbl_equipment e
                LEFT JOIN tbl_equipment_specs s ON e.equipment_id = s.equipment_id
                WHERE e.type_id = 1 AND e.employee_id IS NULL AND e.status = 'Available'
                GROUP BY e.equipment_id, e.brand, e.serial_number
                ORDER BY e.brand ASC
            ");
            break;

        case 'allinone':
            $stmt = $db->query("
                SELECT e.equipment_id AS id,
                       e.brand,
                       e.serial_number AS serial,
                       CONCAT_WS(' / ', 
                           NULLIF(MAX(CASE WHEN s.spec_key = 'Processor' THEN s.spec_value END), ''),
                           NULLIF(MAX(CASE WHEN s.spec_key = 'Memory' THEN s.spec_value END), '')
                       ) AS extra
                FROM tbl_equipment e
                LEFT JOIN tbl_equipment_specs s ON e.equipment_id = s.equipment_id
                WHERE e.type_id = 2 AND e.employee_id IS NULL AND e.status = 'Available'
                GROUP BY e.equipment_id, e.brand, e.serial_number
                ORDER BY e.brand ASC
            ");
            break;

        case 'monitor':
            $stmt = $db->query("
                SELECT e.equipment_id AS id,
                       e.brand,
                       e.serial_number AS serial,
                       MAX(CASE WHEN s.spec_key = 'Monitor Size' THEN s.spec_value END) AS extra
                FROM tbl_equipment e
                LEFT JOIN tbl_equipment_specs s ON e.equipment_id = s.equipment_id
                WHERE e.type_id = 3 AND e.employee_id IS NULL AND e.status = 'Available'
                GROUP BY e.equipment_id, e.brand, e.serial_number
                ORDER BY e.brand ASC
            ");
            break;

        case 'printer':
            $stmt = $db->query("
                SELECT equipment_id AS id,
                       brand,
                       serial_number AS serial,
                       model AS model,
                       NULL AS extra
                FROM tbl_equipment
                WHERE type_id = 4 AND employee_id IS NULL AND status = 'Available'
                ORDER BY brand ASC
            ");
            break;

        case 'otherequipment':
            $stmt = $db->query("
                SELECT otherEquipmentId AS id,
                       CONCAT(equipmentType, IFNULL(CONCAT(' — ', brand), '')) AS brand,
                       serialNumber AS serial,
                       model AS model,
                       NULL AS extra
                FROM tbl_otherequipment
                WHERE employeeId IS NULL AND status = 'Available'
                ORDER BY equipmentType ASC
            ");
            break;

        case 'software':
            $stmt = $db->query("
                SELECT softwareId AS id,
                       licenseSoftware AS brand,
                       NULL AS serial,
                       licenseDetails AS extra
                FROM tbl_software
                WHERE employeeId IS NULL
                ORDER BY licenseSoftware ASC
            ");
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown type.']);
            exit;
    }

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'items' => $items]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
}