-- =========================================================
-- Sample data for Maintenance History page testing
-- Run this AFTER importing nia-inventory-with-preventive.sql
-- =========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- =========================================================
-- 1. ADD EMPLOYEES across various locations
-- =========================================================
-- Existing: 1 = Lexter Manuel (ICT Unit, loc 4)
-- Existing: 645987 = Demi Xochitl (Pantabangan, loc 13)

INSERT INTO `tbl_employee` (`employeeId`, `firstName`, `middleName`, `lastName`, `suffixName`, `position`, `birthDate`, `sex`, `employmentStatus`, `photoPath`, `location_id`, `createdAt`, `updatedAt`, `is_active`) VALUES
(100, 'Juan',    'P.', 'Dela Cruz',  '',   'Project Engineer',     '1990-05-15', 'Male',   'Permanent', NULL, 18, NOW(), NULL, 1),  -- Engineering Section
(101, 'Maria',   'S.', 'Santos',     '',   'Accountant III',       '1988-08-22', 'Female', 'Permanent', NULL, 29, NOW(), NULL, 1),  -- Accounting Unit
(102, 'Pedro',   'R.', 'Reyes',      '',   'O&M Technician',       '1992-03-10', 'Male',   'Permanent', NULL, 19, NOW(), NULL, 1),  -- Operation Section
(103, 'Ana',     'G.', 'Garcia',     '',   'Property Custodian',   '1985-11-30', 'Female', 'Permanent', NULL, 11, NOW(), NULL, 1),  -- Property Unit
(104, 'Carlos',  'M.', 'Lopez',      '',   'Legal Officer II',     '1991-07-14', 'Male',   'Permanent', NULL,  6, NOW(), NULL, 1),  -- Legal Services
(105, 'Carmen',  'D.', 'Villar',     '',   'Records Officer',      '1993-01-25', 'Female', 'Permanent', NULL, 25, NOW(), NULL, 1),  -- Personnel and Records Unit
(106, 'Roberto', 'T.', 'Manalo',     '',   'Cashier II',           '1989-09-03', 'Male',   'Permanent', NULL, 15, NOW(), NULL, 1),  -- Cashiering Unit
(107, 'Elena',   'C.', 'Ramos',      '',   'PR Officer',           '1994-04-18', 'Female', 'Permanent', NULL,  5, NOW(), NULL, 1),  -- Public Relation Office Unit
(108, 'Miguel',  'A.', 'Torres',     '',   'BAC Secretary',        '1987-12-08', 'Male',   'Permanent', NULL, 22, NOW(), NULL, 1),  -- BAC Unit
(109, 'Sofia',   'L.', 'Mendoza',    '',   'Equipment Mgmt Staff', '1995-06-20', 'Female', 'Permanent', NULL, 20, NOW(), NULL, 1),  -- Equipment Management Section
(110, 'Jose',    'B.', 'Aquino',     '',   'IDS Staff',            '1996-02-11', 'Male',   'Casual',    NULL, 21, NOW(), NULL, 1),  -- Institutional Development Section
(111, 'Lucia',   'N.', 'Bautista',   '',   'FISA Analyst',         '1990-10-05', 'Female', 'Permanent', NULL, 16, NOW(), NULL, 1);  -- FISA Unit

-- =========================================================
-- 2. ADD SYSTEM UNITS for new employees
-- =========================================================
-- Existing: 1 (employee 645987), 2 (employee 645987)

