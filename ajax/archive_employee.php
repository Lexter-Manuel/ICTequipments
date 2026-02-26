<?php
// ajax/archive_employee.php
// Archive or restore an employee. Archiving also unassigns all equipment.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action     = $_POST['action'] ?? '';
$employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);

if (!$employeeId) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee ID.']);
    exit;
}

try {
    // Verify employee exists
    $empStmt = $db->prepare("SELECT employeeId, firstName, lastName FROM tbl_employee WHERE employeeId = ?");
    $empStmt->execute([$employeeId]);
    $employee = $empStmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo json_encode(['success' => false, 'message' => 'Employee not found.']);
        exit;
    }

    $fullName = trim($employee['firstName'] . ' ' . $employee['lastName']);

    if ($action === 'archive') {
        $db->beginTransaction();

        // 1) Unassign all equipment from the employee
        $tables = [
            'tbl_systemunit',
            'tbl_allinone',
            'tbl_monitor',
            'tbl_printer',
            'tbl_otherequipment',
            'tbl_software',
        ];

        $unassignedCounts = [];
        foreach ($tables as $table) {
            $countStmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE employeeId = ?");
            $countStmt->execute([$employeeId]);
            $count = (int) $countStmt->fetchColumn();

            if ($count > 0) {
                $db->prepare("UPDATE {$table} SET employeeId = NULL WHERE employeeId = ?")
                   ->execute([$employeeId]);
                $unassignedCounts[$table] = $count;
            }
        }

        // 2) Mark employee as archived
        $db->prepare("UPDATE tbl_employee SET is_archive = 1, updatedAt = NOW() WHERE employeeId = ?")
           ->execute([$employeeId]);

        $db->commit();

        $totalUnassigned = array_sum($unassignedCounts);
        $msg = "Employee \"{$fullName}\" has been archived.";
        if ($totalUnassigned > 0) {
            $msg .= " {$totalUnassigned} equipment item(s) were unassigned.";
        }

        logActivity(ACTION_UPDATE, MODULE_EMPLOYEES,
            "Archived employee {$fullName} (ID: {$employeeId}). {$totalUnassigned} equipment item(s) unassigned.");

        echo json_encode(['success' => true, 'message' => $msg]);

    } elseif ($action === 'restore') {
        $db->prepare("UPDATE tbl_employee SET is_archive = 0, updatedAt = NOW() WHERE employeeId = ?")
           ->execute([$employeeId]);

        logActivity(ACTION_UPDATE, MODULE_EMPLOYEES,
            "Restored employee {$fullName} (ID: {$employeeId}) from archive.");

        echo json_encode(['success' => true, 'message' => "Employee \"{$fullName}\" has been restored."]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action. Use "archive" or "restore".']);
    }

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Archive employee error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    error_log("Archive employee error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
