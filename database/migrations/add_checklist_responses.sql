-- ============================================================
-- Migration: Normalized checklist response storage
-- Date: 2026-02-20
--
-- Adds:
--   1. templateId column to tbl_maintenance_record
--   2. tbl_maintenance_response — one row per checklist item
--      per maintenance record, with FK references to the
--      template item that was answered.
--
-- The existing checklistJson column is kept as a read-only
-- backup for backward compatibility with older records.
-- ============================================================

-- 1. Add templateId to tbl_maintenance_record
ALTER TABLE `tbl_maintenance_record`
    ADD COLUMN `templateId` INT(11) DEFAULT NULL
        COMMENT 'FK → tbl_maintenance_template.templateId — which template was used'
        AFTER `scheduleId`;

-- Add index for the new FK
ALTER TABLE `tbl_maintenance_record`
    ADD INDEX `idx_record_template` (`templateId`);

-- 2. Create the normalised response table
CREATE TABLE IF NOT EXISTS `tbl_maintenance_response` (
    `responseId`      INT(11)      NOT NULL AUTO_INCREMENT,
    `recordId`        INT(11)      NOT NULL  COMMENT 'FK → tbl_maintenance_record.recordId',
    `itemId`          INT(11)      DEFAULT NULL COMMENT 'FK → tbl_checklist_item.itemId (NULL for legacy/unlinked)',
    `categoryId`      INT(11)      DEFAULT NULL COMMENT 'FK → tbl_checklist_category.categoryId',
    `categoryName`    VARCHAR(150) NOT NULL  COMMENT 'Snapshot of category name at time of inspection',
    `taskDescription` TEXT         NOT NULL  COMMENT 'Snapshot of task text at time of inspection',
    `response`        ENUM('Yes','No','N/A') NOT NULL DEFAULT 'N/A'
                      COMMENT 'The technician''s answer',
    `sequenceOrder`   INT(11)      NOT NULL DEFAULT 0
                      COMMENT 'Display order (preserves template ordering)',
    PRIMARY KEY (`responseId`),
    INDEX `idx_resp_record`   (`recordId`),
    INDEX `idx_resp_item`     (`itemId`),
    INDEX `idx_resp_category` (`categoryId`),
    CONSTRAINT `fk_resp_record`   FOREIGN KEY (`recordId`)   REFERENCES `tbl_maintenance_record`(`recordId`)   ON DELETE CASCADE,
    CONSTRAINT `fk_resp_item`     FOREIGN KEY (`itemId`)     REFERENCES `tbl_checklist_item`(`itemId`)         ON DELETE SET NULL,
    CONSTRAINT `fk_resp_category` FOREIGN KEY (`categoryId`) REFERENCES `tbl_checklist_category`(`categoryId`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Individual checklist responses per maintenance record';
