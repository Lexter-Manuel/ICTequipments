<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$db = getDB();
$type = $_GET['type'] ?? '';

try {
    // Map dropdown values to DB Table Names
    $tableMap = [
        'system_unit' => 'tbl_systemunit',
        'printer' => 'tbl_printer',
        'laptop'  => 'tbl_otherequipment', // Assuming laptops are here or separate
        'monitor' => 'tbl_monitor'
    ];

    if (!isset($tableMap[$type])) {
        echo json_encode([]); 
        exit;
    }

    $sql = "";
    
    // DIFFERENT QUERY BASED ON TYPE (Because column names differ)
    if ($type === 'system_unit') {
        $sql = "SELECT systemunitId as id, systemUnitBrand as name, systemUnitSerial as serial, 
                CONCAT('Owned by: ', employeeId) as location_info 
                FROM tbl_systemunit ORDER BY systemUnitBrand";
    } 
    elseif ($type === 'printer') {
        $sql = "SELECT printerId as id, printerBrand as name, printerSerial as serial, 
                'Printer' as location_info 
                FROM tbl_printer ORDER BY printerBrand";
    }
    // Add other cases as needed...

    if ($sql) {
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($assets);
    } else {
        echo json_encode([]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>