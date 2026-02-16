-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3310
-- Generation Time: Feb 13, 2026 at 01:00 AM
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
(3, 'SystemSuperAdmin', 'inventory@upriis.local', '$2y$12$RAUQs6D0FBVNz.ky7N6rJegTTOCmDxYpO850YwZMlxyWX4bLDKl9G', 'Super Admin', 'Active', 0, NULL, NULL, NULL, 0, NULL, '2026-02-09 23:26:19', '2026-02-12 07:27:00', NULL);

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
(645987, 'Demi', NULL, 'Xochitl', '', 'OJT Trainee', '2006-02-10', 'Male', 'Casual', 'employee_645987_1770779085.jpeg', 13, '2026-02-11 03:04:45', '2026-02-11 03:04:45', 1);

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
(1, 'CCTV', 'TAPO', 'C615F KIT', '', 'CC-OE-5872-001', 'In Use', '2025', 9, NULL, '2026-02-12 00:48:56', NULL);

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
(12, 'asd', 'asd', 'Perpetual', '2026-11-11 00:00:00', 'asd', 'asdasdsad', 1);

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
(3, 'Pre-Built', 'HP2000', 'Core i7', '16GB', 'RTX 2060', '512GB SSD', 'SN_200011', '2000', 1);

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
-- Indexes for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  ADD PRIMARY KEY (`employeeId`),
  ADD KEY `fk_employee_location` (`location_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

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
-- AUTO_INCREMENT for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  MODIFY `employeeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2147483648;

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
  MODIFY `systemunitId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
