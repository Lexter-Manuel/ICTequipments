-- Migration: Rename is_active to is_archive in tbl_employee
-- The "active" status is now derived from equipment count.
-- is_archive is a soft-delete / archive flag (0 = visible, 1 = archived).

ALTER TABLE `tbl_employee`
  CHANGE COLUMN `is_active` `is_archive` TINYINT(1) NOT NULL DEFAULT 0;

-- Flip existing values: old is_active=1 (active) → is_archive=0, old is_active=0 → is_archive=1
UPDATE `tbl_employee` SET `is_archive` = CASE WHEN `is_archive` = 1 THEN 0 ELSE 1 END;
