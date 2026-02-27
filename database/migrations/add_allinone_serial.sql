-- Add serial number column to tbl_allinone
ALTER TABLE `tbl_allinone`
ADD COLUMN `allinoneSerial` VARCHAR(100) NULL DEFAULT NULL AFTER `allinoneBrand`;

-- Add unique index for serial number (allows NULLs)
ALTER TABLE `tbl_allinone`
ADD UNIQUE INDEX `idx_allinone_serial` (`allinoneSerial`);
