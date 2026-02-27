<?php
require_once __DIR__ . '/../../config/database.php';

$db = Database::getInstance()->getConnection();

$sql = "
CREATE OR REPLACE VIEW view_maintenance_master AS
SELECT 'System Unit' AS type_name, r.typeId AS type_id, s.systemunitId AS id, s.systemUnitBrand AS brand, s.systemUnitSerial AS serial, CONCAT(e.firstName,' ',e.lastName) AS owner_name, l.location_name AS location_name, 'Employee' AS context
FROM tbl_systemunit s
JOIN tbl_equipment_type_registry r ON r.tableName = 'tbl_systemunit'
LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId
LEFT JOIN location l ON e.location_id = l.location_id

UNION ALL

SELECT 'All-in-One' AS type_name, r.typeId AS type_id, a.allinoneId AS id, a.allinoneBrand AS brand, COALESCE(a.allinoneSerial, 'N/A') AS serial, CONCAT(e.firstName,' ',e.lastName) AS owner_name, l.location_name AS location_name, 'Employee' AS context
FROM tbl_allinone a
JOIN tbl_equipment_type_registry r ON r.tableName = 'tbl_allinone'
LEFT JOIN tbl_employee e ON a.employeeId = e.employeeId
LEFT JOIN location l ON e.location_id = l.location_id

UNION ALL

SELECT 'Monitor' AS type_name, r.typeId AS type_id, m.monitorId AS id, m.monitorBrand AS brand, m.monitorSerial AS serial, CONCAT(e.firstName,' ',e.lastName) AS owner_name, l.location_name AS location_name, 'Employee' AS context
FROM tbl_monitor m
JOIN tbl_equipment_type_registry r ON r.tableName = 'tbl_monitor'
LEFT JOIN tbl_employee e ON m.employeeId = e.employeeId
LEFT JOIN location l ON e.location_id = l.location_id

UNION ALL

SELECT 'Printer' AS type_name, r.typeId AS type_id, p.printerId AS id, COALESCE(p.printerBrand,'Unknown') AS brand, p.printerSerial AS serial, CONCAT(e.firstName,' ',e.lastName) AS owner_name, l.location_name AS location_name, 'Employee' AS context
FROM tbl_printer p
JOIN tbl_equipment_type_registry r ON r.tableName = 'tbl_printer'
LEFT JOIN tbl_employee e ON p.employeeId = e.employeeId
LEFT JOIN location l ON e.location_id = l.location_id

UNION ALL

SELECT o.equipmentType AS type_name, r.typeId AS type_id, o.otherEquipmentId AS id, o.brand AS brand, o.serialNumber AS serial,
CASE WHEN r.context = 'Employee' THEN CONCAT(e.firstName,' ',e.lastName) ELSE 'N/A' END AS owner_name,
CASE WHEN r.context = 'Employee' THEN el.location_name ELSE l.location_name END AS location_name,
r.context AS context
FROM tbl_otherequipment o
JOIN tbl_equipment_type_registry r ON r.tableName = 'tbl_otherequipment' AND o.equipmentType = r.typeName
LEFT JOIN location l ON o.location_id = l.location_id
LEFT JOIN tbl_employee e ON o.employeeId = e.employeeId
LEFT JOIN location el ON e.location_id = el.location_id
";

try {
    $db->exec($sql);
    echo "View view_maintenance_master updated successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
