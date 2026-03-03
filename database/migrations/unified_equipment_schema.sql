-- ============================================================================
-- Migration: Unified Equipment Schema
-- Date: 2026-03-03
-- Description: Consolidates tbl_systemunit, tbl_allinone, tbl_monitor,
--              tbl_printer, tbl_otherequipment into a single tbl_equipment
--              table with a tbl_equipment_specs EAV table for type-specific
--              attributes. Also simplifies tbl_equipment_type_registry.
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================================
-- STEP 1: Create the new unified equipment table
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_equipment` (
    `equipment_id` INT(11) NOT NULL AUTO_INCREMENT,
    `type_id` INT(11) NOT NULL COMMENT 'FK to tbl_equipment_type_registry.typeId',
    `employee_id` INT(11) DEFAULT NULL COMMENT 'Assigned employee (NULL = unassigned)',
    `location_id` INT(11) DEFAULT NULL COMMENT 'Direct location assignment (for location-context equipment like CCTV)',
    `brand` VARCHAR(100) DEFAULT NULL,
    `model` VARCHAR(100) DEFAULT NULL,
    `serial_number` VARCHAR(255) DEFAULT NULL,
    `property_number` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('Available','In Use','Under Maintenance','Disposed') NOT NULL DEFAULT 'Available',
    `year_acquired` YEAR(4) DEFAULT NULL,
    `acquisition_date` DATE DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `is_archived` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`equipment_id`),
    UNIQUE KEY `uniq_type_serial` (`type_id`, `serial_number`),
    KEY `idx_type` (`type_id`),
    KEY `idx_employee` (`employee_id`),
    KEY `idx_location` (`location_id`),
    KEY `idx_status` (`status`),
    KEY `idx_archived` (`is_archived`),
    CONSTRAINT `fk_equipment_type` FOREIGN KEY (`type_id`) REFERENCES `tbl_equipment_type_registry` (`typeId`),
    CONSTRAINT `fk_equipment_employee` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee` (`employeeId`) ON DELETE SET NULL,
    CONSTRAINT `fk_equipment_location` FOREIGN KEY (`location_id`) REFERENCES `location` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- STEP 2: Create the equipment specs (EAV) table
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tbl_equipment_specs` (
    `spec_id` INT(11) NOT NULL AUTO_INCREMENT,
    `equipment_id` INT(11) NOT NULL,
    `spec_key` VARCHAR(50) NOT NULL COMMENT 'e.g. Processor, Memory, GPU, Storage, Monitor Size, Category',
    `spec_value` TEXT DEFAULT NULL,
    PRIMARY KEY (`spec_id`),
    KEY `idx_equipment` (`equipment_id`),
    KEY `idx_spec_key` (`spec_key`),
    UNIQUE KEY `uniq_equipment_spec` (`equipment_id`, `spec_key`),
    CONSTRAINT `fk_spec_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `tbl_equipment` (`equipment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- STEP 3: Migrate data from old tables into tbl_equipment + tbl_equipment_specs
-- ============================================================================

-- 3a. Migrate System Units (typeId = 1)
INSERT INTO `tbl_equipment` (`equipment_id`, `type_id`, `employee_id`, `location_id`, `brand`, `model`, `serial_number`, `status`, `year_acquired`, `created_at`)
SELECT 
    `systemunitId`,
    1,
    `employeeId`,
    (SELECT `e`.`location_id` FROM `tbl_employee` `e` WHERE `e`.`employeeId` = `s`.`employeeId` LIMIT 1),
    `systemUnitBrand`,
    COALESCE(`systemUnitCategory`, ''),
    `systemUnitSerial`,
    CASE WHEN `employeeId` IS NOT NULL THEN 'In Use' ELSE 'Available' END,
    `yearAcquired`,
    CURRENT_TIMESTAMP
FROM `tbl_systemunit` `s`;

-- System unit specs
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `systemunitId`, 'Category', `systemUnitCategory` FROM `tbl_systemunit` WHERE `systemUnitCategory` IS NOT NULL AND `systemUnitCategory` != '';
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `systemunitId`, 'Processor', `specificationProcessor` FROM `tbl_systemunit` WHERE `specificationProcessor` IS NOT NULL AND `specificationProcessor` != '';
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `systemunitId`, 'Memory', `specificationMemory` FROM `tbl_systemunit` WHERE `specificationMemory` IS NOT NULL AND `specificationMemory` != '';
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `systemunitId`, 'GPU', `specificationGPU` FROM `tbl_systemunit` WHERE `specificationGPU` IS NOT NULL AND `specificationGPU` != '';
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `systemunitId`, 'Storage', `specificationStorage` FROM `tbl_systemunit` WHERE `specificationStorage` IS NOT NULL AND `specificationStorage` != '';

-- 3b. Migrate All-in-Ones (typeId = 2)
-- Use offset to avoid ID collision: allinone IDs + 10000
INSERT INTO `tbl_equipment` (`equipment_id`, `type_id`, `employee_id`, `location_id`, `brand`, `serial_number`, `status`, `year_acquired`, `created_at`)
SELECT 
    `allinoneId` + 10000,
    2,
    `employeeId`,
    (SELECT `e`.`location_id` FROM `tbl_employee` `e` WHERE `e`.`employeeId` = `a`.`employeeId` LIMIT 1),
    `allinoneBrand`,
    NULL,
    CASE WHEN `employeeId` IS NOT NULL THEN 'In Use' ELSE 'Available' END,
    NULL,
    CURRENT_TIMESTAMP
FROM `tbl_allinone` `a`;

-- All-in-one specs
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `allinoneId` + 10000, 'Processor', `specificationProcessor` FROM `tbl_allinone` WHERE `specificationProcessor` IS NOT NULL AND `specificationProcessor` != '';
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `allinoneId` + 10000, 'Memory', `specificationMemory` FROM `tbl_allinone` WHERE `specificationMemory` IS NOT NULL AND `specificationMemory` != '';
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `allinoneId` + 10000, 'GPU', `specificationGPU` FROM `tbl_allinone` WHERE `specificationGPU` IS NOT NULL AND `specificationGPU` != '';
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `allinoneId` + 10000, 'Storage', `specificationStorage` FROM `tbl_allinone` WHERE `specificationStorage` IS NOT NULL AND `specificationStorage` != '';

-- 3c. Migrate Monitors (typeId = 3)
-- Use offset: monitorId + 20000
INSERT INTO `tbl_equipment` (`equipment_id`, `type_id`, `employee_id`, `location_id`, `brand`, `serial_number`, `status`, `year_acquired`, `created_at`)
SELECT 
    `monitorId` + 20000,
    3,
    `employeeId`,
    (SELECT `e`.`location_id` FROM `tbl_employee` `e` WHERE `e`.`employeeId` = `m`.`employeeId` LIMIT 1),
    `monitorBrand`,
    `monitorSerial`,
    CASE WHEN `employeeId` IS NOT NULL THEN 'In Use' ELSE 'Available' END,
    `yearAcquired`,
    CURRENT_TIMESTAMP
FROM `tbl_monitor` `m`;

-- Monitor specs
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `monitorId` + 20000, 'Monitor Size', `monitorSize` FROM `tbl_monitor` WHERE `monitorSize` IS NOT NULL AND `monitorSize` != '';

-- 3d. Migrate Printers (typeId = 4)
-- Use offset: printerId + 30000
INSERT INTO `tbl_equipment` (`equipment_id`, `type_id`, `employee_id`, `location_id`, `brand`, `model`, `serial_number`, `status`, `year_acquired`, `created_at`)
SELECT 
    `printerId` + 30000,
    4,
    `employeeId`,
    (SELECT `e`.`location_id` FROM `tbl_employee` `e` WHERE `e`.`employeeId` = `p`.`employeeId` LIMIT 1),
    `printerBrand`,
    `printerModel`,
    `printerSerial`,
    CASE WHEN `employeeId` IS NOT NULL THEN 'In Use' ELSE 'Available' END,
    `yearAcquired`,
    CURRENT_TIMESTAMP
FROM `tbl_printer` `p`;

-- 3e. Migrate Other Equipment (typeId varies based on registry)
-- Use offset: otherEquipmentId + 40000
INSERT INTO `tbl_equipment` (`equipment_id`, `type_id`, `employee_id`, `location_id`, `brand`, `model`, `serial_number`, `status`, `year_acquired`, `created_at`)
SELECT 
    `o`.`otherEquipmentId` + 40000,
    COALESCE(
        (SELECT `r`.`typeId` FROM `tbl_equipment_type_registry` `r` 
         WHERE `r`.`tableName` = 'tbl_otherequipment' AND `r`.`typeName` = `o`.`equipmentType` LIMIT 1),
        5
    ),
    `o`.`employeeId`,
    `o`.`location_id`,
    `o`.`brand`,
    `o`.`model`,
    `o`.`serialNumber`,
    `o`.`status`,
    `o`.`yearAcquired`,
    `o`.`createdAt`
FROM `tbl_otherequipment` `o`;

-- Other equipment specs
INSERT INTO `tbl_equipment_specs` (`equipment_id`, `spec_key`, `spec_value`)
SELECT `otherEquipmentId` + 40000, 'Details', `details` FROM `tbl_otherequipment` WHERE `details` IS NOT NULL AND `details` != '';

-- ============================================================================
-- STEP 4: Create equipment ID mapping table (for updating maintenance records)
-- This maps old (typeId, equipmentId) to new equipment_id
-- ============================================================================

CREATE TEMPORARY TABLE `_equipment_id_map` (
    `old_type_id` INT,
    `old_equipment_id` INT,
    `new_equipment_id` INT,
    PRIMARY KEY (`old_type_id`, `old_equipment_id`)
);

-- System Units: typeId=1, old=systemunitId, new=systemunitId
INSERT INTO `_equipment_id_map` SELECT 1, `systemunitId`, `systemunitId` FROM `tbl_systemunit`;
-- All-in-Ones: typeId=2, old=allinoneId, new=allinoneId+10000
INSERT INTO `_equipment_id_map` SELECT 2, `allinoneId`, `allinoneId` + 10000 FROM `tbl_allinone`;
-- Monitors: typeId=3, old=monitorId, new=monitorId+20000
INSERT INTO `_equipment_id_map` SELECT 3, `monitorId`, `monitorId` + 20000 FROM `tbl_monitor`;
-- Printers: typeId=4, old=printerId, new=printerId+30000
INSERT INTO `_equipment_id_map` SELECT 4, `printerId`, `printerId` + 30000 FROM `tbl_printer`;
-- Other equipment: typeId from registry, old=otherEquipmentId, new=otherEquipmentId+40000
INSERT INTO `_equipment_id_map`
SELECT 
    COALESCE(
        (SELECT `r`.`typeId` FROM `tbl_equipment_type_registry` `r` 
         WHERE `r`.`tableName` = 'tbl_otherequipment' AND `r`.`typeName` = `o`.`equipmentType` LIMIT 1),
        5
    ),
    `o`.`otherEquipmentId`,
    `o`.`otherEquipmentId` + 40000
FROM `tbl_otherequipment` `o`;

-- ============================================================================
-- STEP 5: Update maintenance_schedule to reference new equipment_id
-- ============================================================================

UPDATE `tbl_maintenance_schedule` `ms`
INNER JOIN `_equipment_id_map` `m` ON `ms`.`equipmentType` = `m`.`old_type_id` AND `ms`.`equipmentId` = `m`.`old_equipment_id`
SET `ms`.`equipmentId` = `m`.`new_equipment_id`;

-- ============================================================================
-- STEP 6: Update maintenance_record to reference new equipment_id
-- ============================================================================

UPDATE `tbl_maintenance_record` `mr`
INNER JOIN `_equipment_id_map` `m` ON `mr`.`equipmentTypeId` = `m`.`old_type_id` AND `mr`.`equipmentId` = `m`.`old_equipment_id`
SET `mr`.`equipmentId` = `m`.`new_equipment_id`;

-- ============================================================================
-- STEP 7: Simplify tbl_equipment_type_registry (remove table-mapping columns)
-- ============================================================================

ALTER TABLE `tbl_equipment_type_registry`
    DROP COLUMN `tableName`,
    DROP COLUMN `pkColumn`,
    DROP COLUMN `filterClause`;

-- ============================================================================
-- STEP 8: Recreate view_maintenance_master using the unified table
-- ============================================================================

DROP VIEW IF EXISTS `view_maintenance_master`;

CREATE VIEW `view_maintenance_master` AS
SELECT
    r.typeName AS type_name,
    r.typeId AS type_id,
    e.equipment_id AS id,
    e.brand,
    COALESCE(e.serial_number, 'N/A') AS serial,
    CASE 
        WHEN r.context = 'Employee' AND emp.employeeId IS NOT NULL 
        THEN CONCAT(emp.firstName, ' ', emp.lastName)
        ELSE 'N/A'
    END AS owner_name,
    CASE 
        WHEN r.context = 'Employee' AND emp.employeeId IS NOT NULL 
        THEN el.location_name
        WHEN r.context = 'Location' AND e.location_id IS NOT NULL
        THEN ll.location_name
        ELSE 'N/A'
    END AS location_name,
    r.context AS context
FROM tbl_equipment e
INNER JOIN tbl_equipment_type_registry r ON e.type_id = r.typeId
LEFT JOIN tbl_employee emp ON e.employee_id = emp.employeeId
LEFT JOIN location el ON emp.location_id = el.location_id
LEFT JOIN location ll ON e.location_id = ll.location_id
WHERE e.is_archived = 0;

-- ============================================================================
-- STEP 9: Recreate other maintenance views
-- ============================================================================

DROP VIEW IF EXISTS `view_maintenance_due`;

CREATE VIEW `view_maintenance_due` AS
SELECT 
    ms.scheduleId,
    r.typeName,
    ms.equipmentId,
    ms.lastMaintenanceDate,
    ms.nextDueDate,
    TO_DAYS(ms.nextDueDate) - TO_DAYS(CURDATE()) AS daysUntilDue,
    CASE 
        WHEN ms.nextDueDate < CURDATE() THEN 'Overdue'
        WHEN TO_DAYS(ms.nextDueDate) - TO_DAYS(CURDATE()) <= 7 THEN 'Due Soon'
        ELSE 'Scheduled'
    END AS status
FROM tbl_maintenance_schedule ms
JOIN tbl_equipment_type_registry r ON ms.equipmentType = r.typeId
WHERE ms.isActive = 1;

DROP VIEW IF EXISTS `view_due_soon_maintenance`;

CREATE VIEW `view_due_soon_maintenance` AS
SELECT 
    ms.scheduleId,
    ms.equipmentType,
    ms.equipmentId,
    ms.maintenanceFrequency,
    ms.lastMaintenanceDate,
    ms.nextDueDate,
    TO_DAYS(ms.nextDueDate) - TO_DAYS(CURDATE()) AS days_until_due
FROM tbl_maintenance_schedule ms
WHERE ms.nextDueDate BETWEEN CURDATE() AND CURDATE() + INTERVAL 7 DAY
AND ms.isActive = 1
ORDER BY days_until_due ASC;

DROP VIEW IF EXISTS `view_overdue_maintenance`;

CREATE VIEW `view_overdue_maintenance` AS
SELECT 
    ms.scheduleId,
    ms.equipmentType,
    ms.equipmentId,
    ms.maintenanceFrequency,
    ms.lastMaintenanceDate,
    ms.nextDueDate,
    TO_DAYS(CURDATE()) - TO_DAYS(ms.nextDueDate) AS days_overdue
FROM tbl_maintenance_schedule ms
WHERE ms.nextDueDate < CURDATE()
AND ms.isActive = 1
ORDER BY days_overdue DESC;

DROP VIEW IF EXISTS `view_maintenance_dashboard`;

CREATE VIEW `view_maintenance_dashboard` AS
SELECT 
    ms.scheduleId,
    ms.equipmentType,
    ms.equipmentId,
    ms.maintenanceFrequency,
    ms.lastMaintenanceDate,
    ms.nextDueDate,
    TO_DAYS(ms.nextDueDate) - TO_DAYS(CURDATE()) AS days_until_due,
    CASE 
        WHEN ms.nextDueDate < CURDATE() THEN 'overdue'
        WHEN TO_DAYS(ms.nextDueDate) - TO_DAYS(CURDATE()) <= 7 THEN 'due_soon'
        ELSE 'ok'
    END AS status,
    (SELECT mh.maintenanceDate FROM tbl_maintenance_record mh WHERE mh.scheduleId = ms.scheduleId ORDER BY mh.maintenanceDate DESC LIMIT 1) AS actual_last_maintenance,
    (SELECT mh.conditionRating FROM tbl_maintenance_record mh WHERE mh.scheduleId = ms.scheduleId ORDER BY mh.maintenanceDate DESC LIMIT 1) AS last_condition_rating
FROM tbl_maintenance_schedule ms
WHERE ms.isActive = 1;

-- ============================================================================
-- STEP 10: Drop old equipment tables (commented out for safety - uncomment when ready)
-- ============================================================================

-- DROP TABLE IF EXISTS `tbl_systemunit`;
-- DROP TABLE IF EXISTS `tbl_allinone`;
-- DROP TABLE IF EXISTS `tbl_monitor`;
-- DROP TABLE IF EXISTS `tbl_printer`;
-- DROP TABLE IF EXISTS `tbl_otherequipment`;

COMMIT;