INSERT INTO `tbl_systemunit` (`systemunitId`, `systemUnitCategory`, `systemUnitBrand`, `specificationProcessor`, `specificationMemory`, `specificationGPU`, `specificationStorage`, `systemUnitSerial`, `yearAcquired`, `employeeId`) VALUES
(10, 'Pre-Built',   'Dell OptiPlex 5070',    'Intel Core i5-9500',  '8GB DDR4',  'Intel UHD 630',       '256GB SSD',  'SU-2023-010', '2023', 100),
(11, 'Pre-Built',   'HP ProDesk 400 G6',     'Intel Core i5-9500T', '16GB DDR4', 'Intel UHD 630',       '512GB SSD',  'SU-2023-011', '2023', 101),
(12, 'Pre-Built',   'Lenovo ThinkCentre M70', 'Intel Core i3-10100', '8GB DDR4', 'Intel UHD 630',       '256GB SSD',  'SU-2024-012', '2024', 102),
(13, 'Pre-Built',   'Acer Veriton M200',     'Intel Core i5-10400', '8GB DDR4',  'Intel UHD 630',       '500GB HDD',  'SU-2023-013', '2023', 103),
(14, 'Pre-Built',   'Dell OptiPlex 3080',    'Intel Core i5-10500', '16GB DDR4', 'Intel UHD 630',       '512GB SSD',  'SU-2024-014', '2024', 104),
(15, 'Pre-Built',   'HP EliteDesk 800 G5',   'Intel Core i7-9700',  '16GB DDR4', 'Intel UHD 630',       '512GB SSD',  'SU-2022-015', '2022', 105),
(16, 'Pre-Built',   'Lenovo V530',           'Intel Core i3-9100',  '8GB DDR4',  'Intel UHD Graphics',  '256GB SSD',  'SU-2024-016', '2024', 106),
(17, 'Custom Built', 'Custom Build ICT',     'AMD Ryzen 7 5700X',   '32GB DDR4', 'NVIDIA RTX 3060',     '1TB NVMe',   'SU-2025-017', '2025', 1),
(18, 'Pre-Built',   'Dell Vostro 3710',      'Intel Core i5-12400', '8GB DDR4',  'Intel UHD 730',       '512GB SSD',  'SU-2025-018', '2025', 107),
(19, 'Pre-Built',   'HP ProDesk 405 G8',     'AMD Ryzen 5 5600G',   '16GB DDR4', 'AMD Radeon Graphics', '512GB SSD',  'SU-2024-019', '2024', 108),
(20, 'Pre-Built',   'Acer Veriton S2690',    'Intel Core i5-12400', '8GB DDR4',  'Intel UHD 730',       '256GB SSD',  'SU-2025-020', '2025', 109),
(21, 'Pre-Built',   'Lenovo ThinkCentre M80', 'Intel Core i5-10500', '16GB DDR4', 'Intel UHD 630',      '512GB SSD',  'SU-2023-021', '2023', 110),
(22, 'Pre-Built',   'Dell OptiPlex 7090',    'Intel Core i7-10700', '16GB DDR4',  'Intel UHD 630',      '1TB SSD',    'SU-2023-022', '2023', 111);

-- =========================================================
-- 3. ADD MONITORS for new employees
-- =========================================================
-- Existing: 15-20 (20 = employee 1)

INSERT INTO `tbl_monitor` (`monitorId`, `monitorBrand`, `monitorSize`, `monitorSerial`, `yearAcquired`, `employeeId`) VALUES
(30, 'Dell P2422H',          '24 inches', 'MO-2023-030', '2023', 100),
(31, 'LG 24MK430H',          '24 inches', 'MO-2023-031', '2023', 101),
(32, 'Samsung S24C450',       '24 inches', 'MO-2024-032', '2024', 102),
(33, 'HP V24e G5',            '24 inches', 'MO-2023-033', '2023', 103),
(34, 'Dell E2420H',           '24 inches', 'MO-2024-034', '2024', 104),
(35, 'LG 22MK430H',          '22 inches', 'MO-2022-035', '2022', 105),
(36, 'Acer V246HQL',          '24 inches', 'MO-2024-036', '2024', 106),
(37, 'Dell P2723QE',          '27 inches', 'MO-2025-037', '2025', 1),
(38, 'Samsung LS24C360',      '24 inches', 'MO-2025-038', '2025', 107),
(39, 'HP M24fw',              '24 inches', 'MO-2024-039', '2024', 108),
(40, 'LG 24MP400',            '24 inches', 'MO-2025-040', '2025', 109),
(41, 'Dell SE2422H',          '24 inches', 'MO-2023-041', '2023', 110),
(42, 'Acer KA242Y',           '24 inches', 'MO-2023-042', '2023', 111);

-- =========================================================
-- 4. ADD PRINTERS (shared across some units)
-- =========================================================
-- Existing: 4,5 (no employee), 7,8 (employee 645987)

