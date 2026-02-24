<?php
/**
 * Returns sections (that have no child units) and all units
 * for use in the sectionUnit filter dropdown.
 */
require_once '../config/database.php';
header('Content-Type: application/json');

try {
    $db = getDB();

    // Sections that have NO child units (leaf sections)
    // Plus all units (location_type_id = 3)
    $sql = "
        SELECT 
            l.location_id,
            l.location_name,
            l.location_type_id,
            l.parent_location_id,
            lt.name AS type_label
        FROM location l
        JOIN location_type lt ON l.location_type_id = lt.id
        WHERE l.is_deleted = '0'
          AND (
                -- Units (always leaf)
                l.location_type_id = 3
                OR
                -- Sections with NO child units
                (l.location_type_id = 2 AND NOT EXISTS (
                    SELECT 1 FROM location c
                    WHERE c.parent_location_id = l.location_id
                      AND c.location_type_id = 3
                      AND c.is_deleted = '0'
                ))
          )
        ORDER BY l.location_name ASC
    ";

    $stmt = $db->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
