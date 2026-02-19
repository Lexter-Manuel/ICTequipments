<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$db = getDB();

try {
    // 1. Get POST Data (JSON from perform-maintenance.php)
    $input = json_decode(file_get_contents('php://input'), true);
    
    $scheduleId = $input['scheduleId'] ?? null;
    $equipmentId = $input['equipmentId'] ?? null;
    $typeId = $input['typeId'] ?? null; // e.g. from Registry
    $checklistData = json_encode($input['checklist']); // Pass/Fail results
    $remarks = $input['remarks'];
    $rating = $input['rating']; // Good/Fair/Poor
    $status = $input['status']; // Operational/Defective

    $db->beginTransaction();

    // 2. Insert History Record
    $stmtRecord = $db->prepare("
        INSERT INTO tbl_maintenance_record 
        (scheduleId, equipmentTypeId, equipmentId, maintenanceDate, checklistJson, remarks, overallStatus, conditionRating)
        VALUES (:sid, :typeId, :equipmentId, NOW(), :json, :remarks, :status, :rating)
    ");
    $stmtRecord->execute([
        ':sid' => $scheduleId,
        ':typeId' => $typeId,
        ':equipmentId' => $equipmentId,
        ':json' => $checklistData,
        ':remarks' => $remarks,
        ':status' => $status,
        ':rating' => $rating
    ]);

    // 3. Update Schedule (Move Next Due Date forward)
    // First, find the frequency
    $stmtFreq = $db->prepare("SELECT frequencyDays FROM tbl_maintenance_schedule WHERE scheduleId = ?");
    $stmtFreq->execute([$scheduleId]);
    $days = $stmtFreq->fetchColumn() ?: 90; // Default 90 if not found

    $stmtUpdate = $db->prepare("
        UPDATE tbl_maintenance_schedule 
        SET lastMaintenanceDate = NOW(),
            nextDueDate = DATE_ADD(NOW(), INTERVAL :days DAY)
        WHERE scheduleId = :sid
    ");
    $stmtUpdate->execute([':days' => $days, ':sid' => $scheduleId]);

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Maintenance recorded successfully']);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>