INSERT INTO `tbl_printer` (`printerId`, `printerBrand`, `printerModel`, `printerSerial`, `yearAcquired`, `employeeId`) VALUES
(20, 'Epson L3150',            'EcoTank L3150',      'PR-2023-020', '2023', 100),
(21, 'HP LaserJet Pro M404n',  'M404n',              'PR-2024-021', '2024', 101),
(22, 'Canon PIXMA G3010',      'G3010',              'PR-2023-022', '2023', 103),
(23, 'Epson L5290',            'EcoTank L5290',      'PR-2024-023', '2024', 105),
(24, 'HP LaserJet M110we',     'M110we',             'PR-2025-024', '2025', 1),
(25, 'Brother DCP-T520W',      'DCP-T520W',          'PR-2024-025', '2024', 107),
(26, 'Epson L3110',            'EcoTank L3110',      'PR-2023-026', '2023', 108);

-- =========================================================
-- 5. ADD MAINTENANCE SCHEDULES for new equipment
-- =========================================================
-- Existing schedules: 2-16

-- System Unit schedules
INSERT INTO `tbl_maintenance_schedule` (`scheduleId`, `equipmentType`, `equipmentId`, `maintenanceFrequency`, `lastMaintenanceDate`, `nextDueDate`, `isActive`, `createdAt`, `updatedAt`) VALUES
(20, '1', 10, 'Semi-Annual', '2026-02-10', '2026-08-10', 1, NOW(), NOW()),
(21, '1', 11, 'Semi-Annual', '2026-02-12', '2026-08-12', 1, NOW(), NOW()),
(22, '1', 12, 'Semi-Annual', '2026-02-08', '2026-08-08', 1, NOW(), NOW()),
(23, '1', 13, 'Semi-Annual', '2026-02-01', '2026-08-01', 1, NOW(), NOW()),
(24, '1', 14, 'Semi-Annual', '2026-02-06', '2026-08-06', 1, NOW(), NOW()),
(25, '1', 15, 'Semi-Annual', '2026-01-28', '2026-07-28', 1, NOW(), NOW()),
(26, '1', 16, 'Semi-Annual', '2026-02-03', '2026-08-03', 1, NOW(), NOW()),
(27, '1', 17, 'Semi-Annual', '2026-02-15', '2026-08-15', 1, NOW(), NOW()),
(28, '1', 18, 'Semi-Annual', '2026-01-30', '2026-07-30', 1, NOW(), NOW()),
(29, '1', 19, 'Semi-Annual', '2026-01-30', '2026-07-30', 1, NOW(), NOW()),
(30, '1', 20, 'Semi-Annual', '2026-01-20', '2026-07-20', 1, NOW(), NOW()),
(31, '1', 21, 'Semi-Annual', '2026-01-20', '2026-07-20', 1, NOW(), NOW()),
(32, '1', 22, 'Semi-Annual', '2026-02-14', '2026-08-14', 1, NOW(), NOW()),

-- Monitor schedules
(40, '3', 30, 'Semi-Annual', '2026-02-10', '2026-08-10', 1, NOW(), NOW()),
(41, '3', 31, 'Semi-Annual', '2026-02-12', '2026-08-12', 1, NOW(), NOW()),
(42, '3', 32, 'Semi-Annual', '2026-02-08', '2026-08-08', 1, NOW(), NOW()),
(43, '3', 33, 'Semi-Annual', '2026-02-01', '2026-08-01', 1, NOW(), NOW()),
(44, '3', 34, 'Semi-Annual', '2026-02-06', '2026-08-06', 1, NOW(), NOW()),
(45, '3', 35, 'Semi-Annual', '2026-01-28', '2026-07-28', 1, NOW(), NOW()),
(46, '3', 36, 'Semi-Annual', '2026-02-03', '2026-08-03', 1, NOW(), NOW()),
(47, '3', 37, 'Semi-Annual', '2026-02-15', '2026-08-15', 1, NOW(), NOW()),
(48, '3', 38, 'Semi-Annual', '2026-01-30', '2026-07-30', 1, NOW(), NOW()),
(49, '3', 39, 'Semi-Annual', '2026-01-30', '2026-07-30', 1, NOW(), NOW()),
(50, '3', 40, 'Semi-Annual', '2026-01-20', '2026-07-20', 1, NOW(), NOW()),
(51, '3', 41, 'Semi-Annual', '2026-01-20', '2026-07-20', 1, NOW(), NOW()),
(52, '3', 42, 'Semi-Annual', '2026-02-14', '2026-08-14', 1, NOW(), NOW()),

