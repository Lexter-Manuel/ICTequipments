<?php
/**
 * ajax/get_equipment_types.php
 * Returns equipment types from the registry.
 * Updated for unified tbl_equipment schema (tableName column removed).
 */
require_once '../config/session-guard.php';
require_once '../config/database.php';
header('Content-Type: application/json');
$db = getDB();

try {
    $search = trim($_GET['search'] ?? '');
    $scope  = trim($_GET['scope'] ?? '');   // 'all' = entire registry, default = non-builtin types only

    // Built-in hardware types (IDs 1-4 typically)
    $builtinTypes = ['System Unit', 'All-in-One', 'Monitor', 'Printer'];

    if ($scope === 'all') {
        // Return ALL equipment types from the registry
        $stmt = $db->prepare("
            SELECT typeId, typeName, context 
            FROM tbl_equipment_type_registry 
            ORDER BY typeId ASC
        ");
        $stmt->execute();
    } elseif (!empty($search)) {
        // Return "other" types matching search, with relevance ordering
        $builtinPH = implode(',', array_fill(0, count($builtinTypes), '?'));
        $stmt = $db->prepare("
            SELECT typeId, typeName, context,
                CASE 
                    WHEN typeName = ? THEN 1
                    WHEN typeName LIKE ? THEN 2
                    WHEN typeName LIKE ? THEN 3
                    ELSE 4
                END as relevance
            FROM tbl_equipment_type_registry 
            WHERE typeName NOT IN ($builtinPH)
              AND (typeName LIKE ?)
            ORDER BY relevance ASC, typeName ASC
        ");
        $params = [$search, $search . '%', '%' . $search . '%'];
        $params = array_merge($params, $builtinTypes, ['%' . $search . '%']);
        $stmt->execute($params);
    } else {
        // Return all "other equipment" types (exclude built-in hw)
        $builtinPH = implode(',', array_fill(0, count($builtinTypes), '?'));
        $stmt = $db->prepare("
            SELECT typeId, typeName, context 
            FROM tbl_equipment_type_registry 
            WHERE typeName NOT IN ($builtinPH)
            ORDER BY typeName ASC
        ");
        $stmt->execute($builtinTypes);
    }

    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $types]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>