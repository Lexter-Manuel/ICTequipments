<?php
/**
 * ajax/archive_employee.php
 * Archives an employee and unassigns all their equipment.
 * Updated for unified tbl_equipment schema.
 */
require_once '../config/session-guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

$employeeId = filter_input(INPUT_POST, 'employee_id', FILTER_VALIDATE_INT);

if (!$employeeId) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID.']);
    exit;
}

try {
    $db->beginTransaction();

    // Unassign all equipment from this employee
    $eqStmt = $db->prepare("UPDATE tbl_equipment SET employee_id = NULL WHERE employee_id = :id");
    $eqStmt->execute([':id' => $employeeId]);
    $eqCount = $eqStmt->rowCount();

    // Unassign all software from this employee
    $swStmt = $db->prepare("UPDATE tbl_software SET employeeId = NULL WHERE employeeId = :id");
    $swStmt->execute([':id' => $employeeId]);
    $swCount = $swStmt->rowCount();

    // Archive the employee
    $archStmt = $db->prepare("UPDATE tbl_employee SET is_archive = 1 WHERE employeeId = :id");
    $archStmt->execute([':id' => $employeeId]);

    if ($archStmt->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Employee not found.']);
        exit;
    }

    // Log activity
    $logStmt = $db->prepare("
        INSERT INTO activity_log (user_id, action_type, action_details, entity_type, entity_id)
        VALUES (:uid, 'archive', :details, 'employee', :eid)
    ");
    $userId = $_SESSION['user_id'] ?? 0;
    $logStmt->execute([
        ':uid'     => $userId,
        ':details' => "Archived employee #$employeeId. Unassigned $eqCount equipment and $swCount software.",
        ':eid'     => $employeeId,
    ]);

    $db->commit();
    echo json_encode([
        'success' => true,
        'message' => "Employee archived. $eqCount equipment and $swCount software unassigned.",
    ]);

} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
