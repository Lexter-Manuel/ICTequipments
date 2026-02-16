<?php
require_once '../../config/database.php';

$db = Database::getInstance()->getConnection();

$divStmt = $db->query("SELECT location_id, location_name FROM location WHERE location_type_id = 1 AND is_deleted = '0' ORDER BY location_name ASC");
$divisionsData = $divStmt->fetchAll(PDO::FETCH_ASSOC);

$secStmt = $db->query("SELECT location_id, location_name, parent_location_id FROM location WHERE location_type_id = 2 AND is_deleted = '0' ORDER BY location_name ASC");
$sectionsData = $secStmt->fetchAll(PDO::FETCH_ASSOC);

$unitStmt = $db->query("SELECT location_id, location_name, parent_location_id FROM location WHERE location_type_id = 3 AND is_deleted = '0' ORDER BY location_name ASC");
$unitsData = $unitStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<script>
    var divisionsData = <?php echo json_encode($divisionsData); ?>;
    var sectionsData = <?php echo json_encode($sectionsData); ?>;
    var unitsData = <?php echo json_encode($unitsData); ?>;
</script>