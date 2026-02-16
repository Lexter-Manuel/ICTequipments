<?php
require_once '../config/database.php';
$db = Database::getInstance()->getConnection();

$id = $_GET['id'] ?? null;
if (!$id) { echo json_encode([]); exit; }

// Fetch the target location
$stmt = $db->prepare("SELECT location_id, location_type_id, parent_location_id FROM location WHERE location_id = ?");
$stmt->execute([$id]);
$target = $stmt->fetch(PDO::FETCH_ASSOC);

$path = [
    'division_id' => null,
    'section_id' => null,
    'unit_id' => null
];

if ($target) {
    if ($target['location_type_id'] == 3) { // Target is Unit
        $path['unit_id'] = $target['location_id'];
        
        // Get Parent (could be Section OR Division)
        $stmt->execute([$target['parent_location_id']]);
        $parent = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($parent['location_type_id'] == 2) { // Parent is Section
            $path['section_id'] = $parent['location_id'];
            $path['division_id'] = $parent['parent_location_id'];
        } else { // Parent is Division
            $path['division_id'] = $parent['location_id'];
        }
    } 
    elseif ($target['location_type_id'] == 2) { // Target is Section
        $path['section_id'] = $target['location_id'];
        $path['division_id'] = $target['parent_location_id'];
    } 
    else { // Target is Division
        $path['division_id'] = $target['location_id'];
    }
}

echo json_encode($path);
?>