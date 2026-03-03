<?php
require_once __DIR__ . '/../../config/database.php';

$db = Database::getInstance()->getConnection();

$sql = "
CREATE OR REPLACE VIEW view_maintenance_master AS
SELECT
    r.typeName      AS type_name,
    eq.type_id      AS type_id,
    eq.equipment_id AS id,
    eq.brand        AS brand,
    COALESCE(eq.serial_number, 'N/A') AS serial,
    CASE WHEN eq.employee_id IS NOT NULL
         THEN CONCAT(e.firstName, ' ', e.lastName)
         ELSE 'N/A'
    END AS owner_name,
    COALESCE(el.location_name, l.location_name) AS location_name,
    r.context       AS context
FROM tbl_equipment eq
INNER JOIN tbl_equipment_type_registry r ON eq.type_id = r.typeId
LEFT JOIN location l     ON eq.location_id = l.location_id
LEFT JOIN tbl_employee e ON eq.employee_id = e.employeeId
LEFT JOIN location el    ON e.location_id  = el.location_id
WHERE eq.is_archived = 0
";

try {
    $db->exec($sql);
    echo "View view_maintenance_master updated successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
