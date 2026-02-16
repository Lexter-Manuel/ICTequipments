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
        // Calculate next due date (Today + Frequency)
        $nextDueDate = date('Y-m-d', strtotime("+$frequency days"));

        // Insert or Ignore (to prevent duplicates)
        $sql = "INSERT IGNORE INTO tbl_maintenance_schedule 
                (equipmentType, equipmentId, maintenanceFrequency, nextDueDate, isActive) 
                VALUES (?, ?, ?, ?, 1)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$typeId, $equipmentId, $frequency, $nextDueDate]);
    }
}
?>