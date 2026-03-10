<?php
/**
 * Equipment Assignment History Helper
 *
 * Records every assignment change (assign / unassign / transfer)
 * into tbl_equipment_assignment_history.
 */

/**
 * Record an assignment change for a piece of equipment.
 *
 * Call this BEFORE the UPDATE statement so you can pass
 * the old employee_id and the new employee_id.
 *
 * @param PDO      $db
 * @param int      $equipmentId
 * @param int|null $oldEmployeeId  Previous holder (null = was unassigned)
 * @param int|null $newEmployeeId  New holder      (null = being unassigned)
 * @param string|null $remarks     Optional note
 */
function recordAssignmentChange(PDO $db, int $equipmentId, ?int $oldEmployeeId, ?int $newEmployeeId, ?string $remarks = null): void
{
    // No change — nothing to record
    if ($oldEmployeeId === $newEmployeeId) return;

    $performedBy = $_SESSION['user_id'] ?? null;
    $now = date('Y-m-d H:i:s');

    // 1. Close the previous open assignment (set unassigned_at)
    if ($oldEmployeeId !== null) {
        $stmt = $db->prepare("
            UPDATE tbl_equipment_assignment_history
            SET unassigned_at = :now
            WHERE equipment_id = :eid
              AND employee_id  = :empId
              AND unassigned_at IS NULL
            ORDER BY history_id DESC
            LIMIT 1
        ");
        $stmt->execute([':now' => $now, ':eid' => $equipmentId, ':empId' => $oldEmployeeId]);
    }

    // 2. Determine action label
    if ($oldEmployeeId === null && $newEmployeeId !== null) {
        $action = 'assigned';
    } elseif ($oldEmployeeId !== null && $newEmployeeId === null) {
        $action = 'unassigned';
    } else {
        $action = 'transferred';
    }

    // 3. Insert new history row
    if ($newEmployeeId !== null) {
        // Assigned or transferred → new open assignment
        $stmt = $db->prepare("
            INSERT INTO tbl_equipment_assignment_history
                (equipment_id, employee_id, action, assigned_at, performed_by, remarks)
            VALUES (:eid, :empId, :action, :now, :by, :remarks)
        ");
        $stmt->execute([
            ':eid'     => $equipmentId,
            ':empId'   => $newEmployeeId,
            ':action'  => $action,
            ':now'     => $now,
            ':by'      => $performedBy,
            ':remarks' => $remarks,
        ]);
    } else {
        // Unassigned → record the event (employee_id = old holder)
        $stmt = $db->prepare("
            INSERT INTO tbl_equipment_assignment_history
                (equipment_id, employee_id, action, unassigned_at, performed_by, remarks)
            VALUES (:eid, :empId, 'unassigned', :now, :by, :remarks)
        ");
        $stmt->execute([
            ':eid'     => $equipmentId,
            ':empId'   => $oldEmployeeId,
            ':now'     => $now,
            ':by'      => $performedBy,
            ':remarks' => $remarks,
        ]);
    }
}
