-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3310
-- Generation Time: Feb 16, 2026 at 04:41 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nia-inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `success` tinyint(1) DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `location_id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `location_type_id` int(11) NOT NULL,
  `parent_location_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` enum('0','1') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`location_id`, `location_name`, `location_type_id`, `parent_location_id`, `created_at`, `is_deleted`) VALUES
(3, 'Office of the Department Manager', 2, 34, '2025-02-16 00:02:03', '0'),
(4, 'ICT Unit', 3, 34, '2025-02-16 00:02:03', '0'),
(5, 'Public Relation Office Unit', 3, 34, '2025-02-16 00:02:03', '0'),
(6, 'Legal Services', 3, 34, '2025-02-16 00:02:03', '0'),
(7, 'Office of the EOD Manager', 2, 23, '2025-02-16 00:02:03', '0'),
(8, 'Office of the ADFIN Manager', 2, 24, '2025-02-16 00:02:03', '0'),
(9, 'Administrative Section', 2, 24, '2025-02-16 00:02:03', '0'),
(10, 'Finance Section', 2, 24, '2025-02-16 00:02:03', '0'),
(11, 'Property Unit', 3, 9, '2025-02-16 00:02:03', '0'),
(12, 'General Services Security Unit ', 3, 9, '2025-02-16 00:02:03', '0'),
(13, 'Pantabangan Lake Resort and Hotel', 3, 9, '2025-02-16 00:02:03', '0'),
(14, 'Medical Services Unit', 3, 9, '2025-02-16 00:02:03', '0'),
(15, 'Cashiering Unit', 3, 10, '2025-02-16 00:02:03', '0'),
(16, 'FISA Unit', 3, 9, '2025-02-16 00:02:03', '0'),
(18, 'Engineering Section', 2, 23, '2025-02-16 00:02:03', '0'),
(19, 'Operation Section', 2, 23, '2025-02-16 00:02:03', '0'),
(20, 'Equipment Management Section', 2, 23, '2025-02-16 00:02:03', '0'),
(21, 'Institutional Development Section', 2, 23, '2025-02-16 00:02:03', '0'),
(22, 'BAC Unit', 3, 34, '2025-02-17 23:24:43', '0'),
(23, 'Engineering and Operation Division(EOD)', 1, NULL, '2025-03-02 22:38:42', '0'),
(24, 'Administrative and Finance Division(ADFIN)', 1, NULL, '2025-03-02 22:44:12', '0'),
(25, 'Personnel and Records Unit', 3, 9, '2025-03-03 00:28:04', '0'),
(29, 'Accounting Unit', 3, 10, '2025-04-01 23:46:43', '0'),
(34, 'Office of the Department Manager(ODM)', 1, NULL, '2026-02-05 06:02:54', '0');

-- --------------------------------------------------------

--
-- Table structure for table `location_type`
--

CREATE TABLE `location_type` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `location_type`
--

INSERT INTO `location_type` (`id`, `name`) VALUES
(1, 'Division'),
(2, 'Section'),
(3, 'Unit');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 1 hour),
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` enum('Super Admin','Admin') NOT NULL,
  `module` varchar(50) NOT NULL,
  `can_view` tinyint(1) DEFAULT 1,
  `can_create` tinyint(1) DEFAULT 0,
  `can_update` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0,
  `can_export` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `module`, `can_view`, `can_create`, `can_update`, `can_delete`, `can_export`) VALUES
(1, 'Super Admin', 'employees', 1, 1, 1, 1, 1),
(2, 'Super Admin', 'equipment', 1, 1, 1, 1, 1),
(3, 'Super Admin', 'divisions', 1, 1, 1, 1, 1),
(4, 'Super Admin', 'sections', 1, 1, 1, 1, 1),
(5, 'Super Admin', 'software', 1, 1, 1, 1, 1),
(6, 'Super Admin', 'reports', 1, 1, 1, 1, 1),
(7, 'Super Admin', 'accounts', 1, 1, 1, 1, 1),
(8, 'Super Admin', 'settings', 1, 1, 1, 1, 1),
(9, 'Admin', 'employees', 1, 1, 1, 0, 1),
(10, 'Admin', 'equipment', 1, 1, 1, 0, 1),
(11, 'Admin', 'divisions', 1, 0, 0, 0, 1),
(12, 'Admin', 'sections', 1, 0, 0, 0, 1),
(13, 'Admin', 'software', 1, 1, 1, 0, 1),
(14, 'Admin', 'reports', 1, 0, 0, 0, 1),
(15, 'Admin', 'accounts', 0, 0, 0, 0, 0),
(16, 'Admin', 'settings', 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 1 day),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_accounts`
--

CREATE TABLE `tbl_accounts` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt hashed password',
  `role` enum('Super Admin','Admin') NOT NULL DEFAULT 'Admin',
  `status` enum('Active','Inactive','Locked') NOT NULL DEFAULT 'Active',
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `2fa_enabled` tinyint(1) DEFAULT 0,
  `2fa_secret` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_accounts`
