<?php

require_once '../config/session-guard.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/assignmentHistoryHelper.php';
header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

$employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'archive';

if (!$employeeId) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID.']);
    exit;
}

try {
    $db->beginTransaction();

    if ($action === 'restore') {
        // Restore the employee from archive
        $restoreStmt = $db->prepare("UPDATE tbl_employee SET is_archive = 0 WHERE employeeId = :id AND is_archive = 1");
        $restoreStmt->execute([':id' => $employeeId]);

        if ($restoreStmt->rowCount() === 0) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Employee not found or already active.']);
            exit;
        }

        // Log activity
        logActivity(
            'RESTORE',
            'Employees',
            "Restored employee #$employeeId from archive."
        );

        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Employee restored successfully.',
        ]);
    } else {
        // Unassign all equipment from this employee (with history)
        $eqListStmt = $db->prepare("SELECT equipment_id FROM tbl_equipment WHERE employee_id = :id");
        $eqListStmt->execute([':id' => $employeeId]);
        $equipIds = $eqListStmt->fetchAll(PDO::FETCH_COLUMN);

        $eqStmt = $db->prepare("UPDATE tbl_equipment SET employee_id = NULL WHERE employee_id = :id");
        $eqStmt->execute([':id' => $employeeId]);
        $eqCount = $eqStmt->rowCount();

        // Record history for each unassigned equipment
        foreach ($equipIds as $eqId) {
            recordAssignmentChange($db, (int)$eqId, (int)$employeeId, null, 'Employee archived');
        }

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
        logActivity(
            'ARCHIVE',
            'Employees',
            "Archived employee #$employeeId. Unassigned $eqCount equipment and $swCount software."
        );

        $db->commit();
        echo json_encode([
            'success' => true,
            'message' => "Employee archived. $eqCount equipment and $swCount software unassigned.",
        ]);
    }

} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
