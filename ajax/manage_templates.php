<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/database.php';
require_once '../config/config.php';
header('Content-Type: application/json');

// Require a logged-in user
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$db = getDB();
$action = $_GET['action'] ?? '';

try {
    // ===================== GET REQUESTS =====================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        // ---------- LIST all templates ----------
        if ($action === 'list') {
            $stmt = $db->query("
                SELECT t.templateId, t.templateName, t.targetTypeId, t.frequency,
                       t.signatories_json, t.isActive, t.createdAt,
                       (SELECT COUNT(*) FROM tbl_checklist_item ci
                        JOIN tbl_checklist_category cc ON ci.categoryId = cc.categoryId
                        WHERE cc.templateId = t.templateId) AS item_count
                FROM tbl_maintenance_template t
                WHERE t.isActive = 1
                ORDER BY t.createdAt DESC
            ");
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Resolve targetTypeId to type names
            $typeStmt = $db->query("SELECT typeId, typeName FROM tbl_equipment_type_registry");
            $typeMap = [];
            foreach ($typeStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $typeMap[$row['typeId']] = $row['typeName'];
            }

            foreach ($templates as &$tpl) {
                $ids = array_filter(array_map('trim', explode(',', $tpl['targetTypeId'])));
                $names = [];
                foreach ($ids as $id) {
                    if (isset($typeMap[$id])) $names[] = $typeMap[$id];
                }
                $tpl['targetTypeNames'] = $names;
            }
            unset($tpl);

            echo json_encode(['success' => true, 'data' => $templates]);
            exit;
        }

        // ---------- GET single template ----------
        if ($action === 'get') {
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception("Missing template ID");

            $stmt = $db->prepare("SELECT * FROM tbl_maintenance_template WHERE templateId = ?");
            $stmt->execute([$id]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$template) throw new Exception("Template not found");

            // Build structure_json from normalised tables if it is NULL
            if (empty($template['structure_json'])) {
                $catStmt = $db->prepare("
                    SELECT categoryId, categoryName, sequenceOrder
                    FROM tbl_checklist_category
                    WHERE templateId = ?
                    ORDER BY sequenceOrder
                ");
                $catStmt->execute([$id]);
                $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

                $itemStmt = $db->prepare("
                    SELECT taskDescription, sequenceOrder
                    FROM tbl_checklist_item
                    WHERE categoryId = ?
                    ORDER BY sequenceOrder
                ");

                $structure = ['categories' => []];
                foreach ($categories as $cat) {
                    $itemStmt->execute([$cat['categoryId']]);
                    $items = [];
                    foreach ($itemStmt->fetchAll(PDO::FETCH_ASSOC) as $item) {
                        $items[] = ['text' => $item['taskDescription'], 'order' => (int)$item['sequenceOrder']];
                    }
                    $structure['categories'][] = [
                        'title' => $cat['categoryName'],
                        'order' => (int)$cat['sequenceOrder'],
                        'items' => $items
                    ];
                }
                $template['structure_json'] = json_encode($structure);
            }

            // Resolve type names
            $typeIds = array_filter(array_map('trim', explode(',', $template['targetTypeId'])));
            if ($typeIds) {
                $placeholders = implode(',', array_fill(0, count($typeIds), '?'));
                $tStmt = $db->prepare("SELECT typeName FROM tbl_equipment_type_registry WHERE typeId IN ($placeholders)");
                $tStmt->execute($typeIds);
                $template['targetTypeNames'] = $tStmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $template['targetTypeNames'] = [];
            }

            echo json_encode(['success' => true, 'data' => $template]);
            exit;
        }

        // ---------- LIST templates filtered by equipment type ----------
        if ($action === 'list_by_type') {
            $typeId = (int)($_GET['type'] ?? 0);
            if (!$typeId) throw new Exception("Missing type parameter");

            // targetTypeId is stored as comma-separated IDs, so use FIND_IN_SET
            $stmt = $db->prepare("
                SELECT t.templateId, t.templateName, t.targetTypeId, t.frequency,
                       t.signatories_json, t.isActive, t.createdAt,
                       (SELECT COUNT(*) FROM tbl_checklist_item ci
                        JOIN tbl_checklist_category cc ON ci.categoryId = cc.categoryId
                        WHERE cc.templateId = t.templateId) AS item_count
                FROM tbl_maintenance_template t
                WHERE t.isActive = 1
                  AND FIND_IN_SET(:typeId, REPLACE(t.targetTypeId, ' ', ''))
                ORDER BY t.createdAt DESC
            ");
            $stmt->execute([':typeId' => $typeId]);
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $templates]);
            exit;
        }

        // ---------- DELETE template (soft) ----------
        if ($action === 'delete') {
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) throw new Exception("Missing template ID");

            $stmt = $db->prepare("UPDATE tbl_maintenance_template SET isActive = 0 WHERE templateId = ?");
            $stmt->execute([$id]);

            logActivity(ACTION_DELETE, MODULE_MAINTENANCE, "Soft-deleted maintenance template ID {$id}.");
            echo json_encode(['success' => true, 'message' => 'Template deleted']);
            exit;
        }

        throw new Exception("Unknown GET action: " . htmlspecialchars($action));
    }

    // ===================== POST REQUESTS =====================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) throw new Exception("Invalid JSON body");

        // ---------- CREATE ----------
        if ($action === 'create') {
            if (empty($input['title']) || empty($input['equipmentType']) || empty($input['frequency'])) {
                throw new Exception("Missing required fields (title, equipmentType, frequency)");
            }

            $db->beginTransaction();
            try {
                // Build structure JSON from categories
                $structureJson = json_encode(['categories' => $input['categories'] ?? []]);
                $signatoriesJson = !empty($input['signatories']) ? json_encode($input['signatories']) : null;

                $stmt = $db->prepare("
                    INSERT INTO tbl_maintenance_template (templateName, targetTypeId, frequency, structure_json, signatories_json, isActive)
                    VALUES (:name, :type, :freq, :struct, :sig, 1)
                ");
                $stmt->execute([
                    ':name'   => $input['title'],
                    ':type'   => $input['equipmentType'],
                    ':freq'   => $input['frequency'],
                    ':struct' => $structureJson,
                    ':sig'    => $signatoriesJson
                ]);
                $newId = (int)$db->lastInsertId();

                // Persist normalised categories & items
                persistCategories($db, $newId, $input['categories'] ?? []);

                $db->commit();
                logActivity(ACTION_CREATE, MODULE_MAINTENANCE, "Created maintenance template '{$input['title']}' (ID: {$newId}).");
                echo json_encode(['success' => true, 'message' => 'Template created', 'templateId' => $newId]);

            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            exit;
        }

        // ---------- UPDATE ----------
        if ($action === 'update') {
            $id = (int)($input['id'] ?? 0);
            if (!$id) throw new Exception("Missing template ID for update");

            $db->beginTransaction();
            try {
                $structureJson = json_encode(['categories' => $input['categories'] ?? []]);
                $signatoriesJson = !empty($input['signatories']) ? json_encode($input['signatories']) : null;

                $stmt = $db->prepare("
                    UPDATE tbl_maintenance_template
                    SET templateName    = :name,
                        targetTypeId    = :type,
                        frequency       = :freq,
                        structure_json  = :struct,
                        signatories_json = :sig
                    WHERE templateId = :id
                ");
                $stmt->execute([
                    ':name'   => $input['title'],
                    ':type'   => $input['equipmentType'],
                    ':freq'   => $input['frequency'],
                    ':struct' => $structureJson,
                    ':sig'    => $signatoriesJson,
                    ':id'     => $id
                ]);

                // Replace normalised categories & items
                // Delete old ones first
                $catIds = $db->prepare("SELECT categoryId FROM tbl_checklist_category WHERE templateId = ?");
                $catIds->execute([$id]);
                $oldCatIds = $catIds->fetchAll(PDO::FETCH_COLUMN);

                if ($oldCatIds) {
                    $ph = implode(',', array_fill(0, count($oldCatIds), '?'));
                    $db->prepare("DELETE FROM tbl_checklist_item WHERE categoryId IN ($ph)")->execute($oldCatIds);
                }
                $db->prepare("DELETE FROM tbl_checklist_category WHERE templateId = ?")->execute([$id]);

                // Re-insert
                persistCategories($db, $id, $input['categories'] ?? []);

                $db->commit();
                logActivity(ACTION_UPDATE, MODULE_MAINTENANCE, "Updated maintenance template ID {$id}.");
                echo json_encode(['success' => true, 'message' => 'Template updated']);

            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            exit;
        }

        throw new Exception("Unknown POST action: " . htmlspecialchars($action));
    }

    throw new Exception("Invalid request method");

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Persist normalised checklist categories & items for a template.
 */
function persistCategories(PDO $db, int $templateId, array $categories): void
{
    $catStmt = $db->prepare("
        INSERT INTO tbl_checklist_category (templateId, categoryName, sequenceOrder)
        VALUES (:tid, :name, :seq)
    ");
    $itemStmt = $db->prepare("
        INSERT INTO tbl_checklist_item (categoryId, taskDescription, sequenceOrder)
        VALUES (:cid, :desc, :seq)
    ");

    foreach ($categories as $catIdx => $cat) {
        $catStmt->execute([
            ':tid'  => $templateId,
            ':name' => $cat['title'] ?? 'Untitled',
            ':seq'  => $cat['order'] ?? ($catIdx + 1)
        ]);
        $catId = (int)$db->lastInsertId();

        if (!empty($cat['items']) && is_array($cat['items'])) {
            foreach ($cat['items'] as $itemIdx => $item) {
                $itemStmt->execute([
                    ':cid'  => $catId,
                    ':desc' => $item['text'] ?? '',
                    ':seq'  => $item['order'] ?? ($itemIdx + 1)
                ]);
            }
        }
    }
}
?>