--

INSERT INTO `tbl_accounts` (`id`, `user_name`, `email`, `password`, `role`, `status`, `failed_login_attempts`, `locked_until`, `last_login`, `last_login_ip`, `2fa_enabled`, `2fa_secret`, `created_at`, `updated_at`, `created_by`) VALUES
(3, 'SystemSuperAdmin', 'inventory@upriis.local', '$2y$12$RAUQs6D0FBVNz.ky7N6rJegTTOCmDxYpO850YwZMlxyWX4bLDKl9G', 'Super Admin', 'Active', 0, NULL, NULL, NULL, 0, NULL, '2026-02-09 23:26:19', '2026-02-16 02:29:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_allinone`
--

CREATE TABLE `tbl_allinone` (
  `allinoneId` int(11) NOT NULL,
  `allinoneBrand` varchar(100) NOT NULL,
  `specificationProcessor` varchar(255) NOT NULL,
  `specificationMemory` varchar(255) NOT NULL,
  `specificationGPU` varchar(255) NOT NULL,
  `specificationStorage` varchar(255) NOT NULL,
  `employeeId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_allinone`
--

INSERT INTO `tbl_allinone` (`allinoneId`, `allinoneBrand`, `specificationProcessor`, `specificationMemory`, `specificationGPU`, `specificationStorage`, `employeeId`) VALUES
(2, 'asd', 'asd', 'asd', 'asd', 'asad', 1),
(3, 'HP all-in-one', 'ryzen 7', '8GB', 'Intel Integrated Graphics', '250GB SSD', 645987);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_checklist_category`
--

CREATE TABLE `tbl_checklist_category` (
  `categoryId` int(11) NOT NULL,
  `templateId` int(11) NOT NULL,
  `categoryName` varchar(100) NOT NULL,
  `sequenceOrder` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_checklist_item`
--

CREATE TABLE `tbl_checklist_item` (
  `itemId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  `taskDescription` text NOT NULL,
  `sequenceOrder` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_employee`
--

CREATE TABLE `tbl_employee` (
  `employeeId` int(11) NOT NULL,
  `firstName` varchar(100) NOT NULL,
  `middleName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) NOT NULL,
  `suffixName` varchar(50) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `birthDate` date NOT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `employmentStatus` enum('Permanent','Casual','Job Order') NOT NULL,
  `photoPath` varchar(255) DEFAULT NULL,
  `location_id` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_employee`
--

INSERT INTO `tbl_employee` (`employeeId`, `firstName`, `middleName`, `lastName`, `suffixName`, `position`, `birthDate`, `sex`, `employmentStatus`, `photoPath`, `location_id`, `createdAt`, `updatedAt`, `is_active`) VALUES
(1, 'Lexter', 'N.', 'Manuel', '', 'OJT Trainee', '2002-11-06', 'Male', 'Casual', 'employee_1_1770881693.jpeg', 4, '2026-02-10 03:41:25', '2026-02-12 07:34:53', 1),
(645987, 'Demi', NULL, 'Xochitl', '', 'OJT Trainee', '2006-02-10', 'Male', 'Casual', 'employee_645987_1770970632.jpeg', 13, '2026-02-11 03:04:45', '2026-02-13 08:17:12', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_equipment_type_registry`
--

CREATE TABLE `tbl_equipment_type_registry` (
  `typeId` int(11) NOT NULL,
  `typeName` varchar(50) NOT NULL,
  `tableName` varchar(50) NOT NULL,
  `pkColumn` varchar(50) NOT NULL,
  `filterClause` varchar(255) DEFAULT NULL,
  `defaultFrequency` int(11) DEFAULT 180,
  `context` enum('Employee','Location') NOT NULL DEFAULT 'Location',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_equipment_type_registry`
--

INSERT INTO `tbl_equipment_type_registry` (`typeId`, `typeName`, `tableName`, `pkColumn`, `filterClause`, `defaultFrequency`, `context`, `created_at`) VALUES
(1, 'System Unit', 'tbl_systemunit', 'systemunitId', NULL, 180, 'Employee', '2026-02-16 03:18:42'),
(2, 'All-in-One', 'tbl_allinone', 'allinoneId', NULL, 180, 'Employee', '2026-02-16 03:18:42'),
(3, 'Monitor', 'tbl_monitor', 'monitorId', NULL, 180, 'Employee', '2026-02-16 03:18:42'),
(4, 'Printer', 'tbl_printer', 'printerId', NULL, 180, 'Employee', '2026-02-16 03:18:42'),
(5, 'Laptop', 'tbl_otherequipment', 'otherEquipmentId', 'equipmentType = \'Laptop\'', 180, 'Employee', '2026-02-16 03:18:42'),
(6, 'Mouse', 'tbl_otherequipment', 'otherEquipmentId', 'equipmentType = \'Mouse\'', 180, 'Employee', '2026-02-16 03:18:42'),
(7, 'Keyboard', 'tbl_otherequipment', 'otherEquipmentId', 'equipmentType = \'Keyboard\'', 180, 'Employee', '2026-02-16 03:18:42'),
(8, 'CCTV System', 'tbl_otherequipment', 'otherEquipmentId', 'equipmentType = \'CCTV\'', 180, 'Location', '2026-02-16 03:18:42'),
(9, 'Network Storage (NAS)', 'tbl_otherequipment', 'otherEquipmentId', 'equipmentType = \'NAS\'', 180, 'Location', '2026-02-16 03:18:42'),
(10, 'Network Switch', 'tbl_otherequipment', 'otherEquipmentId', 'equipmentType = \'Switch\'', 180, 'Location', '2026-02-16 03:18:42'),
(11, 'Projector', 'tbl_otherequipment', 'otherEquipmentId', 'equipmentType = \'Projector\'', 180, 'Location', '2026-02-16 03:18:42'),
(12, 'Other Infrastructure', 'tbl_otherequipment', 'otherEquipmentId', 'equipmentType NOT IN (\'CCTV\', \'NAS\', \'Switch\', \'Projector\', \'Laptop\', \'Mouse\', \'Keyboard\')', 180, 'Location', '2026-02-16 03:18:42');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_maintenance_frequency`
--

CREATE TABLE `tbl_maintenance_frequency` (
  `frequencyId` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `intervalDays` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_maintenance_frequency`
--

INSERT INTO `tbl_maintenance_frequency` (`frequencyId`, `name`, `intervalDays`) VALUES
(1, 'Monthly', 30),
(2, 'Quarterly', 90),
(3, 'Semi-Annual', 180),
(4, 'Annual', 365);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_maintenance_record`
--

CREATE TABLE `tbl_maintenance_record` (
  `recordId` int(11) NOT NULL,
  `scheduleId` int(11) NOT NULL,
  `equipmentTypeId` int(11) NOT NULL,
  `equipmentId` int(11) NOT NULL,
  `accountId` int(11) NOT NULL,
  `maintenanceDate` datetime NOT NULL DEFAULT current_timestamp(),
  `checklistJson` longtext DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `overallStatus` enum('Operational','For Replacement','Disposed') NOT NULL DEFAULT 'Operational',
  `conditionRating` enum('Excellent','Good','Fair','Poor') NOT NULL DEFAULT 'Good',
  `preparedBy` varchar(100) DEFAULT NULL,
  `checkedBy` varchar(100) DEFAULT NULL,
  `notedBy` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_maintenance_schedule`
--

CREATE TABLE `tbl_maintenance_schedule` (
  `scheduleId` int(11) NOT NULL,
  `equipmentType` varchar(50) NOT NULL COMMENT 'System Unit, Monitor, Printer, Laptop, etc.',
  `equipmentId` int(11) NOT NULL COMMENT 'ID from the specific equipment table',
  `maintenanceFrequency` enum('Monthly','Quarterly','Semi-Annual','Annual') NOT NULL DEFAULT 'Semi-Annual',
  `lastMaintenanceDate` date DEFAULT NULL COMMENT 'When was it last maintained?',
  `nextDueDate` date NOT NULL COMMENT 'When is next maintenance due?',
  `isActive` tinyint(1) DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Maintenance schedule for equipment';

-- --------------------------------------------------------

--
-- Table structure for table `tbl_maintenance_template`
--

CREATE TABLE `tbl_maintenance_template` (
  `templateId` int(11) NOT NULL,
  `templateName` varchar(100) NOT NULL,
  `targetTypeId` int(11) NOT NULL,
  `frequencyId` int(11) NOT NULL DEFAULT 3,
  `isActive` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_monitor`
--

CREATE TABLE `tbl_monitor` (
  `monitorId` int(11) NOT NULL,
  `monitorBrand` varchar(100) NOT NULL,
  `monitorSize` varchar(50) DEFAULT NULL,
  `monitorSerial` varchar(255) NOT NULL,
  `yearAcquired` year(4) DEFAULT NULL,
  `employeeId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_monitor`
--

INSERT INTO `tbl_monitor` (`monitorId`, `monitorBrand`, `monitorSize`, `monitorSerial`, `yearAcquired`, `employeeId`) VALUES
(11, 'asd', 'asd', 'asd', '2000', 1),
(12, 'Samsung', '24 inches', '241908712039', '2025', 1),
(13, 'HP', '43 Inches', '53109847', '2025', NULL),
(14, 'Samsung', '32 inches', 'SN_11111', '2000', 645987);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_otherequipment`
--

CREATE TABLE `tbl_otherequipment` (
  `otherEquipmentId` int(11) NOT NULL,
  `equipmentType` varchar(100) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `serialNumber` varchar(150) DEFAULT NULL,
  `status` enum('Available','In Use','Under Maintenance','Disposed') DEFAULT 'Available',
  `yearAcquired` year(4) DEFAULT NULL,
  `location_id` int(11) NOT NULL,
  `employeeId` int(11) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_otherequipment`
--

INSERT INTO `tbl_otherequipment` (`otherEquipmentId`, `equipmentType`, `brand`, `model`, `details`, `serialNumber`, `status`, `yearAcquired`, `location_id`, `employeeId`, `createdAt`, `updatedAt`) VALUES
(1, 'CCTV System', 'TAPO', 'C2000', 'Pan and Tilt CCTV Cameras', 'CCTV-4978-589', 'In Use', '2025', 4, NULL, '2026-02-12 00:48:56', '2026-02-13 04:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_printer`
--

CREATE TABLE `tbl_printer` (
  `printerId` int(11) NOT NULL,
  `printerBrand` varchar(255) DEFAULT NULL,
  `printerModel` varchar(100) NOT NULL,
  `printerSerial` varchar(100) DEFAULT NULL,
  `yearAcquired` year(4) DEFAULT NULL,
  `employeeId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_printer`
--

INSERT INTO `tbl_printer` (`printerId`, `printerBrand`, `printerModel`, `printerSerial`, `yearAcquired`, `employeeId`) VALUES
(0, 'HP DESKJET', '200020', 'SN_11112325', '2000', 645987),
(3, 'HP', 'v13', 'HP-PR-2025-004', '2025', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_software`
--

CREATE TABLE `tbl_software` (
  `softwareId` int(11) NOT NULL,
  `licenseSoftware` varchar(255) NOT NULL,
  `licenseDetails` varchar(100) NOT NULL,
  `licenseType` enum('Perpetual','Subscription') DEFAULT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `employeeId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_software`
--

INSERT INTO `tbl_software` (`softwareId`, `licenseSoftware`, `licenseDetails`, `licenseType`, `expiryDate`, `email`, `password`, `employeeId`) VALUES
(12, 'Adobe Photoshop', 'NONE', 'Perpetual', '2026-11-11 00:00:00', 'nia.lextermanuel@gmail.com', 'asdasdsad', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_systemunit`
--

CREATE TABLE `tbl_systemunit` (
  `systemunitId` int(11) NOT NULL,
  `systemUnitCategory` enum('Pre-Built','Custom Built') DEFAULT NULL,
  `systemUnitBrand` varchar(100) NOT NULL,
  `specificationProcessor` varchar(255) NOT NULL,
  `specificationMemory` varchar(255) NOT NULL,
  `specificationGPU` varchar(255) NOT NULL,
  `specificationStorage` varchar(255) NOT NULL,
  `systemUnitSerial` varchar(255) NOT NULL,
  `yearAcquired` year(4) DEFAULT NULL,
  `employeeId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_systemunit`
--

INSERT INTO `tbl_systemunit` (`systemunitId`, `systemUnitCategory`, `systemUnitBrand`, `specificationProcessor`, `specificationMemory`, `specificationGPU`, `specificationStorage`, `systemUnitSerial`, `yearAcquired`, `employeeId`) VALUES
(1, 'Custom Built', 'asd', 'asd', 'asd', 'asd', 'asd', 'asd', '2000', NULL),
(2, 'Pre-Built', '4214', '4213', '4124', '24134', '4214', '42141321412', '0000', 1),
(3, 'Pre-Built', 'HP2000', 'Core i7', '16GB', 'RTX 2060', '512GB SSD', 'SN_200011', '2000', 1),
(7, 'Custom Built', 'dasd', 'eqwewqe', 'ewqe', 'wqeqe', 'qweqewq', 'qweqweq', '2025', 645987);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_due_soon_maintenance`
-- (See below for the actual view)
--
CREATE TABLE `view_due_soon_maintenance` (
`scheduleId` int(11)
,`equipmentType` varchar(50)
,`equipmentId` int(11)
,`maintenanceFrequency` enum('Monthly','Quarterly','Semi-Annual','Annual')
,`lastMaintenanceDate` date
,`nextDueDate` date
,`days_until_due` int(7)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_maintenance_dashboard`
-- (See below for the actual view)
--
CREATE TABLE `view_maintenance_dashboard` (
`scheduleId` int(11)
,`equipmentType` varchar(50)
,`equipmentId` int(11)
,`maintenanceFrequency` enum('Monthly','Quarterly','Semi-Annual','Annual')
,`lastMaintenanceDate` date
,`nextDueDate` date
,`days_until_due` int(7)
,`status` varchar(8)
,`actual_last_maintenance` datetime
,`last_condition_rating` varchar(9)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_maintenance_due`
-- (See below for the actual view)
--
CREATE TABLE `view_maintenance_due` (
`scheduleId` int(11)
,`typeName` varchar(50)
,`equipmentId` int(11)
,`lastMaintenanceDate` date
,`nextDueDate` date
,`daysUntilDue` int(7)
,`status` varchar(9)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_maintenance_master`
-- (See below for the actual view)
--
CREATE TABLE `view_maintenance_master` (
`type_name` varchar(100)
,`type_id` int(11)
,`id` int(11)
,`brand` varchar(255)
,`serial` varchar(255)
,`owner_name` varchar(201)
,`location_name` varchar(255)
,`context` varchar(8)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_overdue_maintenance`
-- (See below for the actual view)
--
CREATE TABLE `view_overdue_maintenance` (
`scheduleId` int(11)
,`equipmentType` varchar(50)
,`equipmentId` int(11)
,`maintenanceFrequency` enum('Monthly','Quarterly','Semi-Annual','Annual')
,`lastMaintenanceDate` date
,`nextDueDate` date
,`days_overdue` int(7)
);

-- --------------------------------------------------------

--
-- Structure for view `view_due_soon_maintenance`
--
DROP TABLE IF EXISTS `view_due_soon_maintenance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_due_soon_maintenance`  AS SELECT `ms`.`scheduleId` AS `scheduleId`, `ms`.`equipmentType` AS `equipmentType`, `ms`.`equipmentId` AS `equipmentId`, `ms`.`maintenanceFrequency` AS `maintenanceFrequency`, `ms`.`lastMaintenanceDate` AS `lastMaintenanceDate`, `ms`.`nextDueDate` AS `nextDueDate`, to_days(`ms`.`nextDueDate`) - to_days(curdate()) AS `days_until_due` FROM `tbl_maintenance_schedule` AS `ms` WHERE `ms`.`nextDueDate` between curdate() and curdate() + interval 7 day AND `ms`.`isActive` = 1 ORDER BY to_days(`ms`.`nextDueDate`) - to_days(curdate()) ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_maintenance_dashboard`
--
DROP TABLE IF EXISTS `view_maintenance_dashboard`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_maintenance_dashboard`  AS SELECT `ms`.`scheduleId` AS `scheduleId`, `ms`.`equipmentType` AS `equipmentType`, `ms`.`equipmentId` AS `equipmentId`, `ms`.`maintenanceFrequency` AS `maintenanceFrequency`, `ms`.`lastMaintenanceDate` AS `lastMaintenanceDate`, `ms`.`nextDueDate` AS `nextDueDate`, to_days(`ms`.`nextDueDate`) - to_days(curdate()) AS `days_until_due`, CASE WHEN `ms`.`nextDueDate` < curdate() THEN 'overdue' WHEN to_days(`ms`.`nextDueDate`) - to_days(curdate()) <= 7 THEN 'due_soon' ELSE 'ok' END AS `status`, (select `mh`.`maintenanceDate` from `tbl_maintenance_record` `mh` where `mh`.`scheduleId` = `ms`.`scheduleId` order by `mh`.`maintenanceDate` desc limit 1) AS `actual_last_maintenance`, (select `mh`.`conditionRating` from `tbl_maintenance_record` `mh` where `mh`.`scheduleId` = `ms`.`scheduleId` order by `mh`.`maintenanceDate` desc limit 1) AS `last_condition_rating` FROM `tbl_maintenance_schedule` AS `ms` WHERE `ms`.`isActive` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `view_maintenance_due`
--
DROP TABLE IF EXISTS `view_maintenance_due`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_maintenance_due`  AS SELECT `ms`.`scheduleId` AS `scheduleId`, `r`.`typeName` AS `typeName`, `ms`.`equipmentId` AS `equipmentId`, `ms`.`lastMaintenanceDate` AS `lastMaintenanceDate`, `ms`.`nextDueDate` AS `nextDueDate`, to_days(`ms`.`nextDueDate`) - to_days(curdate()) AS `daysUntilDue`, CASE WHEN `ms`.`nextDueDate` < curdate() THEN 'Overdue' WHEN to_days(`ms`.`nextDueDate`) - to_days(curdate()) <= 7 THEN 'Due Soon' ELSE 'Scheduled' END AS `status` FROM (`tbl_maintenance_schedule` `ms` join `tbl_equipment_type_registry` `r` on(`ms`.`equipmentType` = `r`.`typeId`)) WHERE `ms`.`isActive` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `view_maintenance_master`
--
DROP TABLE IF EXISTS `view_maintenance_master`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_maintenance_master`  AS SELECT 'System Unit' AS `type_name`, `r`.`typeId` AS `type_id`, `s`.`systemunitId` AS `id`, `s`.`systemUnitBrand` AS `brand`, `s`.`systemUnitSerial` AS `serial`, concat(`e`.`firstName`,' ',`e`.`lastName`) AS `owner_name`, `l`.`location_name` AS `location_name`, 'Employee' AS `context` FROM (((`tbl_systemunit` `s` join `tbl_equipment_type_registry` `r` on(`r`.`tableName` = 'tbl_systemunit')) left join `tbl_employee` `e` on(`s`.`employeeId` = `e`.`employeeId`)) left join `location` `l` on(`e`.`location_id` = `l`.`location_id`))union all select 'All-in-One' AS `type_name`,`r`.`typeId` AS `type_id`,`a`.`allinoneId` AS `id`,`a`.`allinoneBrand` AS `brand`,'N/A' AS `serial`,concat(`e`.`firstName`,' ',`e`.`lastName`) AS `owner_name`,`l`.`location_name` AS `location_name`,'Employee' AS `context` from (((`tbl_allinone` `a` join `tbl_equipment_type_registry` `r` on(`r`.`tableName` = 'tbl_allinone')) left join `tbl_employee` `e` on(`a`.`employeeId` = `e`.`employeeId`)) left join `location` `l` on(`e`.`location_id` = `l`.`location_id`)) union all select 'Monitor' AS `type_name`,`r`.`typeId` AS `type_id`,`m`.`monitorId` AS `id`,`m`.`monitorBrand` AS `brand`,`m`.`monitorSerial` AS `serial`,concat(`e`.`firstName`,' ',`e`.`lastName`) AS `owner_name`,`l`.`location_name` AS `location_name`,'Employee' AS `context` from (((`tbl_monitor` `m` join `tbl_equipment_type_registry` `r` on(`r`.`tableName` = 'tbl_monitor')) left join `tbl_employee` `e` on(`m`.`employeeId` = `e`.`employeeId`)) left join `location` `l` on(`e`.`location_id` = `l`.`location_id`)) union all select 'Printer' AS `type_name`,`r`.`typeId` AS `type_id`,`p`.`printerId` AS `id`,coalesce(`p`.`printerBrand`,'Unknown') AS `brand`,`p`.`printerSerial` AS `serial`,concat(`e`.`firstName`,' ',`e`.`lastName`) AS `owner_name`,`l`.`location_name` AS `location_name`,'Employee' AS `context` from (((`tbl_printer` `p` join `tbl_equipment_type_registry` `r` on(`r`.`tableName` = 'tbl_printer')) left join `tbl_employee` `e` on(`p`.`employeeId` = `e`.`employeeId`)) left join `location` `l` on(`e`.`location_id` = `l`.`location_id`)) union all select `o`.`equipmentType` AS `type_name`,`r`.`typeId` AS `type_id`,`o`.`otherEquipmentId` AS `id`,`o`.`brand` AS `brand`,`o`.`serialNumber` AS `serial`,case when `r`.`context` = 'Employee' then concat(`e`.`firstName`,' ',`e`.`lastName`) else 'N/A' end AS `owner_name`,case when `r`.`context` = 'Employee' then `el`.`location_name` else `l`.`location_name` end AS `location_name`,`r`.`context` AS `context` from ((((`tbl_otherequipment` `o` join `tbl_equipment_type_registry` `r` on(`r`.`tableName` = 'tbl_otherequipment' and `o`.`equipmentType` = `r`.`typeName`)) left join `location` `l` on(`o`.`location_id` = `l`.`location_id`)) left join `tbl_employee` `e` on(`o`.`employeeId` = `e`.`employeeId`)) left join `location` `el` on(`e`.`location_id` = `el`.`location_id`))  ;

-- --------------------------------------------------------

--
-- Structure for view `view_overdue_maintenance`
--
DROP TABLE IF EXISTS `view_overdue_maintenance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_overdue_maintenance`  AS SELECT `ms`.`scheduleId` AS `scheduleId`, `ms`.`equipmentType` AS `equipmentType`, `ms`.`equipmentId` AS `equipmentId`, `ms`.`maintenanceFrequency` AS `maintenanceFrequency`, `ms`.`lastMaintenanceDate` AS `lastMaintenanceDate`, `ms`.`nextDueDate` AS `nextDueDate`, to_days(curdate()) - to_days(`ms`.`nextDueDate`) AS `days_overdue` FROM `tbl_maintenance_schedule` AS `ms` WHERE `ms`.`nextDueDate` < curdate() AND `ms`.`isActive` = 1 ORDER BY to_days(curdate()) - to_days(`ms`.`nextDueDate`) DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `parent_location_id` (`parent_location_id`),
  ADD KEY `location_type_id_idx` (`location_type_id`);

--
-- Indexes for table `location_type`
--
ALTER TABLE `location_type`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_time` (`email`,`attempt_time`),
  ADD KEY `idx_ip_time` (`ip_address`,`attempt_time`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_module` (`role`,`module`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `tbl_accounts`
--
ALTER TABLE `tbl_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `tbl_allinone`
--
ALTER TABLE `tbl_allinone`
  ADD PRIMARY KEY (`allinoneId`),
  ADD KEY `employeeId` (`employeeId`);

--
-- Indexes for table `tbl_checklist_category`
--
ALTER TABLE `tbl_checklist_category`
  ADD PRIMARY KEY (`categoryId`),
  ADD KEY `idx_template` (`templateId`);

--
-- Indexes for table `tbl_checklist_item`
--
ALTER TABLE `tbl_checklist_item`
  ADD PRIMARY KEY (`itemId`),
  ADD KEY `idx_category` (`categoryId`);

--
-- Indexes for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  ADD PRIMARY KEY (`employeeId`),
  ADD KEY `fk_employee_location` (`location_id`);

--
-- Indexes for table `tbl_equipment_type_registry`
--
ALTER TABLE `tbl_equipment_type_registry`
  ADD PRIMARY KEY (`typeId`);

--
-- Indexes for table `tbl_maintenance_frequency`
--
ALTER TABLE `tbl_maintenance_frequency`
  ADD PRIMARY KEY (`frequencyId`);

--
-- Indexes for table `tbl_maintenance_record`
--
ALTER TABLE `tbl_maintenance_record`
  ADD PRIMARY KEY (`recordId`),
  ADD KEY `idx_main_lookup` (`equipmentTypeId`,`equipmentId`),
  ADD KEY `idx_schedule` (`scheduleId`),
  ADD KEY `idx_equipment` (`equipmentTypeId`,`equipmentId`),
  ADD KEY `idx_date` (`maintenanceDate`);

--
-- Indexes for table `tbl_maintenance_schedule`
--
ALTER TABLE `tbl_maintenance_schedule`
  ADD PRIMARY KEY (`scheduleId`),
  ADD KEY `idx_equipment` (`equipmentType`,`equipmentId`),
  ADD KEY `idx_due_date` (`nextDueDate`),
  ADD KEY `idx_active` (`isActive`);

--
-- Indexes for table `tbl_maintenance_template`
--
ALTER TABLE `tbl_maintenance_template`
  ADD PRIMARY KEY (`templateId`);

--
-- Indexes for table `tbl_monitor`
--
ALTER TABLE `tbl_monitor`
  ADD PRIMARY KEY (`monitorId`),
  ADD KEY `employeeId` (`employeeId`);

--
-- Indexes for table `tbl_otherequipment`
--
ALTER TABLE `tbl_otherequipment`
  ADD PRIMARY KEY (`otherEquipmentId`),
  ADD UNIQUE KEY `uniq_serial` (`serialNumber`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_location` (`location_id`),
  ADD KEY `idx_employee` (`employeeId`);

--
-- Indexes for table `tbl_printer`
--
ALTER TABLE `tbl_printer`
  ADD PRIMARY KEY (`printerId`),
  ADD KEY `employeeId` (`employeeId`);

--
-- Indexes for table `tbl_software`
--
ALTER TABLE `tbl_software`
  ADD PRIMARY KEY (`softwareId`),
  ADD KEY `employeeId` (`employeeId`);

--
-- Indexes for table `tbl_systemunit`
--
ALTER TABLE `tbl_systemunit`
  ADD PRIMARY KEY (`systemunitId`),
  ADD KEY `tbl_equipment_ibfk_2` (`employeeId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `location_type`
--
ALTER TABLE `location_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_accounts`
--
ALTER TABLE `tbl_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_allinone`
--
ALTER TABLE `tbl_allinone`
  MODIFY `allinoneId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_checklist_category`
--
ALTER TABLE `tbl_checklist_category`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_checklist_item`
--
ALTER TABLE `tbl_checklist_item`
  MODIFY `itemId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  MODIFY `employeeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2147483648;

--
-- AUTO_INCREMENT for table `tbl_equipment_type_registry`
--
ALTER TABLE `tbl_equipment_type_registry`
  MODIFY `typeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_maintenance_frequency`
--
ALTER TABLE `tbl_maintenance_frequency`
  MODIFY `frequencyId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_maintenance_record`
--
ALTER TABLE `tbl_maintenance_record`
  MODIFY `recordId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_maintenance_schedule`
--
ALTER TABLE `tbl_maintenance_schedule`
  MODIFY `scheduleId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_maintenance_template`
--
ALTER TABLE `tbl_maintenance_template`
  MODIFY `templateId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_monitor`
--
ALTER TABLE `tbl_monitor`
  MODIFY `monitorId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tbl_otherequipment`
--
ALTER TABLE `tbl_otherequipment`
  MODIFY `otherEquipmentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_software`
--
ALTER TABLE `tbl_software`
  MODIFY `softwareId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_systemunit`
--
ALTER TABLE `tbl_systemunit`
  MODIFY `systemunitId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_allinone`
--
ALTER TABLE `tbl_allinone`
  ADD CONSTRAINT `tbl_allinone_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `tbl_employee` (`employeeId`);

--
-- Constraints for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  ADD CONSTRAINT `fk_employee_location` FOREIGN KEY (`location_id`) REFERENCES `location` (`location_id`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_maintenance_record`
--
ALTER TABLE `tbl_maintenance_record`
  ADD CONSTRAINT `fk_history_schedule` FOREIGN KEY (`scheduleId`) REFERENCES `tbl_maintenance_schedule` (`scheduleId`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_monitor`
--
ALTER TABLE `tbl_monitor`
  ADD CONSTRAINT `tbl_monitor_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `tbl_employee` (`employeeId`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `tbl_otherequipment`
--
ALTER TABLE `tbl_otherequipment`
  ADD CONSTRAINT `fk_other_employee` FOREIGN KEY (`employeeId`) REFERENCES `tbl_employee` (`employeeId`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_other_location` FOREIGN KEY (`location_id`) REFERENCES `location` (`location_id`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_printer`
--
ALTER TABLE `tbl_printer`
  ADD CONSTRAINT `tbl_printer_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `tbl_employee` (`employeeId`);

--
-- Constraints for table `tbl_software`
--
ALTER TABLE `tbl_software`
  ADD CONSTRAINT `tbl_software_ibfk_1` FOREIGN KEY (`employeeId`) REFERENCES `tbl_employee` (`employeeId`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `tbl_systemunit`
--
ALTER TABLE `tbl_systemunit`
  ADD CONSTRAINT `tbl_systemunit_ibfk_2` FOREIGN KEY (`employeeId`) REFERENCES `tbl_employee` (`employeeId`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
