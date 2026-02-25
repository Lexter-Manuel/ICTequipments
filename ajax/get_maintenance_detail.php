<?php
/**
 * get_maintenance_detail.php
 * --------------------------
 * Returns detail data for either a schedule entry or a maintenance record.
 *
 * Modes (via ?type=):
 *   schedule  – detailed info about a scheduled maintenance entry  (?scheduleId=)
 *   record    – detailed info about a completed maintenance record (?recordId=)
 */

require_once '../config/database.php';
header('Content-Type: application/json');
$db = getDB();

$type = $_GET['type'] ?? '';

try {

    // ═════════════════════════════════════════════════════════
    // MODE: SCHEDULE DETAIL
    // ═════════════════════════════════════════════════════════
    if ($type === 'schedule') {
        $scheduleId = (int)($_GET['scheduleId'] ?? 0);
        if ($scheduleId <= 0) throw new Exception('Invalid schedule ID.');

        $sql = "
            SELECT
                ms.scheduleId,
                ms.equipmentId,
                ms.equipmentType,
                ms.maintenanceFrequency,
                ms.nextDueDate,
                ms.lastMaintenanceDate,
                ms.isActive,
                DATEDIFF(ms.nextDueDate, CURDATE()) AS daysDue,
                v.brand,
                v.serial,
                v.type_name,
                v.type_id,
                v.owner_name,
                v.location_name
            FROM tbl_maintenance_schedule ms
            JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
            WHERE ms.scheduleId = :sid
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':sid', $scheduleId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) throw new Exception('Schedule not found.');

        // Add computed status
        $daysDue = (int)$row['daysDue'];
        if ($daysDue < 0) {
            $row['status'] = 'overdue';
            $d = abs($daysDue);
            $row['daysLabel'] = $d . ' Day' . ($d !== 1 ? 's' : '') . ' Overdue';
        } elseif ($daysDue === 0) {
            $row['status'] = 'due_soon';
            $row['daysLabel'] = 'Due Today';
        } elseif ($daysDue <= 7) {
            $row['status'] = 'due_soon';
            $row['daysLabel'] = $daysDue . ' Day' . ($daysDue !== 1 ? 's' : '') . ' Away';
        } else {
            $row['status'] = 'scheduled';
            $row['daysLabel'] = $daysDue . ' Day' . ($daysDue !== 1 ? 's' : '') . ' Away';
        }

        // Last 5 maintenance records for this schedule
        $histSql = "
            SELECT 
                mr.recordId,
                mr.maintenanceDate,
                mr.overallStatus,
                mr.conditionRating,
                mr.preparedBy AS technician,
                mr.remarks
            FROM tbl_maintenance_record mr
            WHERE mr.scheduleId = :sid
            ORDER BY mr.maintenanceDate DESC
            LIMIT 5
        ";
        $histStmt = $db->prepare($histSql);
        $histStmt->bindValue(':sid', $scheduleId, PDO::PARAM_INT);
        $histStmt->execute();
        $row['recentRecords'] = $histStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'data' => $row]);
        exit;
    }

    // ═════════════════════════════════════════════════════════
    // MODE: RECORD DETAIL (completed maintenance)
    // ═════════════════════════════════════════════════════════
    if ($type === 'record') {
        $recordId = (int)($_GET['recordId'] ?? 0);
        if ($recordId <= 0) throw new Exception('Invalid record ID.');

        // Fetch main record
        $sql = "
            SELECT
                mr.recordId,
                mr.scheduleId,
                mr.templateId,
                mr.maintenanceDate,
                mr.overallStatus,
                mr.conditionRating,
                mr.remarks,
                mr.checklistJson,
                mr.preparedBy,
                mr.checkedBy,
                mr.notedBy,
                v.brand,
                v.serial,
                v.type_name,
                v.location_name,
                v.owner_name,
                mt.templateName
            FROM tbl_maintenance_record mr
            LEFT JOIN tbl_maintenance_schedule ms ON mr.scheduleId = ms.scheduleId
            LEFT JOIN view_maintenance_master v ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
            LEFT JOIN tbl_maintenance_template mt ON mr.templateId = mt.templateId
            WHERE mr.recordId = :rid
            LIMIT 1
        ";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':rid', $recordId, PDO::PARAM_INT);
        $stmt->execute();
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rec) throw new Exception('Maintenance record not found.');

        // Fetch normalized checklist responses
        $respSql = "
            SELECT
                mresp.categoryName,
                mresp.taskDescription,
                mresp.response,
                mresp.sequenceOrder
            FROM tbl_maintenance_response mresp
            WHERE mresp.recordId = :rid
            ORDER BY mresp.sequenceOrder ASC
        ";
        $respStmt = $db->prepare($respSql);
        $respStmt->bindValue(':rid', $recordId, PDO::PARAM_INT);
        $respStmt->execute();
        $responses = $respStmt->fetchAll(PDO::FETCH_ASSOC);

        // If no normalized responses, fall back to checklistJson
        if (empty($responses) && !empty($rec['checklistJson'])) {
            $decoded = json_decode($rec['checklistJson'], true);
            if (is_array($decoded)) {
                // Handle flat array format
                if (isset($decoded[0]['desc']) || isset($decoded[0]['taskDescription'])) {
                    foreach ($decoded as $idx => $item) {
                        $responses[] = [
                            'categoryName'    => $item['categoryName'] ?? 'General',
                            'taskDescription' => $item['desc'] ?? $item['taskDescription'] ?? $item['task'] ?? '',
                            'response'        => $item['status'] ?? $item['response'] ?? 'N/A',
                            'sequenceOrder'   => $item['seq'] ?? ($idx + 1)
                        ];
                    }
                }
                // Handle categories format
                elseif (isset($decoded['categories'])) {
                    $seq = 0;
                    foreach ($decoded['categories'] as $cat) {
                        foreach ($cat['items'] ?? [] as $item) {
                            $seq++;
                            $responses[] = [
                                'categoryName'    => $cat['name'] ?? 'General',
                                'taskDescription' => $item['task'] ?? $item['desc'] ?? '',
                                'response'        => $item['status'] ?? 'N/A',
                                'sequenceOrder'   => $seq
                            ];
                        }
                    }
                }
            }
        }

        $rec['responses'] = $responses;
        // Remove raw JSON from response to keep payload lean
        unset($rec['checklistJson']);

        echo json_encode(['success' => true, 'data' => $rec]);
        exit;
    }

    throw new Exception('Invalid type parameter. Use ?type=schedule or ?type=record');

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
