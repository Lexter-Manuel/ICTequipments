-- ============================================================
-- Seed: Sample data for tbl_maintenance_response
-- Date: 2026-02-20
--
-- Populates tbl_maintenance_response with realistic checklist
-- responses for several existing maintenance records, and sets
-- their templateId so the report can link back to the template.
--
-- Prerequisites:
--   • add_checklist_responses.sql migration has been applied
--   • Existing maintenance records 10-19 exist
--   • Checklist categories/items for templates 8, 10, 11 exist
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Set templateId on existing records
-- ────────────────────────────────────────────────────────────
UPDATE `tbl_maintenance_record` SET `templateId` = 8  WHERE `recordId` = 10;  -- System Unit, template 8
UPDATE `tbl_maintenance_record` SET `templateId` = 10 WHERE `recordId` = 11;  -- Monitor, template 10
UPDATE `tbl_maintenance_record` SET `templateId` = 11 WHERE `recordId` = 12;  -- Printer, template 11
UPDATE `tbl_maintenance_record` SET `templateId` = 8  WHERE `recordId` = 13;  -- System Unit, template 8
UPDATE `tbl_maintenance_record` SET `templateId` = 10 WHERE `recordId` = 14;  -- Monitor, template 10
UPDATE `tbl_maintenance_record` SET `templateId` = 8  WHERE `recordId` = 15;  -- System Unit, template 8
UPDATE `tbl_maintenance_record` SET `templateId` = 10 WHERE `recordId` = 16;  -- Monitor, template 10
UPDATE `tbl_maintenance_record` SET `templateId` = 8  WHERE `recordId` = 17;  -- System Unit, template 8
UPDATE `tbl_maintenance_record` SET `templateId` = 10 WHERE `recordId` = 18;  -- Monitor, template 10
UPDATE `tbl_maintenance_record` SET `templateId` = 11 WHERE `recordId` = 19;  -- Printer, template 11

-- ────────────────────────────────────────────────────────────
-- 2. Insert response rows per record
-- ────────────────────────────────────────────────────────────

