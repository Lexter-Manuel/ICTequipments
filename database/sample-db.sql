SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `tbl_equipment_type_registry` (
  `typeId` INT(11) NOT NULL AUTO_INCREMENT,
  `typeName` VARCHAR(50) NOT NULL, -- 'System Unit', 'Laptop', 'CCTV'
  `tableName` VARCHAR(50) NOT NULL, -- 'tbl_systemunit', 'tbl_otherequipment'
  `pkColumn` VARCHAR(50) NOT NULL, -- 'systemunitId', 'otherEquipmentId'
  `filterClause` VARCHAR(255) DEFAULT NULL, -- "equipmentType = 'Laptop'"
  `defaultFrequency` INT DEFAULT 180, -- Default to 180 days (Semi-Annual)
  `context` ENUM('Employee', 'Location') NOT NULL DEFAULT 'Location',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`typeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Clear table to ensure clean seed (Optional - remove if preserving custom types)
-- TRUNCATE TABLE `tbl_equipment_type_registry`;

-- Seed Data
INSERT INTO `tbl_equipment_type_registry` (`typeName`, `tableName`, `pkColumn`, `context`, `filterClause`, `defaultFrequency`) VALUES 
-- Dedicated Tables (Personal Assets)
('System Unit', 'tbl_systemunit', 'systemunitId', 'Employee', NULL, 180),
('All-in-One', 'tbl_allinone', 'allinoneId', 'Employee', NULL, 180),
('Monitor', 'tbl_monitor', 'monitorId', 'Employee', NULL, 180),
('Printer', 'tbl_printer', 'printerId', 'Employee', NULL, 180),

-- Shared Table (Employee Context)
('Laptop', 'tbl_otherequipment', 'otherEquipmentId', 'Employee', "equipmentType = 'Laptop'", 180),
('Mouse', 'tbl_otherequipment', 'otherEquipmentId', 'Employee', "equipmentType = 'Mouse'", 180),
('Keyboard', 'tbl_otherequipment', 'otherEquipmentId', 'Employee', "equipmentType = 'Keyboard'", 180),

-- Shared Table (Location Context - Infrastructure)
('CCTV System', 'tbl_otherequipment', 'otherEquipmentId', 'Location', "equipmentType = 'CCTV'", 180),
('Network Storage (NAS)', 'tbl_otherequipment', 'otherEquipmentId', 'Location', "equipmentType = 'NAS'", 180),
('Network Switch', 'tbl_otherequipment', 'otherEquipmentId', 'Location', "equipmentType = 'Switch'", 180),
('Projector', 'tbl_otherequipment', 'otherEquipmentId', 'Location', "equipmentType = 'Projector'", 180),
('Other Infrastructure', 'tbl_otherequipment', 'otherEquipmentId', 'Location', "equipmentType NOT IN ('CCTV', 'NAS', 'Switch', 'Projector', 'Laptop', 'Mouse', 'Keyboard')", 180);

-- Frequencies
CREATE TABLE IF NOT EXISTS `tbl_maintenance_frequency` (
  `frequencyId` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL, -- 'Monthly', 'Quarterly'
  `intervalDays` INT(11) NOT NULL,
  PRIMARY KEY (`frequencyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `tbl_maintenance_frequency` (`frequencyId`, `name`, `intervalDays`) VALUES 
(1, 'Monthly', 30), 
(2, 'Quarterly', 90), 
(3, 'Semi-Annual', 180), 
(4, 'Annual', 365);

-- Templates (Form Headers)
CREATE TABLE IF NOT EXISTS `tbl_maintenance_template` (
  `templateId` INT(11) NOT NULL AUTO_INCREMENT,
  `templateName` VARCHAR(100) NOT NULL,
  `targetTypeId` INT(11) NOT NULL, -- Links to registry
  `frequencyId` INT(11) NOT NULL DEFAULT 3, -- Default Semi-Annual
  `isActive` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`templateId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Checklist Categories (Sections within the Form)
CREATE TABLE IF NOT EXISTS `tbl_checklist_category` (
  `categoryId` INT(11) NOT NULL AUTO_INCREMENT,
  `templateId` INT(11) NOT NULL,
  `categoryName` VARCHAR(100) NOT NULL,
  `sequenceOrder` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`categoryId`),
  KEY `idx_template` (`templateId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Checklist Items (The Questions)
CREATE TABLE IF NOT EXISTS `tbl_checklist_item` (
  `itemId` INT(11) NOT NULL AUTO_INCREMENT,
  `categoryId` INT(11) NOT NULL,
  `taskDescription` TEXT NOT NULL,
  `sequenceOrder` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`itemId`),
  KEY `idx_category` (`categoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_maintenance_schedule` (
  `scheduleId` INT(11) NOT NULL AUTO_INCREMENT,
  `equipmentType` VARCHAR(50) NOT NULL COMMENT 'System Unit, Monitor, Printer, Laptop, etc.',
  `equipmentId` INT(11) NOT NULL COMMENT 'ID from the specific equipment table',
  `maintenanceFrequency` ENUM('Monthly', 'Quarterly', 'Semi-Annual', 'Annual') NOT NULL DEFAULT 'Semi-Annual',
  `lastMaintenanceDate` DATE DEFAULT NULL COMMENT 'When was it last maintained?',
  `nextDueDate` DATE NOT NULL COMMENT 'When is next maintenance due?',
  `isActive` TINYINT(1) DEFAULT 1,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`scheduleId`),
  KEY `idx_equipment` (`equipmentType`, `equipmentId`),
  KEY `idx_due_date` (`nextDueDate`),
  KEY `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Maintenance schedule for equipment';

-- Records (History Log)
CREATE TABLE IF NOT EXISTS `tbl_maintenance_record` (
  `recordId` INT(11) NOT NULL AUTO_INCREMENT,
  `scheduleId` INT(11) NOT NULL,
  `equipmentTypeId` INT(11) NOT NULL,
  `equipmentId` INT(11) NOT NULL,
  `accountId` INT(11) NOT NULL, -- Links to your users/accounts table
  `maintenanceDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  -- The Snapshot: Stores the filled checklist as JSON
  `checklistJson` LONGTEXT DEFAULT NULL, 
  
  `remarks` TEXT DEFAULT NULL,
  `overallStatus` ENUM('Operational', 'For Replacement', 'Disposed') NOT NULL DEFAULT 'Operational',
  `conditionRating` ENUM('Excellent', 'Good', 'Fair', 'Poor') NOT NULL DEFAULT 'Good',
  
  -- Approvals / Signatories
  `preparedBy` VARCHAR(100) DEFAULT NULL,
  `checkedBy` VARCHAR(100) DEFAULT NULL,
  `notedBy` VARCHAR(100) DEFAULT NULL,
--   `approvalStatus` ENUM('Draft', 'Pending', 'Approved', 'Rejected') DEFAULT 'Draft',
  
  PRIMARY KEY (`recordId`),
  KEY `idx_main_lookup` (`equipmentTypeId`, `equipmentId`)
  KEY `idx_schedule` (`scheduleId`),
  KEY `idx_equipment` (`equipmentTypeId`, `equipmentId`),
  KEY `idx_date` (`maintenanceDate`),

  CONSTRAINT `fk_history_schedule` FOREIGN KEY (`scheduleId`) 
    REFERENCES `tbl_maintenance_schedule`(`scheduleId`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE OR REPLACE VIEW view_maintenance_master AS
SELECT 
    base.*,
    -- Smart Location Logic: 
    -- If location is a Unit (Type 3), the Section is the Parent.
    -- If location is a Section (Type 2), it IS the Section.
    CASE 
        WHEN l.location_type_id = 3 THEN parent.location_name 
        WHEN l.location_type_id = 2 THEN l.location_name
        ELSE 'N/A'
    END AS section_name,
    
    CASE 
        WHEN l.location_type_id = 3 THEN parent.location_id 
        WHEN l.location_type_id = 2 THEN l.location_id
        ELSE NULL
    END AS section_id,

    -- Unit Name (Only if it is a unit)
    CASE 
        WHEN l.location_type_id = 3 THEN l.location_name
        ELSE 'Direct Section Assignment'
    END AS unit_name

FROM (
    -- 1. System Units
    SELECT 
        'System Unit' AS type_name, r.typeId AS type_id, s.systemunitId AS id,
        s.systemUnitBrand AS brand, s.systemUnitSerial AS serial,
        CONCAT(e.firstName, ' ', e.lastName) AS owner_name,
        e.location_id -- We use this to join below
    FROM tbl_systemunit s
    JOIN tbl_equipment_type_registry r ON r.tableName = 'tbl_systemunit'
    LEFT JOIN tbl_employee e ON s.employeeId = e.employeeId

    UNION ALL

    -- 2. Monitors
    SELECT 
        'Monitor' AS type_name, r.typeId AS type_id, m.monitorId AS id,
        m.monitorBrand AS brand, m.monitorSerial AS serial,
        CONCAT(e.firstName, ' ', e.lastName) AS owner_name,
        e.location_id
    FROM tbl_monitor m
    JOIN tbl_equipment_type_registry r ON r.tableName = 'tbl_monitor'
    LEFT JOIN tbl_employee e ON m.employeeId = e.employeeId

    UNION ALL 

    -- 3. Printers
    SELECT 
        'Printer' AS type_name, r.typeId AS type_id, p.printerId AS id,
        p.printerBrand AS brand, p.printerSerial AS serial,
        CONCAT(e.firstName, ' ', e.lastName) AS owner_name,
        e.location_id
    FROM tbl_printer p
    JOIN tbl_equipment_type_registry r ON r.tableName = 'tbl_printer'
    LEFT JOIN tbl_employee e ON p.employeeId = e.employeeId

    UNION ALL

    -- 4. Other Equipment
    SELECT 
        o.equipmentType AS type_name, r.typeId AS type_id, o.otherEquipmentId AS id,
        o.brand AS brand, o.serialNumber AS serial,
        CASE WHEN o.employeeId IS NOT NULL THEN CONCAT(e.firstName, ' ', e.lastName) ELSE 'Unassigned' END AS owner_name,
        CASE WHEN o.employeeId IS NOT NULL THEN e.location_id ELSE o.location_id END AS location_id
    FROM tbl_otherequipment o
    JOIN tbl_equipment_type_registry r ON r.tableName = 'tbl_otherequipment' AND o.equipmentType = r.typeName
    LEFT JOIN tbl_employee e ON o.employeeId = e.employeeId

) AS base
-- Join Location Data
LEFT JOIN location l ON base.location_id = l.location_id
LEFT JOIN location parent ON l.parent_location_id = parent.location_id;

COMMIT;

CREATE OR REPLACE VIEW view_overdue_maintenance AS
SELECT 
    ms.scheduleId,
    ms.equipmentType,
    ms.equipmentId,
    ms.maintenanceFrequency,
    ms.lastMaintenanceDate,
    ms.nextDueDate,
    DATEDIFF(CURDATE(), ms.nextDueDate) as days_overdue
FROM tbl_maintenance_schedule ms
WHERE ms.nextDueDate < CURDATE() 
    AND ms.isActive = 1
ORDER BY days_overdue DESC;

-- View: Equipment due soon (within 7 days)
CREATE OR REPLACE VIEW view_due_soon_maintenance AS
SELECT 
    ms.scheduleId,
    ms.equipmentType,
    ms.equipmentId,
    ms.maintenanceFrequency,
    ms.lastMaintenanceDate,
    ms.nextDueDate,
    DATEDIFF(ms.nextDueDate, CURDATE()) as days_until_due
FROM tbl_maintenance_schedule ms
WHERE ms.nextDueDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND ms.isActive = 1
ORDER BY days_until_due ASC;

-- View: Complete maintenance dashboard with equipment details
-- NOTE: This is a simplified version. You may need to adjust based on your actual equipment tables
CREATE OR REPLACE VIEW view_maintenance_dashboard AS
SELECT 
    ms.scheduleId,
    ms.equipmentType,
    ms.equipmentId,
    ms.maintenanceFrequency,
    ms.lastMaintenanceDate,
    ms.nextDueDate,
    DATEDIFF(ms.nextDueDate, CURDATE()) as days_until_due,
    
    -- Status calculation
    CASE 
        WHEN ms.nextDueDate < CURDATE() THEN 'overdue'
        WHEN DATEDIFF(ms.nextDueDate, CURDATE()) <= 7 THEN 'due_soon'
        ELSE 'ok'
    END as status,
    
    -- Get last maintenance history
    (SELECT mh.maintenanceDate 
     FROM tbl_maintenance_history mh 
     WHERE mh.scheduleId = ms.scheduleId 
     ORDER BY mh.maintenanceDate DESC LIMIT 1) as actual_last_maintenance,
    
    (SELECT mh.conditionRating 
     FROM tbl_maintenance_history mh 
     WHERE mh.scheduleId = ms.scheduleId 
     ORDER BY mh.maintenanceDate DESC LIMIT 1) as last_condition_rating
     
FROM tbl_maintenance_schedule ms
WHERE ms.isActive = 1;

COMMIT;

-- ==========================================
-- THE VIEW (The Dashboard Feed)
-- ==========================================

CREATE OR REPLACE VIEW view_maintenance_due AS
SELECT 
    ms.scheduleId,
    r.typeName,
    ms.equipmentId,
    ms.lastMaintenanceDate,
    ms.nextDueDate,
    DATEDIFF(ms.nextDueDate, CURDATE()) as daysUntilDue,
    CASE 
        WHEN ms.nextDueDate < CURDATE() THEN 'Overdue'
        WHEN DATEDIFF(ms.nextDueDate, CURDATE()) <= 7 THEN 'Due Soon'
        ELSE 'Scheduled'
    END as status
FROM tbl_maintenance_schedule ms
JOIN tbl_equipment_type_registry r ON ms.equipmentTypeId = r.typeId
WHERE ms.isActive = 1;