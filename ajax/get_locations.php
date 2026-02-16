<?php
require_once '../config/database.php';
$db = Database::getInstance()->getConnection();

$type = $_GET['type'] ?? null;
$parent = $_GET['parent'] ?? null;

$sql = "SELECT location_id, location_name, location_type_id FROM location WHERE is_deleted = '0'";
$params = [];

if ($type) {
    $sql .= " AND location_type_id = :type";
    $params[':type'] = $type;
}
if ($parent) {
    $sql .= " AND parent_location_id = :parent";
    $params[':parent'] = $parent;
} else if ($type == 1) {
    // Divisions have no parent
    $sql .= " AND parent_location_id IS NULL";
}

$sql .= " ORDER BY location_name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>