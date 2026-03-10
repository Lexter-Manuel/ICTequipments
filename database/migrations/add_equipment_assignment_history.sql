-- ═══════════════════════════════════════════════════════════════
-- Migration: Equipment Assignment History
-- Tracks every assignment/unassignment event for equipment
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `tbl_equipment_assignment_history` (
    `history_id`    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `equipment_id`  INT             NOT NULL COMMENT 'FK to tbl_equipment',
    `employee_id`   INT             DEFAULT NULL COMMENT 'FK to tbl_employee (NULL = unassigned)',
    `action`        ENUM('assigned','unassigned','transferred') NOT NULL,
    `assigned_at`   DATETIME        DEFAULT NULL COMMENT 'When this assignment started',
    `unassigned_at` DATETIME        DEFAULT NULL COMMENT 'When this assignment ended',
    `performed_by`  INT             DEFAULT NULL COMMENT 'User ID who performed the action',
    `remarks`       VARCHAR(500)    DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`history_id`),
    INDEX `idx_equipment` (`equipment_id`),
    INDEX `idx_employee` (`employee_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════════════════════════════════════════════════════════════
-- Seed existing assignments as initial history records
-- so current "In Use" equipment has a baseline entry.
-- ═══════════════════════════════════════════════════════════════
INSERT INTO `tbl_equipment_assignment_history`
    (`equipment_id`, `employee_id`, `action`, `assigned_at`, `remarks`, `created_at`)
SELECT
    e.equipment_id,
    e.employee_id,
    'assigned',
    COALESCE(e.acquisition_date, NOW()),
    'Initial record (seeded from existing assignment)',
    NOW()
FROM `tbl_equipment` e
WHERE e.employee_id IS NOT NULL
  AND e.is_archived = 0;
