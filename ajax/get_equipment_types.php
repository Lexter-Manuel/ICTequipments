<?php
require_once '../config/database.php';
header('Content-Type: application/json');
$db = getDB();

try {
    $search = trim($_GET['search'] ?? '');

    if (!empty($search)) {
        // Return all types for client-side fuzzy matching, but prioritize matches
        $stmt = $db->prepare("
            SELECT typeId, typeName, context,
                CASE 
                    WHEN typeName = :exact THEN 1
                    WHEN typeName LIKE :starts THEN 2
                    WHEN typeName LIKE :contains THEN 3
                    ELSE 4
                END as relevance
            FROM tbl_equipment_type_registry 
            WHERE tableName = 'tbl_otherequipment'
              AND (typeName LIKE :search)
            ORDER BY relevance ASC, typeName ASC
        ");
        $stmt->execute([
            ':exact'    => $search,
            ':starts'   => $search . '%',
            ':contains' => '%' . $search . '%',
            ':search'   => '%' . $search . '%'
        ]);
    } else {
        // Return all "other equipment" types (exclude built-in hw like System Unit, Monitor, etc.)
        $stmt = $db->prepare("
            SELECT typeId, typeName, context 
            FROM tbl_equipment_type_registry 
            WHERE tableName = 'tbl_otherequipment'
            ORDER BY typeName ASC
        ");
        $stmt->execute();
    }

    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $types]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>