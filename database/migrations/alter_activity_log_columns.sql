-- Migration: Standardize activity_log columns
-- Date: 2026-02-24
-- Purpose: Enforce proper usage of action/module/description columns

ALTER TABLE `activity_log`
    MODIFY COLUMN `action` VARCHAR(50) NOT NULL COMMENT 'e.g., CREATE, UPDATE, DELETE, LOGIN, LOGOUT, ERROR',
    MODIFY COLUMN `module` VARCHAR(50) DEFAULT NULL COMMENT 'e.g., Divisions, Sections, Units, Computers, Settings, Profile, Auth',
    MODIFY COLUMN `description` TEXT DEFAULT NULL COMMENT 'Human-readable summary of the change';
