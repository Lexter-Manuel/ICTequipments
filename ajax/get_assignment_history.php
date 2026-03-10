<?php
/**
 * ajax/get_assignment_history.php
 *
 * Returns the full assignment history for a given equipment_id.
 * Each row includes the employee name, action, dates, and who performed it.
 */
require_once '../config/session-guard.php';
require_once '../config/database.php';

header('Content-Type: application/json');

$equipmentId = filter_var($_GET['equipment_id'] ?? 0, FILTER_VALIDATE_INT);
if (!$equipmentId) {
    echo json_encode(['success' => false, 'message' => 'Equipment ID is required.']);
    exit;
}

try {
    $db = getDB();

    // Verify the equipment exists
    $check = $db->prepare("SELECT equipment_id FROM tbl_equipment WHERE equipment_id = ?");
    $check->execute([$equipmentId]);
    if (!$check->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Equipment not found.']);
        exit;
    }

    $stmt = $db->prepare("
        SELECT h.history_id,
               h.equipment_id,
               h.employee_id,
               CONCAT_WS(' ', e.firstName, e.middleName, e.lastName) AS employeeName,
               h.action,
               h.assigned_at,
               h.unassigned_at,
               h.performed_by,
               a.user_name AS performedByName,
               h.remarks,
               h.created_at
        FROM tbl_equipment_assignment_history h
        LEFT JOIN tbl_employee e ON h.employee_id = e.employeeId
        LEFT JOIN tbl_accounts a ON h.performed_by = a.id
        WHERE h.equipment_id = :eid
        ORDER BY h.created_at DESC, h.history_id DESC
    ");
    $stmt->execute([':eid' => $equipmentId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $history = array_map(function ($row) {
        return [
            'history_id'      => (int)$row['history_id'],
            'equipment_id'    => (int)$row['equipment_id'],
            'employee_id'     => $row['employee_id'] ? (int)$row['employee_id'] : null,
            'employeeName'    => $row['employeeName'] ?: 'Unknown',
            'action'          => $row['action'],
            'assigned_at'     => $row['assigned_at'],
            'unassigned_at'   => $row['unassigned_at'],
            'performedByName' => $row['performedByName'] ?: null,
            'remarks'         => $row['remarks'],
            'created_at'      => $row['created_at'],
        ];
    }, $rows);

    echo json_encode(['success' => true, 'data' => $history]);

} catch (Exception $e) {
    // echo json_encode(['success' => false, 'message' => 'Server error.']);
    error_log("Assignment History Query Error: " . $e->getMessage());

    // Temporarily expose the exact SQL error to the frontend payload
    echo json_encode([
        'success' => false, 
        'message' => 'Query Failed: ' . $e->getMessage()
    ]);
}