-- Printer schedules
(60, '4', 20, 'Semi-Annual', '2026-02-10', '2026-08-10', 1, NOW(), NOW()),
(61, '4', 21, 'Semi-Annual', '2026-02-12', '2026-08-12', 1, NOW(), NOW()),
(62, '4', 22, 'Semi-Annual', '2026-02-01', '2026-08-01', 1, NOW(), NOW()),
(63, '4', 23, 'Semi-Annual', '2026-01-28', '2026-07-28', 1, NOW(), NOW()),
(64, '4', 24, 'Semi-Annual', '2026-02-15', '2026-08-15', 1, NOW(), NOW()),
(65, '4', 25, 'Semi-Annual', '2026-01-30', '2026-07-30', 1, NOW(), NOW()),
(66, '4', 26, 'Semi-Annual', '2026-01-30', '2026-07-30', 1, NOW(), NOW()),

-- All-in-One schedules (existing equipment)
(70, '2', 2, 'Semi-Annual', '2026-02-14', '2026-08-14', 1, NOW(), NOW());

-- =========================================================
-- 6. ADD MAINTENANCE RECORDS (the history data!)
-- =========================================================
-- accountId = 3 (SystemSuperAdmin)
-- preparedBy alternates between Lexter Manuel and Demi Xochitl

INSERT INTO `tbl_maintenance_record` (`recordId`, `scheduleId`, `equipmentTypeId`, `equipmentId`, `accountId`, `maintenanceDate`, `checklistJson`, `remarks`, `overallStatus`, `conditionRating`, `preparedBy`, `checkedBy`, `notedBy`) VALUES

-- ── Feb 15, 2026 ── (ICT Unit batch: system unit + monitor + printer)
(10, 27, 1, 17, 3, '2026-02-15 10:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"OK"}]},{"name":"Hardware Check","items":[{"task":"Power Supply working","status":"OK"}]}]}',
 'All components in excellent condition. Thermal paste refreshed.', 'Operational', 'Excellent', 'Lexter Manuel', 'ICT Head', 'Department Manager'),

(11, 47, 3, 37, 3, '2026-02-15 10:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"},{"task":"No dead pixels","status":"OK"}]}]}',
 'Monitor functioning perfectly. No issues found.', 'Operational', 'Excellent', 'Lexter Manuel', 'ICT Head', 'Department Manager'),

(12, 64, 4, 24, 3, '2026-02-15 11:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Exterior clean","status":"OK"},{"task":"Print head aligned","status":"OK"}]}]}',
 'Printer in good condition. Ink levels adequate.', 'Operational', 'Good', 'Lexter Manuel', 'ICT Head', 'Department Manager'),

-- ── Feb 14, 2026 ── (Legal Services + FISA Unit batch)
(13, 24, 1, 14, 3, '2026-02-14 09:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"OK"}]}]}',
 'System running smoothly. SSD health at 95%.', 'Operational', 'Excellent', 'Demi Xochitl', 'Legal Head', 'Department Manager'),

(14, 44, 3, 34, 3, '2026-02-14 09:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"}]}]}',
 'Monitor in good condition.', 'Operational', 'Good', 'Demi Xochitl', 'Legal Head', 'Department Manager'),