-- ── Record 10 — System Unit (template 8, categories 19/20) ──
--    All items passed (Excellent condition)
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (10, 19, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed',              'Yes', 1),
    (10, 20, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact',                    'Yes', 2),
    (10, 21, 20, 'II. HARDWARE PERFORMANCE CHECK',                 'Power Supply is working properly',     'Yes', 3);

-- ── Record 11 — Monitor (template 10, categories 25/26/27) ──
--    All items passed (Excellent condition)
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (11, 25, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Screen surface cleaned (no smudges/dust)',           'Yes', 1),
    (11, 26, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Monitor casing wiped and free of dust',              'Yes', 2),
    (11, 27, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Ventilation openings clear of obstruction',          'Yes', 3),
    (11, 28, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Stand/mount secure and stable',                      'Yes', 4),
    (11, 29, 26, 'II. DISPLAY PERFORMANCE CHECK',      'No dead or stuck pixels detected',                   'Yes', 5),
    (11, 30, 26, 'II. DISPLAY PERFORMANCE CHECK',      'Brightness and contrast levels acceptable',           'Yes', 6),
    (11, 31, 26, 'II. DISPLAY PERFORMANCE CHECK',      'Color reproduction is accurate',                     'Yes', 7),
    (11, 32, 26, 'II. DISPLAY PERFORMANCE CHECK',      'No visible backlight bleed or flickering',            'Yes', 8),
    (11, 33, 27, 'III. CONNECTIVITY & CABLES',         'Power cable securely connected',                      'Yes', 9),
    (11, 34, 27, 'III. CONNECTIVITY & CABLES',         'Video cable (HDMI/VGA/DP) securely connected',        'Yes', 10),
    (11, 35, 27, 'III. CONNECTIVITY & CABLES',         'No visible cable damage or fraying',                  'Yes', 11);

-- ── Record 12 — Printer (template 11, categories 28/29/30) ──
--    Mostly passed; ink levels flagged N/A
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (12, 36, 28, 'I. PHYSICAL INSPECTION & CLEANING',  'Exterior casing cleaned and free of dust',           'Yes', 1),
    (12, 37, 28, 'I. PHYSICAL INSPECTION & CLEANING',  'Paper tray clean and properly aligned',              'Yes', 2),
    (12, 38, 28, 'I. PHYSICAL INSPECTION & CLEANING',  'Ventilation openings clear of obstruction',          'Yes', 3),
    (12, 39, 28, 'I. PHYSICAL INSPECTION & CLEANING',  'No paper debris inside printer',                     'Yes', 4),
    (12, 40, 29, 'II. PRINT QUALITY CHECK',            'Test page printed successfully',                      'Yes', 5),
    (12, 41, 29, 'II. PRINT QUALITY CHECK',            'Print alignment is correct',                          'Yes', 6),
    (12, 42, 29, 'II. PRINT QUALITY CHECK',            'No streaks, smudges, or banding on output',           'Yes', 7),
    (12, 43, 29, 'II. PRINT QUALITY CHECK',            'Color output accurate (if color printer)',             'N/A', 8),
    (12, 44, 30, 'III. MECHANICAL & CONSUMABLES',      'Ink/toner levels checked and adequate',               'Yes', 9),
    (12, 45, 30, 'III. MECHANICAL & CONSUMABLES',      'Paper feed mechanism operates smoothly',              'Yes', 10),
    (12, 46, 30, 'III. MECHANICAL & CONSUMABLES',      'Print head cleaned (inkjet) or drum inspected (laser)', 'Yes', 11),
    (12, 47, 30, 'III. MECHANICAL & CONSUMABLES',      'Rollers clean and in good condition',                 'Yes', 12);

-- ── Record 13 — System Unit (template 8, categories 19/20) ──
--    All passed
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (13, 19, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed',              'Yes', 1),
    (13, 20, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact',                    'Yes', 2),
    (13, 21, 20, 'II. HARDWARE PERFORMANCE CHECK',                 'Power Supply is working properly',     'Yes', 3);

-- ── Record 14 — Monitor (template 10, categories 25/26/27) ──
--    Good condition; one item N/A
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (14, 25, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Screen surface cleaned (no smudges/dust)',           'Yes', 1),
    (14, 26, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Monitor casing wiped and free of dust',              'Yes', 2),
    (14, 27, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Ventilation openings clear of obstruction',          'Yes', 3),
    (14, 28, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Stand/mount secure and stable',                      'Yes', 4),
    (14, 29, 26, 'II. DISPLAY PERFORMANCE CHECK',      'No dead or stuck pixels detected',                   'Yes', 5),
    (14, 30, 26, 'II. DISPLAY PERFORMANCE CHECK',      'Brightness and contrast levels acceptable',           'Yes', 6),
    (14, 31, 26, 'II. DISPLAY PERFORMANCE CHECK',      'Color reproduction is accurate',                     'N/A', 7),
    (14, 32, 26, 'II. DISPLAY PERFORMANCE CHECK',      'No visible backlight bleed or flickering',            'Yes', 8),
    (14, 33, 27, 'III. CONNECTIVITY & CABLES',         'Power cable securely connected',                      'Yes', 9),
    (14, 34, 27, 'III. CONNECTIVITY & CABLES',         'Video cable (HDMI/VGA/DP) securely connected',        'Yes', 10),
    (14, 35, 27, 'III. CONNECTIVITY & CABLES',         'No visible cable damage or fraying',                  'Yes', 11);

-- ── Record 15 — System Unit (template 8, categories 19/20) ──
--    Good condition
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (15, 19, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed',              'Yes', 1),
    (15, 20, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact',                    'Yes', 2),
    (15, 21, 20, 'II. HARDWARE PERFORMANCE CHECK',                 'Power Supply is working properly',     'Yes', 3);

-- ── Record 16 — Monitor (template 10, categories 25/26/27) ──
--    Good condition; slight color shift flagged
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (16, 25, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Screen surface cleaned (no smudges/dust)',           'Yes', 1),
    (16, 26, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Monitor casing wiped and free of dust',              'Yes', 2),
    (16, 27, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Ventilation openings clear of obstruction',          'Yes', 3),
    (16, 28, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Stand/mount secure and stable',                      'Yes', 4),
    (16, 29, 26, 'II. DISPLAY PERFORMANCE CHECK',      'No dead or stuck pixels detected',                   'Yes', 5),
    (16, 30, 26, 'II. DISPLAY PERFORMANCE CHECK',      'Brightness and contrast levels acceptable',           'Yes', 6),
    (16, 31, 26, 'II. DISPLAY PERFORMANCE CHECK',      'Color reproduction is accurate',                     'No',  7),
    (16, 32, 26, 'II. DISPLAY PERFORMANCE CHECK',      'No visible backlight bleed or flickering',            'Yes', 8),
    (16, 33, 27, 'III. CONNECTIVITY & CABLES',         'Power cable securely connected',                      'Yes', 9),
    (16, 34, 27, 'III. CONNECTIVITY & CABLES',         'Video cable (HDMI/VGA/DP) securely connected',        'Yes', 10),
    (16, 35, 27, 'III. CONNECTIVITY & CABLES',         'No visible cable damage or fraying',                  'Yes', 11);

-- ── Record 17 — System Unit (template 8, categories 19/20) ──
--    Good — fan replaced
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (17, 19, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed',              'Yes', 1),
    (17, 20, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact',                    'No',  2),
    (17, 21, 20, 'II. HARDWARE PERFORMANCE CHECK',                 'Power Supply is working properly',     'Yes', 3);

-- ── Record 18 — Monitor (template 10, categories 25/26/27) ──
--    Excellent
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (18, 25, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Screen surface cleaned (no smudges/dust)',           'Yes', 1),
    (18, 26, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Monitor casing wiped and free of dust',              'Yes', 2),
    (18, 27, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Ventilation openings clear of obstruction',          'Yes', 3),
    (18, 28, 25, 'I. PHYSICAL INSPECTION & CLEANING',  'Stand/mount secure and stable',                      'Yes', 4),
    (18, 29, 26, 'II. DISPLAY PERFORMANCE CHECK',      'No dead or stuck pixels detected',                   'Yes', 5),
    (18, 30, 26, 'II. DISPLAY PERFORMANCE CHECK',      'Brightness and contrast levels acceptable',           'Yes', 6),
    (18, 31, 26, 'II. DISPLAY PERFORMANCE CHECK',      'Color reproduction is accurate',                     'Yes', 7),
    (18, 32, 26, 'II. DISPLAY PERFORMANCE CHECK',      'No visible backlight bleed or flickering',            'Yes', 8),
    (18, 33, 27, 'III. CONNECTIVITY & CABLES',         'Power cable securely connected',                      'Yes', 9),
    (18, 34, 27, 'III. CONNECTIVITY & CABLES',         'Video cable (HDMI/VGA/DP) securely connected',        'Yes', 10),
    (18, 35, 27, 'III. CONNECTIVITY & CABLES',         'No visible cable damage or fraying',                  'Yes', 11);

-- ── Record 19 — Printer (template 11, categories 28/29/30) ──
--    Good condition; toner at 60%
INSERT INTO `tbl_maintenance_response`
    (`recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`)
VALUES
    (19, 36, 28, 'I. PHYSICAL INSPECTION & CLEANING',  'Exterior casing cleaned and free of dust',           'Yes', 1),
    (19, 37, 28, 'I. PHYSICAL INSPECTION & CLEANING',  'Paper tray clean and properly aligned',              'Yes', 2),
    (19, 38, 28, 'I. PHYSICAL INSPECTION & CLEANING',  'Ventilation openings clear of obstruction',          'Yes', 3),
    (19, 39, 28, 'I. PHYSICAL INSPECTION & CLEANING',  'No paper debris inside printer',                     'Yes', 4),
    (19, 40, 29, 'II. PRINT QUALITY CHECK',            'Test page printed successfully',                      'Yes', 5),
    (19, 41, 29, 'II. PRINT QUALITY CHECK',            'Print alignment is correct',                          'Yes', 6),
    (19, 42, 29, 'II. PRINT QUALITY CHECK',            'No streaks, smudges, or banding on output',           'No',  7),
    (19, 43, 29, 'II. PRINT QUALITY CHECK',            'Color output accurate (if color printer)',             'N/A', 8),
    (19, 44, 30, 'III. MECHANICAL & CONSUMABLES',      'Ink/toner levels checked and adequate',               'Yes', 9),
    (19, 45, 30, 'III. MECHANICAL & CONSUMABLES',      'Paper feed mechanism operates smoothly',              'Yes', 10),
    (19, 46, 30, 'III. MECHANICAL & CONSUMABLES',      'Print head cleaned (inkjet) or drum inspected (laser)', 'Yes', 11),
    (19, 47, 30, 'III. MECHANICAL & CONSUMABLES',      'Rollers clean and in good condition',                 'Yes', 12);
