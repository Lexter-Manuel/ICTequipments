<?php
class MaintenanceHelper {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Initialize schedule by Registry Type Name (e.g., "System Unit", "Projector")
     */
    public function initScheduleByType($typeName, $equipmentId) {
        // 1. Find the typeId from Registry
        $stmt = $this->db->prepare("SELECT typeId, defaultFrequency FROM tbl_equipment_type_registry WHERE typeName = ?");
        $stmt->execute([$typeName]);
        $type = $stmt->fetch(PDO::FETCH_ASSOC);

        // If this type exists in registry, create schedule
        if ($type) {
            $this->createSchedule($type['typeId'], $equipmentId, $type['defaultFrequency']);
        }
    }

    /**
     * Initialize schedule by explicit Type ID (if known)
     */
    public function initScheduleById($typeId, $equipmentId) {
        $stmt = $this->db->prepare("SELECT defaultFrequency FROM tbl_equipment_type_registry WHERE typeId = ?");
        $stmt->execute([$typeId]);
        $freq = $stmt->fetchColumn();
        
        if ($freq) {
            $this->createSchedule($typeId, $equipmentId, $freq);
        }
    }

    private function createSchedule($typeId, $equipmentId, $frequency) {
        // Try to find a location group to sync with
        $locationGroupId = null;
        $nextDueDate = date('Y-m-d', strtotime("+$frequency days"));

        // Look up this equipment's location via the master view
        $stmtLoc = $this->db->prepare("
            SELECT v.location_name 
            FROM view_maintenance_master v 
            WHERE v.type_id = ? AND v.id = ?
        ");
        $stmtLoc->execute([$typeId, $equipmentId]);
        $locName = $stmtLoc->fetchColumn();

        if ($locName) {
            // Find a neighbor in the same location with an active schedule
            $stmtNeighbor = $this->db->prepare("
                SELECT ms.nextDueDate, ms.location_group_id
                FROM tbl_maintenance_schedule ms
                JOIN view_maintenance_master v 
                  ON ms.equipmentId = v.id AND ms.equipmentType = v.type_id
                WHERE v.location_name = ? 
                  AND ms.isActive = 1 
                  AND ms.nextDueDate >= CURDATE()
                ORDER BY ms.nextDueDate ASC 
                LIMIT 1
            ");
            $stmtNeighbor->execute([$locName]);
            $neighbor = $stmtNeighbor->fetch(PDO::FETCH_ASSOC);

            if ($neighbor && !empty($neighbor['nextDueDate'])) {
                $nextDueDate = $neighbor['nextDueDate'];
                if (!empty($neighbor['location_group_id'])) {
                    $locationGroupId = $neighbor['location_group_id'];
                }
            }
        }

        // Insert or Ignore (to prevent duplicates)
        $sql = "INSERT IGNORE INTO tbl_maintenance_schedule 
                (equipmentType, equipmentId, maintenanceFrequency, nextDueDate, isActive, location_group_id, is_synced) 
                VALUES (?, ?, ?, ?, 1, ?, 1)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$typeId, $equipmentId, $frequency, $nextDueDate, $locationGroupId]);
    }
}
?>