(15, 32, 1, 22, 3, '2026-02-14 14:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"}]},{"name":"Hardware Check","items":[{"task":"RAM test passed","status":"OK"}]}]}',
 'FISA system unit cleaned and tested. All clear.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),

(16, 52, 3, 42, 3, '2026-02-14 14:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"}]}]}',
 'Monitor working well. Slight color shift noted but acceptable.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),

-- ── Feb 12, 2026 ── (Accounting Unit batch)
(17, 21, 1, 11, 3, '2026-02-12 09:15:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"OK"}]},{"name":"Hardware Check","items":[{"task":"Power Supply working","status":"OK"},{"task":"Fan noise check","status":"OK"}]}]}',
 'System unit thoroughly cleaned. Fan replaced due to noise.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),

(18, 41, 3, 31, 3, '2026-02-12 09:45:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"},{"task":"No dead pixels","status":"OK"}]}]}',
 'Monitor in excellent condition.', 'Operational', 'Excellent', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),

(19, 61, 4, 21, 3, '2026-02-12 10:15:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Exterior clean","status":"OK"}]},{"name":"Print Quality","items":[{"task":"Test page printed","status":"OK"}]}]}',
 'LaserJet printer functioning well. Toner at 60%.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),

-- ── Feb 10, 2026 ── (Engineering Section batch)
(20, 20, 1, 10, 3, '2026-02-10 10:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"Minor Issue"}]},{"name":"Hardware Check","items":[{"task":"Power Supply working","status":"OK"}]}]}',
 'Minor dust buildup inside. RAM slot 2 slightly loose - reseated.', 'Operational', 'Fair', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager'),

(21, 40, 3, 30, 3, '2026-02-10 10:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"}]}]}',
 'Monitor OK. Slight backlight bleed on lower-left corner.', 'Operational', 'Good', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager'),

(22, 60, 4, 20, 3, '2026-02-10 11:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Exterior clean","status":"OK"}]},{"name":"Print Quality","items":[{"task":"Nozzle check","status":"Minor Issue"}]}]}',
 'Print head cleaned. Nozzle check showed minor clog - resolved after cleaning cycle.', 'Operational', 'Fair', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager'),

-- ── Feb 8, 2026 ── (Operation Section batch)
(23, 22, 1, 12, 3, '2026-02-08 08:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"OK"}]},{"name":"Hardware Check","items":[{"task":"Power Supply working","status":"OK"}]}]}',
 'System unit in good condition. Disk health 92%.', 'Operational', 'Good', 'Demi Xochitl', 'Operation Section Head', 'EOD Manager'),

(24, 42, 3, 32, 3, '2026-02-08 09:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"},{"task":"No dead pixels","status":"OK"}]}]}',
 'Monitor excellent. Calibrated for optimal display.', 'Operational', 'Excellent', 'Demi Xochitl', 'Operation Section Head', 'EOD Manager'),

-- ── Feb 6, 2026 ── (Public Relations batch)
(25, 28, 1, 18, 3, '2026-02-06 13:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"}]},{"name":"Hardware Check","items":[{"task":"Power Supply working","status":"OK"}]}]}',
 'PR system unit cleaned successfully.', 'Operational', 'Good', 'Lexter Manuel', 'ODM Head', 'Department Manager'),

(26, 48, 3, 38, 3, '2026-02-06 13:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"}]}]}',
 'Monitor in good working condition.', 'Operational', 'Good', 'Lexter Manuel', 'ODM Head', 'Department Manager'),

(27, 65, 4, 25, 3, '2026-02-06 14:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Exterior clean","status":"OK"}]},{"name":"Print Quality","items":[{"task":"Test page","status":"OK"}]}]}',
 'Brother printer working well. Paper feed smooth.', 'Operational', 'Excellent', 'Lexter Manuel', 'ODM Head', 'Department Manager'),

-- ── Feb 3, 2026 ── (Cashiering Unit batch)
(28, 26, 1, 16, 3, '2026-02-03 09:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"OK"}]}]}',
 'Cashiering PC cleaned. Runs well for daily operations.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),

(29, 46, 3, 36, 3, '2026-02-03 09:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"}]}]}',
 'Monitor functioning normally.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),

-- ── Feb 1, 2026 ── (Property Unit batch)
(30, 23, 1, 13, 3, '2026-02-01 10:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"Minor Issue"}]},{"name":"Hardware Check","items":[{"task":"HDD health check","status":"Warning"}]}]}',
 'HDD showing early signs of degradation (87% health). Recommended SSD upgrade within 6 months.', 'Operational', 'Fair', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),

(31, 43, 3, 33, 3, '2026-02-01 10:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"}]}]}',
 'Monitor working fine.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),

(32, 62, 4, 22, 3, '2026-02-01 11:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Exterior clean","status":"OK"}]},{"name":"Print Quality","items":[{"task":"Ink levels checked","status":"OK"}]}]}',
 'Canon printer cleaned. Ink levels at 70%.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),

-- ── Jan 30, 2026 ── (BAC Unit + PR batch)
(33, 29, 1, 19, 3, '2026-01-30 09:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"OK"}]}]}',
 'BAC system unit in excellent shape.', 'Operational', 'Excellent', 'Demi Xochitl', 'ODM Head', 'Department Manager'),

(34, 49, 3, 39, 3, '2026-01-30 09:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"},{"task":"No dead pixels","status":"OK"}]}]}',
 'HP monitor excellent. No issues.', 'Operational', 'Excellent', 'Demi Xochitl', 'ODM Head', 'Department Manager'),

(35, 66, 4, 26, 3, '2026-01-30 14:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Exterior clean","status":"OK"}]},{"name":"Print Quality","items":[{"task":"Test page","status":"Minor Issue"}]}]}',
 'Epson printer showing slight banding. Cleaned print heads.', 'Operational', 'Fair', 'Lexter Manuel', 'ODM Head', 'Department Manager'),

-- ── Jan 28, 2026 ── (Personnel & Records + Accounting batch)
(36, 25, 1, 15, 3, '2026-01-28 08:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"OK"}]},{"name":"Hardware Check","items":[{"task":"Power Supply working","status":"OK"},{"task":"SSD health","status":"OK"}]}]}',
 'Records PC in excellent condition. SSD health at 98%.', 'Operational', 'Excellent', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),

(37, 45, 3, 35, 3, '2026-01-28 09:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"}]}]}',
 'Older monitor but still performing well. Minor backlight aging.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),

(38, 63, 4, 23, 3, '2026-01-28 09:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Exterior clean","status":"OK"}]},{"name":"Print Quality","items":[{"task":"Ink levels","status":"OK"},{"task":"Feed mechanism","status":"OK"}]}]}',
 'Epson L5290 in good condition. Ink refilled.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),

-- ── Jan 20, 2026 ── (Equipment Mgmt + IDS batch)
(39, 30, 1, 20, 3, '2026-01-20 10:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"},{"task":"Parts are intact","status":"OK"}]}]}',
 'Equipment Management PC cleaned and tested.', 'Operational', 'Good', 'Demi Xochitl', 'Engineering Section Head', 'EOD Manager'),

(40, 50, 3, 40, 3, '2026-01-20 10:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"}]}]}',
 'Monitor in good shape.', 'Operational', 'Good', 'Demi Xochitl', 'Engineering Section Head', 'EOD Manager'),

(41, 31, 1, 21, 3, '2026-01-20 13:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal performed","status":"OK"}]},{"name":"Hardware Check","items":[{"task":"Power Supply working","status":"OK"}]}]}',
 'IDS PC maintained successfully.', 'Operational', 'Excellent', 'Demi Xochitl', 'Engineering Section Head', 'EOD Manager'),

(42, 51, 3, 41, 3, '2026-01-20 13:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Screen clean","status":"OK"}]}]}',
 'Monitor working well.', 'Operational', 'Good', 'Demi Xochitl', 'Engineering Section Head', 'EOD Manager'),

-- ── Jan 15, 2026 ── (All-in-One for Lexter + Pantabangan batch)
(43, 70, 2, 2, 3, '2026-01-15 10:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Exterior clean","status":"OK"},{"task":"All-in-one internals","status":"OK"}]}]}',
 'All-in-One PC cleaned. Good performance.', 'Operational', 'Good', 'Demi Xochitl', 'ICT Head', 'Department Manager'),

-- ── Dec 20, 2025 ── (Early records to show date range)
(44, 20, 1, 10, 3, '2025-12-20 09:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal","status":"OK"}]}]}',
 'Quarterly check. System unit OK.', 'Operational', 'Good', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager'),

(45, 22, 1, 12, 3, '2025-12-20 10:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal","status":"OK"}]}]}',
 'Operation Section PC quarterly maintenance done.', 'Operational', 'Excellent', 'Lexter Manuel', 'Operation Section Head', 'EOD Manager'),

(46, 23, 1, 13, 3, '2025-12-15 14:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal","status":"OK"},{"task":"Parts intact","status":"Minor Issue"}]}]}',
 'Property Unit PC - noted HDD starting to slow down.', 'Operational', 'Fair', 'Demi Xochitl', 'Admin Section Head', 'Department Manager'),

(47, 21, 1, 11, 3, '2025-12-10 09:30:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal","status":"OK"}]}]}',
 'Accounting PC quarterly maintenance complete.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),

-- ── Additional records for Equipment Mgmt section - to show "For Replacement"
(48, 30, 1, 20, 3, '2025-11-15 10:00:00',
 '{"categories":[{"name":"Physical Inspection","items":[{"task":"Dust removal","status":"OK"},{"task":"Parts intact","status":"FAIL"}]},{"name":"Hardware Check","items":[{"task":"Power Supply","status":"Warning"}]}]}',
 'PSU showing intermittent issues. Recommended for replacement if budget allows.', 'Operational', 'Poor', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager');

COMMIT;
