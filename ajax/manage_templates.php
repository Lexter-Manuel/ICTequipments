<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$db = getDB();
$action = $_GET['action'] ?? '';

try {

    switch($action) {
        case 'create':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) throw new Exception("Invalid JSON data");

            // Extract Header Info
            $name = $input['title'] ?? 'Untitled Template';
            $type = $input['equipmentType'] ?? 'system_unit';
            $freq = $input['frequency'] ?? 'Semi-Annual';
            $signatoriesJson = json_encode($input['signatories']);

            // Start Transaction
            $db->beginTransaction();

            try {
                // A. Insert Template Parent
                $stmt = $db->prepare("
                    INSERT INTO tbl_maintenance_template 
                    (templateName, targetTypeId, frequency, signatories_json, isActive) 
                    VALUES (:name, :type, :freq, :sigs, 1)
                ");

                $stmt->execute([
                    ':name' => $name,
                    ':type' => $type,
                    ':freq' => $freq,
                    ':sigs' => $signatoriesJson
                ]);
                
                $templateId = $db->lastInsertId();

                // B. Loop & Insert Categories
                if (!empty($input['categories'])) {
                    $sqlCat = "INSERT INTO tbl_checklist_category (templateId, categoryName, sequenceOrder) VALUES (:tid, :name, :order)";
                    $stmtCat = $db->prepare($sqlCat);

                    $sqlItem = "INSERT INTO tbl_checklist_item (categoryId, taskDescription, sequenceOrder) VALUES (:cid, :desc, :order)";
                    $stmtItem = $db->prepare($sqlItem);

                    foreach ($input['categories'] as $catIndex => $category) {
                        // Insert Category
                        $stmtCat->execute([
                            ':tid' => $templateId,
                            ':name' => $category['title'],
                            ':order' => $category['order'] ?? ($catIndex + 1)
                        ]);
                        $categoryId = $db->lastInsertId();

                        // C. Loop & Insert Items
                        if (!empty($category['items'])) {
                            foreach ($category['items'] as $itemIndex => $item) {
                                $stmtItem->execute([
                                    ':cid' => $categoryId,
                                    ':desc' => $item['text'],
                                    ':order' => $item['order'] ?? ($itemIndex + 1)
                                ]);
                            }
                        }
                    }
                }

                $db->commit();
                echo json_encode(['success' => true, 'message' => 'Template saved successfully']);

            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;

        // GET SINGLE TEMPLATE (Supports ID for Preview, or Type for Maintenance)
        case 'get':
            $id = $_GET['id'] ?? '';
            $type = $_GET['type'] ?? '';
            
            if ($id) {
                // Fetch by ID (For Preview Mode)
                $stmt = $db->prepare("SELECT * FROM tbl_maintenance_template WHERE templateId = ?");
                $stmt->execute([$id]);
            } else {
                // Fetch by Type (For Perform Maintenance Mode)
                // Supports comma-separated targetTypeId (e.g. '1,3,4')
                $stmt = $db->prepare("SELECT * FROM tbl_maintenance_template WHERE FIND_IN_SET(?, targetTypeId) > 0 AND isActive = 1 LIMIT 1");
                $stmt->execute([$type]);
            }

            $template = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($template) {
                // B. Get Categories
                $stmtCat = $db->prepare("SELECT * FROM tbl_checklist_category WHERE templateId = ? ORDER BY sequenceOrder ASC");
                $stmtCat->execute([$template['templateId']]);
                $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

                // C. Build Full Structure
                $fullStructure = [];
                
                foreach ($categories as $cat) {
                    $stmtItem = $db->prepare("SELECT * FROM tbl_checklist_item WHERE categoryId = ? ORDER BY sequenceOrder ASC");
                    $stmtItem->execute([$cat['categoryId']]);
                    $items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

                    $catData = [
                        'title' => $cat['categoryName'],
                        'order' => $cat['sequenceOrder'],
                        'items' => []
                    ];

                    foreach ($items as $item) {
                        $catData['items'][] = [
                            'text' => $item['taskDescription'],
                            'order' => $item['sequenceOrder']
                        ];
                    }
                    $fullStructure[] = $catData;
                }
                
                // Inject structure back into object so JS works normally
                $template['structure_json'] = json_encode(['categories' => $fullStructure]);

                // Resolve type IDs to names
                $typeMap = [];
                $typeStmt = $db->query("SELECT typeId, typeName FROM tbl_equipment_type_registry");
                while ($r = $typeStmt->fetch(PDO::FETCH_ASSOC)) {
                    $typeMap[$r['typeId']] = $r['typeName'];
                }
                $ids = array_filter(explode(',', $template['targetTypeId']));
                $template['targetTypeNames'] = array_map(function($id) use ($typeMap) {
                    return $typeMap[trim($id)] ?? 'Unknown';
                }, $ids);
                
                echo json_encode(['success' => true, 'data' => $template]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No template found']);
            }
            break;

            // LIST TEMPLATES BY TYPE (For the Dropdown)
        case 'list_by_type':
            $type = $_GET['type'] ?? '';
            // Supports comma-separated targetTypeId (e.g. '1,3,4')
            $stmt = $db->prepare("
                SELECT templateId, templateName, frequency 
                FROM tbl_maintenance_template 
                WHERE FIND_IN_SET(?, targetTypeId) > 0 AND isActive = 1 
                ORDER BY templateName ASC
            ");
            $stmt->execute([$type]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
            break;
        
        case 'list':
            $sql = "
                SELECT 
                    t.*, 
                    COUNT(i.itemId) as item_count
                FROM tbl_maintenance_template t
                LEFT JOIN tbl_checklist_category c ON t.templateId = c.templateId
                LEFT JOIN tbl_checklist_item i ON c.categoryId = i.categoryId
                WHERE t.isActive = 1 
                GROUP BY t.templateId
                ORDER BY t.templateId DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Resolve type IDs to names for display
            $typeMap = [];
            $typeStmt = $db->query("SELECT typeId, typeName FROM tbl_equipment_type_registry");
            while ($r = $typeStmt->fetch(PDO::FETCH_ASSOC)) {
                $typeMap[$r['typeId']] = $r['typeName'];
            }
            foreach ($data as &$row) {
                $ids = array_filter(explode(',', $row['targetTypeId']));
                $names = array_map(function($id) use ($typeMap) {
                    return $typeMap[trim($id)] ?? 'Unknown';
                }, $ids);
                $row['targetTypeNames'] = $names;
            }
            unset($row);
            
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'update':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || empty($input['id'])) throw new Exception("Invalid Data or Missing ID");

            $id = $input['id'];
            $name = $input['title'];
            $type = $input['equipmentType'];
            $freq = $input['frequency'];
            $signatoriesJson = json_encode($input['signatories']);

            $db->beginTransaction();

            try {
                $stmt = $db->prepare("
                    UPDATE tbl_maintenance_template 
                    SET templateName = :name, targetTypeId = :type, frequency = :freq, signatories_json = :sigs 
                    WHERE templateId = :id
                ");
                $stmt->execute([':name'=>$name, ':type'=>$type, ':freq'=>$freq, ':sigs'=>$signatoriesJson, ':id'=>$id]);


                $db->prepare("DELETE FROM tbl_checklist_category WHERE templateId = ?")->execute([$id]);

                if (!empty($input['categories'])) {
                    $sqlCat = "INSERT INTO tbl_checklist_category (templateId, categoryName, sequenceOrder) VALUES (:tid, :name, :order)";
                    $stmtCat = $db->prepare($sqlCat);
                    $sqlItem = "INSERT INTO tbl_checklist_item (categoryId, taskDescription, sequenceOrder) VALUES (:cid, :desc, :order)";
                    $stmtItem = $db->prepare($sqlItem);

                    foreach ($input['categories'] as $catIndex => $category) {
                        $stmtCat->execute([':tid' => $id, ':name' => $category['title'], ':order' => $category['order'] ?? ($catIndex + 1)]);
                        $categoryId = $db->lastInsertId();

                        if (!empty($category['items'])) {
                            foreach ($category['items'] as $itemIndex => $item) {
                                $stmtItem->execute([':cid' => $categoryId, ':desc' => $item['text'], ':order' => $item['order'] ?? ($itemIndex + 1)]);
                            }
                        }
                    }
                }

                $db->commit();
                echo json_encode(['success' => true, 'message' => 'Template updated successfully']);

            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;

        case 'delete':
            $id = $_GET['id'] ?? $_POST['id'] ?? null;
            if (!$id) {
                throw new Exception("Missing template ID");
            }

            $db->beginTransaction();

            try {
                $db->prepare("DELETE FROM tbl_checklist_item WHERE categoryId IN (SELECT categoryId FROM tbl_checklist_category WHERE templateId = ?)")->execute([$id]);
                $db->prepare("DELETE FROM tbl_checklist_category WHERE templateId = ?")->execute([$id]);
                $db->prepare("DELETE FROM tbl_maintenance_template WHERE templateId = ?")->execute([$id]);

                $db->commit();
                echo json_encode(['success' => true, 'message' => 'Template deleted successfully']);
            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>