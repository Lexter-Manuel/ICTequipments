-- =========================================================
-- Sample Maintenance Templates: Monitor, Printer, All-in-One
-- =========================================================

START TRANSACTION;

-- Template 1: Monitor Preventive Maintenance (targetTypeId = 3)
INSERT INTO `tbl_maintenance_template` (`templateId`, `templateName`, `targetTypeId`, `frequency`, `structure_json`, `signatories_json`, `isActive`, `createdAt`) VALUES
(10, 'MONITOR PREVENTIVE MAINTENANCE', '3', 'Semi-Annual', NULL, '{"verifiedByName":"[Select Supervisor Name]","verifiedByTitle":"DIVISION / SECTION HEAD","notedByName":"[Select Head of Office]","notedByTitle":"HEAD OF OFFICE"}', 1, NOW());

INSERT INTO `tbl_checklist_category` (`categoryId`, `templateId`, `categoryName`, `sequenceOrder`) VALUES
(25, 10, 'I. PHYSICAL INSPECTION & CLEANING', 1),
(26, 10, 'II. DISPLAY PERFORMANCE CHECK', 2),
(27, 10, 'III. CONNECTIVITY & CABLES', 3);

INSERT INTO `tbl_checklist_item` (`itemId`, `categoryId`, `taskDescription`, `sequenceOrder`) VALUES
(25, 25, 'Screen surface cleaned (no smudges/dust)', 1),
(26, 25, 'Monitor casing wiped and free of dust', 2),
(27, 25, 'Ventilation openings clear of obstruction', 3),
(28, 25, 'Stand/mount secure and stable', 4),
(29, 26, 'No dead or stuck pixels detected', 1),
(30, 26, 'Brightness and contrast levels acceptable', 2),
(31, 26, 'Color reproduction is accurate', 3),
(32, 26, 'No visible backlight bleed or flickering', 4),
(33, 27, 'Power cable securely connected', 1),
(34, 27, 'Video cable (HDMI/VGA/DP) securely connected', 2),
(35, 27, 'No visible cable damage or fraying', 3);

-- Template 2: Printer Preventive Maintenance (targetTypeId = 4)
INSERT INTO `tbl_maintenance_template` (`templateId`, `templateName`, `targetTypeId`, `frequency`, `structure_json`, `signatories_json`, `isActive`, `createdAt`) VALUES
(11, 'PRINTER PREVENTIVE MAINTENANCE', '4', 'Quarterly', NULL, '{"verifiedByName":"[Select Supervisor Name]","verifiedByTitle":"DIVISION / SECTION HEAD","notedByName":"[Select Head of Office]","notedByTitle":"HEAD OF OFFICE"}', 1, NOW());

INSERT INTO `tbl_checklist_category` (`categoryId`, `templateId`, `categoryName`, `sequenceOrder`) VALUES
(28, 11, 'I. PHYSICAL INSPECTION & CLEANING', 1),
(29, 11, 'II. PRINT QUALITY CHECK', 2),
(30, 11, 'III. MECHANICAL & CONSUMABLES', 3);

INSERT INTO `tbl_checklist_item` (`itemId`, `categoryId`, `taskDescription`, `sequenceOrder`) VALUES
(36, 28, 'Exterior casing cleaned and free of dust', 1),
(37, 28, 'Paper tray clean and properly aligned', 2),
(38, 28, 'Ventilation openings clear of obstruction', 3),
(39, 28, 'No paper debris inside printer', 4),
(40, 29, 'Test page printed successfully', 1),
(41, 29, 'Print alignment is correct', 2),
(42, 29, 'No streaks, smudges, or banding on output', 3),
(43, 29, 'Color output accurate (if color printer)', 4),
(44, 30, 'Ink/toner levels checked and adequate', 1),
(45, 30, 'Paper feed mechanism operates smoothly', 2),
(46, 30, 'Print head cleaned (inkjet) or drum inspected (laser)', 3),
(47, 30, 'Rollers clean and in good condition', 4);

-- Template 3: All-in-One PC Preventive Maintenance (targetTypeId = 2)
INSERT INTO `tbl_maintenance_template` (`templateId`, `templateName`, `targetTypeId`, `frequency`, `structure_json`, `signatories_json`, `isActive`, `createdAt`) VALUES
(12, 'ALL-IN-ONE PC PREVENTIVE MAINTENANCE', '2', 'Semi-Annual', NULL, '{"verifiedByName":"[Select Supervisor Name]","verifiedByTitle":"DIVISION / SECTION HEAD","notedByName":"[Select Head of Office]","notedByTitle":"HEAD OF OFFICE"}', 1, NOW());

INSERT INTO `tbl_checklist_category` (`categoryId`, `templateId`, `categoryName`, `sequenceOrder`) VALUES
(31, 12, 'I. PHYSICAL INSPECTION & CLEANING', 1),
(32, 12, 'II. HARDWARE PERFORMANCE CHECK', 2),
(33, 12, 'III. DISPLAY & PERIPHERALS', 3),
(34, 12, 'IV. SOFTWARE & SYSTEM CHECK', 4);

INSERT INTO `tbl_checklist_item` (`itemId`, `categoryId`, `taskDescription`, `sequenceOrder`) VALUES
(48, 31, 'Exterior casing and screen cleaned', 1),
(49, 31, 'Ventilation openings clear and dust-free', 2),
(50, 31, 'All ports free of debris', 3),
(51, 31, 'Stand/mount secure and stable', 4),
(52, 32, 'Power supply unit is working properly', 1),
(53, 32, 'CPU temperature within normal range', 2),
(54, 32, 'RAM test passed (no errors)', 3),
(55, 32, 'Storage health check (SSD/HDD status)', 4),
(56, 32, 'Fan(s) operating normally', 5),
(57, 33, 'Display has no dead pixels or flickering', 1),
(58, 33, 'Built-in webcam functioning (if applicable)', 2),
(59, 33, 'Built-in speakers/microphone working', 3),
(60, 33, 'USB ports and card reader functional', 4),
(61, 34, 'Operating system boots without errors', 1),
(62, 34, 'Antivirus definitions up to date', 2),
(63, 34, 'Windows updates installed', 3),
(64, 34, 'Essential software applications functional', 4);

COMMIT;
