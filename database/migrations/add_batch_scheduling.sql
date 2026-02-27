-- ============================================================
-- Migration: Batch Maintenance Scheduling
-- Date: 2026-02-27
-- Description: Adds batch grouping, sync tracking, and metrics
-- 
-- ROLLBACK:
--   ALTER TABLE tbl_maintenance_schedule DROP COLUMN location_group_id;
--   ALTER TABLE tbl_maintenance_schedule DROP COLUMN is_synced;
--   ALTER TABLE tbl_maintenance_schedule DROP COLUMN frequency_override_days;
--   DROP TABLE IF EXISTS tbl_maintenance_metrics;
-- ============================================================

-- 1. Add batch grouping columns to schedule table
ALTER TABLE tbl_maintenance_schedule
  ADD COLUMN location_group_id INT NULL 
    COMMENT 'The unit/section location_id this schedule was batched with'
    AFTER isActive,
  ADD COLUMN is_synced TINYINT(1) DEFAULT 1 
    COMMENT '1 = still aligned with group schedule, 0 = diverged (maintained off-cycle)'
    AFTER location_group_id,
  ADD COLUMN frequency_override_days INT NULL 
    COMMENT 'System-suggested frequency in days based on metrics (NULL = use maintenanceFrequency)'
    AFTER is_synced,
  ADD INDEX idx_location_group (location_group_id);

-- 2. Create maintenance metrics table for frequency intelligence
CREATE TABLE IF NOT EXISTS tbl_maintenance_metrics (
  metricId INT AUTO_INCREMENT PRIMARY KEY,
  equipmentType VARCHAR(50) NOT NULL COMMENT 'typeId from registry',
  equipmentId INT NOT NULL,
  avg_interval_days DECIMAL(8,1) NULL COMMENT 'Average days between maintenance events',
  total_records INT DEFAULT 0 COMMENT 'Total maintenance records for this equipment',
  off_schedule_count INT DEFAULT 0 COMMENT 'Times maintained >7 days away from scheduled date',
  last_computed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  suggested_frequency ENUM('Monthly','Quarterly','Semi-Annual','Annual') NULL 
    COMMENT 'Computed optimal frequency based on history',
  UNIQUE INDEX idx_equipment (equipmentType, equipmentId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
