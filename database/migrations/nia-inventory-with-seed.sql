-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3310
-- Generation Time: Mar 04, 2026 at 07:42 AM
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
  `action` varchar(50) NOT NULL COMMENT 'e.g., CREATE, UPDATE, DELETE, LOGIN, LOGOUT, ERROR',
  `module` varchar(50) DEFAULT NULL COMMENT 'e.g., Divisions, Sections, Units, Computers, Settings, Profile, Auth',
  `description` text DEFAULT NULL COMMENT 'Human-readable summary of the change',
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `success` tinyint(1) DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `email`, `action`, `module`, `description`, `ip_address`, `user_agent`, `success`, `timestamp`) VALUES
(2, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 04:02:27'),
(3, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 13; RMX3081 Build/RKQ1.211119.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.79 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/548.0.0.37.65;]', 1, '2026-02-26 05:00:50'),
(4, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1, '2026-02-26 05:43:08'),
(5, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: inventory@upriis.local', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 0, '2026-02-26 05:44:10'),
(6, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: inventory@upriis.local', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 0, '2026-02-26 05:44:20'),
(7, 2, 'superadmin', 'CREATE', 'Accounts', 'Created Admin account for Jing Alexis Santiago (javs3116@gmail.com), Status: Active.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1, '2026-02-26 05:45:20'),
(8, 3, 'javs3116@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 05:46:03'),
(9, 2, 'superadmin', 'CREATE', 'Accounts', 'Created Admin account for Roniel Jade Verdadero (ronieljjadee@gmail.com), Status: Active.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1, '2026-02-26 05:47:18'),
(10, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: lexternmanuel@gmail.com', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 0, '2026-02-26 05:47:50'),
(11, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: lexternmanuel@gmail.com', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 0, '2026-02-26 05:47:59'),
(12, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: lexternmanuel@gmail.com', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 0, '2026-02-26 05:48:11'),
(13, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: lextermanuel@gmail.com', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 0, '2026-02-26 05:48:25'),
(14, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: superadmin (wrong password)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-26 06:13:29'),
(15, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:13:42'),
(16, 2, 'superadmin', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:40:17'),
(17, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: 1111, CPU: Ryzen 5700X, RAM: 16GB DDR4, Storage: 1TB SSD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:47:32'),
(18, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: 0002, CPU: Ryzen 5700X, RAM: 16GB DDR4, Storage: 1TB SSD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:54:42'),
(19, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 2) — Brand: AMD, Serial: 0002. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:55:13'),
(20, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:56:02'),
(21, 2, 'superadmin', 'CREATE', 'Accounts', 'Created Admin account for Angelito Padolina (angelitopadolina@gmail.com), Status: Active.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:57:59'),
(22, 2, 'superadmin', 'CREATE', 'Employees', 'Added employee ALVIN MANUEL (Employee ID: 998987, Position: Acting Department Manager, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1, '2026-02-26 06:58:01'),
(23, 5, 'angelitopadolina@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 13; RMX3081 Build/RKQ1.211119.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.79 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/548.0.0.37.65;]', 1, '2026-02-26 06:58:36'),
(24, 2, 'superadmin', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:59:00'),
(25, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: angelitopadolina@gmail.com (wrong password)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-26 06:59:17'),
(26, 5, 'angelitopadolina@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:59:23'),
(27, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 324, Size: 32 Inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 06:59:56'),
(28, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 3242, Size: 32 Inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:00:29'),
(29, 2, 'superadmin', 'CREATE', 'Employees', 'Added employee VIVENCIA DELA CRUZ (Employee ID: 285419, Position: Division Manager A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', 1, '2026-02-26 07:00:34'),
(30, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0003, CPU: i5- 8th Gen, RAM: 4GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:02:35'),
(31, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: BenQ, Serial: 567, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:03:20'),
(32, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:04:57'),
(33, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ASUS AIO PC (Pre-Built), Serial: 0004, CPU: i7-8th Gen, RAM: 16GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:08:36'),
(34, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 3) — Brand: ACER, Serial: 0003. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:08:52'),
(35, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee VIVENCIA DELA CRUZ (Employee ID: 285419, Position: Division Manager A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:10:41'),
(36, 2, 'superadmin', 'UPDATE', 'Employees', 'Restored employee VIVENCIA DELA CRUZ (ID: 285419) from archive.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:11:41'),
(37, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JASMIN FERRY (Employee ID: 881731, Position: Utility Worker A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:12:20'),
(38, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 4) — Brand: ASUS AIO PC, Serial: 0004. Assigned to employee ID 998987.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:12:35'),
(39, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 4) — Brand: ASUS AIO PC, Serial: 0004. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:12:52'),
(40, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 3456345, Size: 32 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:13:00'),
(41, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: 0005, CPU: i7-8th, RAM: 16GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2018.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:14:12'),
(42, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 567567, Size: 24 inches, Year: 2025.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:14:24'),
(43, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 5) — Brand: INTEL, Serial: 0005. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:14:39'),
(44, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MA. ELIZABETH LOPEZ (Employee ID: 832208, Position: Public Relations Officer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:16:47'),
(45, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Dell, Serial: 4567456, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:18:34'),
(46, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARK IAN VILLANUEVA (Employee ID: 935640, Position: Senior Computer Services Programmer, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:20:15'),
(47, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JUNE BARIOGA (Employee ID: 790761, Position: Division Manager A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:21:41'),
(48, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARIA ISOBEL PADOLINA (Employee ID: 321110, Position: Administrative Services Chief A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:22:30'),
(49, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee KARYL LANE MARTINEZ (Employee ID: 127188, Position: Accounting Processor A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:23:29'),
(50, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ANGELICA MALLARI (Employee ID: 680891, Position: Data Encoder, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:24:18'),
(51, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: 0006, CPU: Ryzen 5700X, RAM: 16GB DDR4, Storage: 1TB SSD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:24:31'),
(52, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee SHERILYN CAMACHO (Employee ID: 343449, Position: Data Encoder, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:25:06'),
(53, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: 0007, CPU: Ryzen 5 3600, RAM: 32GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:26:12'),
(54, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARIBETH CRUZ (Employee ID: 852567, Position: Senior Supply Officer, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:26:23'),
(55, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Asus, Serial: 456456, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:27:08'),
(56, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARK PAJARILLAGA (Employee ID: 201374, Position: Store Keeper B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:27:26'),
(57, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: INTEL (Pre-Built), Serial: 0008, CPU: i5- 11th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:27:42'),
(58, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: ViewSonic, Serial: 34534, Size: 27 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:28:13'),
(59, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 435643, Size: 32 Inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:28:30'),
(60, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee SHERILL HERNANDEZ (Employee ID: 977936, Position: Senior Data Encoder, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:28:39'),
(61, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 675675, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:29:07'),
(62, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 123123123, Size: 32 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:29:46'),
(63, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee CHERRY BAUTISTA (Employee ID: 205867, Position: Utility Worker A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:30:00'),
(64, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 345345, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:30:16'),
(65, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 45345, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:30:36'),
(66, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee FILIPINA SOMBILLO (Employee ID: 907875, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:30:57'),
(67, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 34534534, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:31:09'),
(68, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 567567567, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:31:31'),
(69, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee RODOLFO QUIZON (Employee ID: 962770, Position: Industrial Security Guard A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:31:55'),
(70, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 43975893, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:32:06'),
(71, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee RODOLFO PADUNAN (Employee ID: 508425, Position: Industrial Security Guard A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:32:37'),
(72, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee BARRY JOSE ORTIZ (Employee ID: 256056, Position: Industrial Security Guard A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:33:30'),
(73, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 4564564, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:33:43'),
(74, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: 0009, CPU: Ryzen 5700X, RAM: 16GB DDR4, Storage: 1TB SSD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:33:53'),
(75, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee CHRISTOPHER CAÑON (Employee ID: 236943, Position: Industrial Security Guard A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:34:18'),
(76, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee RODEL RUBI (Employee ID: 174096, Position: Industrial Security Guard A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:35:01'),
(77, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ROMMEL GROSPE (Employee ID: 889537, Position: Industrial Security Guard A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:36:16'),
(78, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: INTEL (Pre-Built), Serial: 0010, CPU: i3 - 4th Gen, RAM: 4GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2016.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:36:18'),
(79, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee OSCAR DELOS REYES (Employee ID: 958418, Position: Supervising Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:37:16'),
(80, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: 0011, CPU: Ryzen 7 7800X3D, RAM: 32GB DDR5, Storage: 1TB SSD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:37:41'),
(81, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MERNAN BUSUEGO (Employee ID: 937830, Position: Utility Worker A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:38:05'),
(82, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ELLEN JANE SORIANO (Employee ID: 220749, Position: Chief Corporate Accountant B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:38:54'),
(83, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ROSALINDA SORIANO (Employee ID: 667663, Position: Financial Planning Specialist B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:39:43'),
(84, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: INTEL (Pre-Built), Serial: 0012, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:40:25'),
(85, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ALMA MANAOIS (Employee ID: 156379, Position: Senior Financial Planning Analyst, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:40:55'),
(86, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 8) — Brand: DELL AIO, Serial: 0008. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:41:19'),
(87, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JEAN CAYLA SANTOS (Employee ID: 261775, Position: Corporate Accounts Analyst, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:41:41'),
(88, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 10) — Brand: LENOVO, Serial: 0010. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:41:58'),
(89, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JET LEGASPI (Employee ID: 506073, Position: Cashier A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:42:27'),
(90, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 12) — Brand: HP AIO, Serial: 0012. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:42:44'),
(91, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 56464, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:43:35'),
(92, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 4564566, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:44:02'),
(93, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee GRACE BADILLA (Employee ID: 451973, Position: Supervising Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:44:14'),
(94, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 4563463546, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:44:37'),
(95, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee GERALDINE DARIO (Employee ID: 846559, Position: Supervising Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:44:52'),
(96, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee LANCER GALANG (Employee ID: 730691, Position: Senior Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:45:47'),
(97, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: 0013, CPU: Ryzen 5 3400G, RAM: 16GB DDR4, Storage: 256GB SSD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:46:58'),
(98, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee AIME MARIE JACINTO (Employee ID: 148826, Position: Senior Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:47:39'),
(99, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0014, CPU: i5 - 13th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:48:07'),
(100, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARLON SORIANO (Employee ID: 608389, Position: Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:48:23'),
(101, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0015, CPU: i5 - 13th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:49:18'),
(102, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee NAPOLEON FERDINAND MENDOZA (Employee ID: 580106, Position: Data Encoder, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:49:22'),
(103, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0016, CPU: i5 - 13th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:49:57'),
(104, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MELVIN SANTIAGO (Employee ID: 170456, Position: Principal Engineer C, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:50:19'),
(105, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee IRENE ESTACIO (Employee ID: 784148, Position: Senior Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:51:02'),
(106, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JOHN CARLO CORDOVA (Employee ID: 930795, Position: Hydrologist, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:51:55'),
(107, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 43534, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:52:26'),
(108, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ARMANDO TALPLACIDO (Employee ID: 144784, Position: Senior Draftsman, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:52:40'),
(109, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Dell, Serial: 5345345, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:52:47'),
(110, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 435435, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:53:09'),
(111, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0017, CPU: i5 - 7th Gen, RAM: 16GB DDR4, Storage: 1TB HDD, Year: 2018.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:53:10'),
(112, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARYJANE CALLANTA (Employee ID: 221480, Position: Electronics Communication System Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:53:38'),
(113, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: 0018, CPU: Ryzen 5700X, RAM: 16GB DDR4, Storage: 1TB SSD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:53:47'),
(114, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3453455, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:54:08'),
(115, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 546456, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:54:28'),
(116, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 645645, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:54:42'),
(117, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee HEDGIE NICOLAS (Employee ID: 707158, Position: Electronics Communication System Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:54:49'),
(118, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Dell, Serial: 435345, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:55:07'),
(119, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARIA THERESA BUADO (Employee ID: 902132, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:55:46'),
(120, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee DANIEL JOSON (Employee ID: 464803, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:56:39'),
(121, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 48576, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:57:00'),
(122, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0019, CPU: i7-8th Gen, RAM: 4GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:57:14'),
(123, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 678678, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:57:17'),
(124, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 4756, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:57:30'),
(125, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MICHAEL REYES (Employee ID: 386196, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:57:39'),
(126, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 87456, Size: 18 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:57:49'),
(127, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 8347658, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:58:09'),
(128, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 845769456, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:58:35'),
(129, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JUNE BENEMERITO (Employee ID: 902680, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:58:54'),
(130, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0020, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:59:36'),
(131, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ORLY BENEMERITO (Employee ID: 329651, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 07:59:53'),
(132, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JUNE NARITO (Employee ID: 732960, Position: Electronics Communication System Operator, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:00:48'),
(133, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JEFFREY BUSTILLOS (Employee ID: 390778, Position: Electronics Communication System Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:01:51'),
(134, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 5464565, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:02:02'),
(135, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 39458934, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:02:38'),
(136, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JESSIE BAYLON (Employee ID: 994948, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:02:51'),
(137, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Lenovo, Serial: 8698456, Size: 18 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:03:06'),
(138, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0021, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 1TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:03:39'),
(139, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee LEIFE VILLAFLOR (Employee ID: 221985, Position: Principal Engineer C, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:03:49'),
(140, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0022, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 1TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:04:11'),
(141, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JERICO GALDORES (Employee ID: 501518, Position: Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:04:27'),
(142, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0023, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:04:58'),
(143, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MICHAEL JEROME TORRES (Employee ID: 285583, Position: Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:05:09'),
(144, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ALBERTO LISING (Employee ID: 805509, Position: Driver Mechanic B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:06:02'),
(145, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 348753, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:06:12'),
(146, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee GARY LEO SANTURAY (Employee ID: 369176, Position: Driver Mechanic B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:06:43'),
(147, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ASUS AIO PC (Pre-Built), Serial: 0024, CPU: i5- 8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD, Year: 2018.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:06:45'),
(148, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 84537689, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:06:53'),
(149, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 12) — Brand: HP AIO PC, Serial: 0012. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:07:05'),
(150, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 8) — Brand: DELL AIO PC, Serial: 0008. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:07:16'),
(151, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 436789456, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:07:18'),
(152, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee VIRGILIO PANGAN (Employee ID: 463292, Position: Heavy Equipment Operator, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:07:26'),
(153, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 74564, Size: 18 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:09:39'),
(154, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee EDGARDO BAGUISA (Employee ID: 858115, Position: Heavy Equipment Operator, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:09:46'),
(155, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 45986, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:10:08'),
(156, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 4385734, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:10:39'),
(157, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee INOCENCIO SOMERA (Employee ID: 190331, Position: Senior Automotive Mechanic, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:11:06'),
(158, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee REYNALDO REYES (Employee ID: 665009, Position: Automotive Mechanic A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:11:48'),
(159, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP AIO PC (Pre-Built), Serial: 0025, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:12:01'),
(160, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee FLAVIANO BAGUISA (Employee ID: 367049, Position: Welder A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:12:29'),
(161, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MERRY DAWN HONORIO (Employee ID: 890613, Position: Community Relations Chief B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:14:11'),
(162, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0026, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:14:21'),
(163, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee CAMILLA MARIE ONDRADE (Employee ID: 903089, Position: Supervising Irrigators Development Officer, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:15:18'),
(164, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0027, CPU: i5 - 13th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:15:40'),
(165, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee EDNA CRISTOBAL (Employee ID: 527628, Position: Supervising Irrigators Development Officer, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:16:05'),
(166, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee SHARON ORENA (Employee ID: 573917, Position: Senior Irrigators Development Officer, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:16:57');
INSERT INTO `activity_log` (`id`, `user_id`, `email`, `action`, `module`, `description`, `ip_address`, `user_agent`, `success`, `timestamp`) VALUES
(167, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 345345345, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:17:23'),
(168, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 45986456, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:17:42'),
(169, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee EMILY FRANCISCO (Employee ID: 393709, Position: Irrigators Development Officer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:17:48'),
(170, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 39485, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:18:01'),
(171, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 564564564, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:18:24'),
(172, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ELEONOR NOCUM (Employee ID: 154219, Position: Industrial Relations Management/ Development Officer A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:18:46'),
(173, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP AIO PC (Pre-Built), Serial: 0028, CPU: i3 - 7th Gen, RAM: 4GB DDR4, Storage: 512GB SSD, Year: 2017.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:18:52'),
(174, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 89324758, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:19:06'),
(175, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 4576894, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:19:27'),
(176, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ROEL VEGIGA (Employee ID: 902843, Position: Division Manager A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:19:37'),
(177, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 4387569843, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:19:41'),
(178, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 834725, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:19:59'),
(179, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: LENOVO (Pre-Built), Serial: 0029, CPU: i3 -, RAM: 4GB DDR4, Storage: 1TB HDD, Year: 2016.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:20:06'),
(180, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 43298509345, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:20:14'),
(181, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 29) — Brand: LENOVO, Serial: 0029. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:20:23'),
(182, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee NOEL JEROME PAPA (Employee ID: 411863, Position: Senior Computer Operator, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:20:23'),
(183, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3465435, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:20:34'),
(184, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MICHAEL JOHN SANTOS (Employee ID: 130281, Position: Senior Artist Illustrator, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:21:03'),
(185, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ALGER PASCUAL (Employee ID: 536111, Position: Photographer IV, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:21:42'),
(186, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0030, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 1TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:21:46'),
(187, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ROSE ANN IGNACIO (Employee ID: 290868, Position: Attorney IV, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:22:37'),
(188, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0031, CPU: i5- 8th Gen, RAM: 8GB DDR4, Storage: 2TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:23:05'),
(189, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARVIN PURIFICACION (Employee ID: 313227, Position: Legal Researcher III, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:23:23'),
(190, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0032, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:24:09'),
(191, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MA. CELINE ESTEBAN (Employee ID: 831571, Position: Data Encoder, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:24:14'),
(192, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0033, CPU: i7 - 12th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:24:58'),
(193, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee RAQUEL LIWAG (Employee ID: 661369, Position: Records Officer D, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:25:21'),
(194, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JING ALEXIS SANTIAGO (Employee ID: 911855, Position: Office Equipment Technician A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:26:10'),
(195, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: DELL AIO PC (Pre-Built), Serial: 0034, CPU: i7 - 11th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:26:41'),
(196, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee CHRISTIAN GYVER RED (Employee ID: 396602, Position: Supply Officer III, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:26:52'),
(197, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee DEXTER CRUZ (Employee ID: 751447, Position: Senior Engineer A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:27:30'),
(198, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0035, CPU: i5 - 13th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:27:38'),
(199, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee CRESTA-LEE CASTILLO (Employee ID: 157351, Position: Procurement Analyst A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:28:15'),
(200, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0036, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:28:22'),
(201, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0037, CPU: i7-8th Gen, RAM: 4GB DDR4, Storage: 1TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:29:33'),
(202, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee NAPOLEON PASCUAL (Employee ID: 361044, Position: Computer Services Programmer B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:29:53'),
(203, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0038, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:30:20'),
(204, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ROSE QUEJADA (Employee ID: 548437, Position: Records Assistant, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:30:34'),
(205, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: DELL AIO PC (Pre-Built), Serial: 0039, CPU: i7 - 11th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:31:12'),
(206, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARIBEL OROBIA (Employee ID: 749446, Position: Records Assistant, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:31:12'),
(207, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MICOLE BRYLLE VILLANUEVA (Employee ID: 304314, Position: Data Encoder, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:32:12'),
(208, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee DEBBIE ANNE DE LEON (Employee ID: 170722, Position: Procurement Analyst A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:33:07'),
(209, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0040, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:33:24'),
(210, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee LILIBETH SISON (Employee ID: 932573, Position: Accounting Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:33:43'),
(211, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0041, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:34:14'),
(212, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 435345e, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:34:44'),
(213, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 9237498234, Size: 32 Inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:34:59'),
(214, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 943859, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:35:24'),
(215, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: DELL AIO PC (Pre-Built), Serial: 0042, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:35:26'),
(216, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ROSEMARIE LORENZO (Employee ID: 552752, Position: Housekeeping Services Headman A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:35:36'),
(217, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 84375, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:35:59'),
(218, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Dell, Serial: 487589, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:36:22'),
(219, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 0043, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 512GB SSD / 2TB HDD, Year: 2019.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:36:22'),
(220, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ALMA OCAMPO (Employee ID: 129437, Position: Household Attendant III, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:36:22'),
(221, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 83274823, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:36:43'),
(222, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: N-VISION, Serial: 438746, Size: 27 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:37:06'),
(223, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee SOLOMON AREJA (Employee ID: 417554, Position: Housekeeping Services Assistant, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:37:11'),
(224, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 3478753, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:37:33'),
(225, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARIA KRISTINA REYES (Employee ID: 104074, Position: Nurse I, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:37:51'),
(226, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 7645645, Size: 21 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:37:58'),
(227, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee IVAN CUEVAS (Employee ID: 492366, Position: Industrial Security Guard A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:38:32'),
(228, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ROEL VERAYO (Employee ID: 265374, Position: Industrial Security Guard A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:39:06'),
(229, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 38) — Brand: HP AIO PC, Serial: 0038. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:39:12'),
(230, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ERICKSON SAN GABRIEL (Employee ID: 276120, Position: Industrial Security Guard A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:39:42'),
(231, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee NUELZON LOPEZ (Employee ID: 731802, Position: Industrial Security Guard A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:40:24'),
(232, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JAYSON TOMINEZ (Employee ID: 498561, Position: Water Resources Facilities Operator B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:41:07'),
(233, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: DELL AIO PC (Pre-Built), Serial: 0044, CPU: i7 - 11th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2020.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:41:22'),
(234, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JEFFREY PANGILINAN (Employee ID: 562171, Position: Survey Aide, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:41:56'),
(235, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee CATHERINE JOY QUINTAO (Employee ID: 115386, Position: Utility Worker A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:42:26'),
(236, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: DELL (Pre-Built), Serial: 0045, CPU: i7 - 7th Gen, RAM: 8GB DDR4, Storage: 128GB SSD / 1TB HDD, Year: 2018.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:42:59'),
(237, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee PINKY SANTOS (Employee ID: 249441, Position: Utility Worker A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:43:01'),
(238, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee FRANCIA MACASAYA (Employee ID: 383802, Position: Utility Worker A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:43:34'),
(239, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER AIO PC (Pre-Built), Serial: 0046, CPU: i3 - 7th Gen, RAM: 4GB DDR4, Storage: 1TB HDD, Year: 2018.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:44:05'),
(240, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee FREDERICK NANGEL (Employee ID: 548486, Position: Utility Worker A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:44:07'),
(241, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 3) — Brand: ACER AIO PC, Serial: 0003. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:44:36'),
(242, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ROMEO EMERALD PLAZA (Employee ID: 307027, Position: Airconditioning Technician I, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:45:04'),
(243, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MANILYN MACAPAGAL (Employee ID: 190650, Position: Office Equipment Technician B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:45:48'),
(244, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: 0047, CPU: i5 - 7th Gen, RAM: 8GB DDR4, Storage: 1TB HDD, Year: 2017.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:46:14'),
(245, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee VICENTE VIBAL (Employee ID: 813438, Position: Watchman III, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:46:28'),
(246, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee PINKY GONZALES (Employee ID: 609250, Position: Accounting Processor B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:47:16'),
(247, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARIA LOURDES DE GUZMAN (Employee ID: 897577, Position: Cashier B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:48:06'),
(248, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JOCELYN DELA CRUZ (Employee ID: 410918, Position: Accounting Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:48:50'),
(249, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 943593, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:49:30'),
(250, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee SHERMAINE ATRAJE (Employee ID: 296288, Position: Procurement Assistant A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:49:50'),
(251, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 5675673, Size: 32 Inches, Year: 2025.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:50:02'),
(252, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JONALYN TAN (Employee ID: 389637, Position: Data Encoder, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:50:22'),
(253, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Samsung, Serial: 458678456, Size: 32 Inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:50:22'),
(254, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee KRISSIALEN CALPITO (Employee ID: 888190, Position: Accounting Processor B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:50:55'),
(255, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: LG, Serial: 45456456, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:51:10'),
(256, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: LG, Serial: 48397534, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:51:25'),
(257, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee LAILA KRISCHELLE GARCIA (Employee ID: 559092, Position: Cashiering Assistant, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:51:34'),
(258, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Gamdias, Serial: 345354, Size: 27 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:51:49'),
(259, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: LG, Serial: 85768945, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:52:09'),
(260, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee RICHARD MANABAT (Employee ID: 644136, Position: Senior Engineer A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:52:11'),
(261, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 43564356, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:53:26'),
(262, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Dell, Serial: 9458690, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:53:47'),
(263, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JOHN ARIEL ARISTOTLE MENDOZA (Employee ID: 408115, Position: Senior Engineer A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:53:52'),
(264, 5, 'angelitopadolina@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: Acer, Serial: 875345, Size: 24 inches, Year: 2024.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:54:07'),
(265, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee NIMROD RUFINO (Employee ID: 376614, Position: Driver Mechanic A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:55:21'),
(266, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARIO VILLANUEVA (Employee ID: 651936, Position: Driver Mechanic A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:57:03'),
(267, 2, 'superadmin', 'CREATE', 'Employees', 'Added employee Angelito Padolina (Employee ID: 236784, Position: OJT Trainee, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 08:59:08'),
(268, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-26 23:55:43'),
(269, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:05:26'),
(270, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MICHAEL GARCIA (Employee ID: 900261, Position: Driver Mechanic A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:08:23'),
(271, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee KATE DAGAWIN (Employee ID: 408994, Position: Information Officer C, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:09:14'),
(272, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated System Unit (ID: 47) — Brand: ACERS, Serial: 0047. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:11:41'),
(273, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated System Unit (ID: 47) — Brand: ACER, Serial: 0047. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:11:49'),
(274, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee RUSSELLENIE PRIETO (Employee ID: 517043, Position: Industrial Nurse, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:13:46'),
(275, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee PAULO DUCUSIN (Employee ID: 817307, Position: Watchman III, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:14:34'),
(276, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee TROY GUBA (Employee ID: 915863, Position: Watchman III, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:15:13'),
(277, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated Monitor (ID: 71) — Brand: Acers, Serial: 875345. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:15:27'),
(278, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated Monitor (ID: 71) — Brand: Acer, Serial: 875345. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:15:35'),
(279, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JANFERSON MIRANDA (Employee ID: 595357, Position: Watchman III, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:16:05'),
(280, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee RAUL PLACIDO (Employee ID: 377264, Position: Watchman III, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:18:17'),
(281, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ORLANDO GAMIT (Employee ID: 578203, Position: Foreman B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:19:11'),
(282, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee REYNALDO BANEZ (Employee ID: 329759, Position: Hotel Operations Officer B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:19:59'),
(283, 5, 'angelitopadolina@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:20:10'),
(284, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee BENJAMIN ABAD (Employee ID: 206693, Position: Utility Worker A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:20:34'),
(285, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:23:10'),
(286, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MICHAEL MARQUEZ (Employee ID: 158835, Position: Chef, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:23:57'),
(287, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARK JEFFREY CASTRO (Employee ID: 569494, Position: Housekeeping Services Headman B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:24:44'),
(288, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:25:18'),
(289, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ALVIN HIPOLITO (Employee ID: 888651, Position: Watchman III, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:25:22'),
(290, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee FRANCES ANGELIQUE DE GUZMAN (Employee ID: 800625, Position: Corporate Budget Analyst B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:27:04'),
(291, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee BENELYN RONQUILLO (Employee ID: 353441, Position: Clerk Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:27:43'),
(292, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:28:15'),
(293, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee DEBBIE DAWN SANTIAGO (Employee ID: 893880, Position: Accounting Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:28:38'),
(294, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:30:05'),
(295, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JEAN AIRA BALAGTAS (Employee ID: 975919, Position: Office Equipment Technician B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:30:14'),
(296, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:30:29'),
(297, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:30:45'),
(298, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee DARREN JOSHUA GUEVARRA (Employee ID: 940159, Position: Senior Engineer A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:30:49'),
(299, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:31:13'),
(300, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:31:19'),
(301, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:31:21'),
(302, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:31:23'),
(303, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JOSEPH PAOLO GERONIMO (Employee ID: 284449, Position: Senior Engineer A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:31:28'),
(304, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:31:58'),
(305, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee BERNAN ALEXIS DUCUSIN (Employee ID: 288636, Position: Senior Engineer A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:31:59'),
(306, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MON KEVIN FLORES (Employee ID: 333908, Position: Engineer B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:32:56'),
(307, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee PRINCESS TAMONDONG (Employee ID: 884207, Position: Engineer I, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:33:40'),
(308, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MELVIN NAZAR (Employee ID: 836645, Position: Driver Mechanic B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:34:18'),
(309, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee RYAN ENCOMIENDA (Employee ID: 921537, Position: Engineer A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:34:55'),
(310, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee CARLO JAY CRUZ (Employee ID: 952072, Position: Driver Mechanic A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:35:35'),
(311, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JOEL DE GUZMAN (Employee ID: 979781, Position: Driver Mechanic A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:36:15'),
(312, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:36:22'),
(313, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:36:26'),
(314, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee SAMUEL SAYSON (Employee ID: 579667, Position: Driver Mechanic A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:36:48'),
(315, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:39:19'),
(316, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee EMMANUEL NAGAÑO (Employee ID: 516453, Position: Driver Mechanic B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:40:55'),
(317, 3, 'javs3116@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:56:11'),
(318, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee NESTOR PORTANA (Employee ID: 913515, Position: Driver Mechanic B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:58:40'),
(319, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee DANIEL MACASAYA (Employee ID: 864059, Position: Automotive Mechanic C, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 00:59:20'),
(320, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee BENJ OLIVER CASTRO (Employee ID: 685880, Position: Community Relations Officer B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:00:00'),
(321, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:00:34'),
(322, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee HONEY LEITH DELA CRUZ (Employee ID: 249874, Position: Secretary A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:00:42'),
(323, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee SHIELA PERALTA (Employee ID: 108341, Position: Clerk Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:01:17'),
(324, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JANA TEE (Employee ID: 879471, Position: Senior Data Encoder Controller, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:02:03'),
(325, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee JERICO LOUIS MANGAHAS (Employee ID: 339003, Position: Clerk Processor B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:09:25'),
(326, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ROCHELLE ANGELA RELUSCO (Employee ID: 424481, Position: Clerk Processor A, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:10:11'),
(327, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARC LOUISE DE GUZMAN (Employee ID: 252229, Position: Clerk Processor B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:10:56'),
(328, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee DALE JOSEF PARARUAN (Employee ID: 609813, Position: Data Encoder, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:12:54'),
(329, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee DALE JOSEPH PARARUAN (Employee ID: 609813, Position: Data Encoder, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:13:38'),
(330, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee EDNA FERMIN (Employee ID: 777388, Position: Accountant III, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:14:24'),
(331, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee CHRISTIAN AUSTRIA (Employee ID: 257989, Position: Assistant Cook, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:15:16'),
(332, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ZANDIE ESTEBAN (Employee ID: 492520, Position: Utility Worker A, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:15:54');
INSERT INTO `activity_log` (`id`, `user_id`, `email`, `action`, `module`, `description`, `ip_address`, `user_agent`, `success`, `timestamp`) VALUES
(333, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee PAUL KEVIN MENDOZA (Employee ID: 781007, Position: Watchman III, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:16:29'),
(334, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ARIEL MACAPAGAL (Employee ID: 832805, Position: Watchman III, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:17:28'),
(335, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: ACER AIO PC, Serial: 0046, CPU: i3 - 7th Gen, RAM: 4GB DDR4, Storage: 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:17:50'),
(336, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee MARK JOSEPH PERIA (Employee ID: 234567, Position: Utility Worker A, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:18:00'),
(337, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: DELL AIO PC, Serial: 0044, CPU: i7 - 11th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:18:56'),
(338, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee ANGELO BRANDO CALLEJO (Employee ID: 100752, Position: Utility Worker A, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:18:56'),
(339, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee REGIE BOY REYES (Employee ID: 691402, Position: Utility Worker A, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:19:35'),
(340, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: DELL AIO PC, Serial: 0042, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:20:44'),
(341, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: DELL AIO PC, Serial: 0039, CPU: i7 - 11th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:26:29'),
(342, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: HP AIO PC, Serial: 0038, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:27:07'),
(343, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: DELL AIO PC, Serial: 0034, CPU: i7 - 11th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:27:35'),
(344, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: HP AIO PC, Serial: 0028, CPU: i3 - 7th Gen, RAM: 4GB DDR4, Storage: 512GB SSD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:28:18'),
(345, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: HP AIO PC, Serial: 0025, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:28:57'),
(346, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: ASUS AIO PC, Serial: 0024, CPU: i5- 8th Gen, RAM: i5- 8th Gen, Storage: 256GB SSD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:31:30'),
(347, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: HP AIO PC, Serial: 0012, CPU: i7-8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:32:14'),
(348, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: DELL AIO PC, Serial: 0008, CPU: i5- 11th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:33:22'),
(349, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: ASUS AIO PC, Serial: 0004, CPU: i7-8th Gen, RAM: 16GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:34:11'),
(350, 2, 'superadmin', 'CREATE', 'Computers', 'Added All-in-One — Brand: ACER AIO PC, Serial: 0003, CPU: i5- 8th Gen, RAM: 4GB DDR4, Storage: 256GB SSD / 1TB HDD.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 01:34:55'),
(351, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated System Unit (ID: 47) — Brand: ACER, Serial: 0047. Assigned to employee ID 236784.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:38:21'),
(352, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated System Unit (ID: 47) — Brand: ACER, Serial: 0047. Unassigned.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:38:30'),
(353, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated security settings (0 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:42:01'),
(354, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated security settings (0 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:42:25'),
(355, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated organization settings (0 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:42:48'),
(356, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated organization settings (0 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:42:50'),
(357, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated organization settings (0 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:42:56'),
(358, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated maintenance settings (0 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:43:36'),
(359, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated system settings (0 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:43:42'),
(360, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated system settings (0 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:43:54'),
(361, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated maintenance settings (0 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:54:52'),
(362, 2, 'superadmin', 'CREATE', 'Other Equipment', 'Added NAS — server N/a, Serial: 341, Status: Available, Year: 2000.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:58:30'),
(363, 2, 'superadmin', 'DELETE', 'Other Equipment', 'Deleted NAS (ID: 1) — server N/a, Serial: 341.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 02:58:41'),
(364, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated system settings (4 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 03:00:07'),
(365, 2, 'superadmin', 'CREATE', 'Other Equipment', 'Added NAS — server N/a, Serial: 341, Status: Available, Year: .', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 03:10:32'),
(366, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated system settings (4 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 07:53:39'),
(367, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 8CG9201GT4, CPU: i7 8th Gen, RAM: 4GB DDR4, Storage: 1TB HDD, Year: 2019. Assigned to employee ID 410918.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 07:55:05'),
(368, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3CM9040C85, Size: 24 inches, Year: 2019. Assigned to employee ID 410918.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 07:56:15'),
(369, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee DAISYROSE HIPOLITO (Employee ID: 269219, Position: Accounting Processor A, Status: Job Order).', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 07:56:42'),
(370, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added All-in-One — Brand: HP, Serial: 8CC4080CJR, CPU: Ultra 5 125U, RAM: 16GB DDR5, Storage: 512GB SSD. Assigned to employee ID 667663.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 07:59:30'),
(371, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON WF-C5790, Serial: X3BC005492, Year: 1990. Assigned to employee ID 667663.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:03:11'),
(372, 3, 'javs3116@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/550.0.0.28.106;FBBV/890844927;FBDV/iPhone18,3;FBMD/iPhone;FBSN/iOS;FBSV/26.3;FBSS/3;FBCR/;FBID/phone;FBLC/en_US;FBOP/80]', 1, '2026-02-27 08:06:46'),
(373, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 8CG85040G0, CPU: i7 8th Gen, RAM: 16GB DDR4, Storage: 512SSD / 2TB HDD, Year: 2019. Assigned to employee ID 261775.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:06:53'),
(374, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3CM9040C8K, Size: 24 inches, Year: 2019. Assigned to employee ID 261775.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:08:54'),
(375, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — HP SMART TANK 515, Serial: CN1414S2NT, Year: 1990. Assigned to employee ID 261775.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:12:14'),
(376, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added All-in-One — Brand: HP, Serial: 8CC72316NP, CPU: i3 7th Gen, RAM: 4GB DDR4, Storage: 1TB HDD. Assigned to employee ID 389637.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:16:14'),
(377, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L3550, Serial: XBCF014593, Year: 1990. Assigned to employee ID 389637.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:17:13'),
(378, 3, 'javs3116@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/550.0.0.28.106;FBBV/890844927;FBDV/iPhone18,3;FBMD/iPhone;FBSN/iOS;FBSV/26.3;FBSS/3;FBCR/;FBID/phone;FBLC/en_US;FBOP/80]', 1, '2026-02-27 08:17:57'),
(379, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 8CG85040F3, CPU: i7-8TH GEN, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2019. Assigned to employee ID 296288.', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/550.0.0.28.106;FBBV/890844927;FBDV/iPhone18,3;FBMD/iPhone;FBSN/iOS;FBSV/26.3;FBSS/3;FBCR/;FBID/phone;FBLC/en_US;FBOP/80]', 1, '2026-02-27 08:20:55'),
(380, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3CM9040C98, Size: 24 INCHES, Year: 2019. Assigned to employee ID 296288.', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/550.0.0.28.106;FBBV/890844927;FBDV/iPhone18,3;FBMD/iPhone;FBSN/iOS;FBSV/26.3;FBSS/3;FBCR/;FBID/phone;FBLC/en_US;FBOP/80]', 1, '2026-02-27 08:21:50'),
(381, 3, 'javs3116@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON 3150, Serial: X93K048868, Year: 1990. Assigned to employee ID 296288.', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 [FBAN/FBIOS;FBAV/550.0.0.28.106;FBBV/890844927;FBDV/iPhone18,3;FBMD/iPhone;FBSN/iOS;FBSV/26.3;FBSS/3;FBCR/;FBID/phone;FBLC/en_US;FBOP/80]', 1, '2026-02-27 08:22:55'),
(382, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: DTBEVSP00E03901CB89600, CPU: i5 - 10th Gen, RAM: 8GB, Storage: 256GB SSD / 1TB HDD, Year: 2021. Assigned to employee ID 609250.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:24:29'),
(383, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: ACER, Serial: 03911016542, Size: 24 inches, Year: 2021. Assigned to employee ID 609250.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:26:06'),
(384, 5, 'angelitopadolina@gmail.com', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for ALVIN DE LEON MANUEL (ID: 998987)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 08:26:10'),
(385, 5, 'angelitopadolina@gmail.com', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for ALVIN DE LEON MANUEL (ID: 998987)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 08:26:32'),
(386, 5, 'angelitopadolina@gmail.com', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for ALVIN DE LEON MANUEL (ID: 998987)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 08:26:34'),
(387, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L3110, Serial: X93P189494, Year: 1990. Assigned to employee ID 609250.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:27:28'),
(388, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: LENOVO, Serial: 1S65BAACC1WWU38DG787, Size: 18 inches, Year: 1990. Assigned to employee ID 839423.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:30:33'),
(389, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: LENOVO (Pre-Built), Serial: 8SSC80K13486F1WH63101D2, CPU: i3 - 4th Gen, RAM: 4GB DDR4, Storage: 1TB HDD, Year: 1990. Assigned to employee ID 839423.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:32:15'),
(390, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L3550, Serial: XBCF005397, Year: 1990. Assigned to employee ID 839423.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:32:48'),
(391, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 8CG833312P, CPU: i7 - 8th Gen, RAM: 4GB DDR4, Storage: 2TB HDD, Year: 2019. Assigned to employee ID 897577.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:34:59'),
(392, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3CM9040CB7, Size: 24 inches, Year: 2019. Assigned to employee ID 897577.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:36:03'),
(393, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L3550, Serial: XBCF003754, Year: 1990. Assigned to employee ID 897577.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:36:54'),
(394, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 8CG9081YP6, CPU: i7 - 8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2019. Assigned to employee ID 102892.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:38:59'),
(395, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3CM8450WTT, Size: 24 inches, Year: 2019. Assigned to employee ID 102892.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:39:43'),
(396, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L3550, Serial: XBCF005420, Year: 1990. Assigned to employee ID 102892.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:40:25'),
(397, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3CM9040C11, Size: 24 inches, Year: 1990. Assigned to employee ID 888190.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:43:14'),
(398, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 8CG9081YN2, CPU: i7 - 8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2019. Assigned to employee ID 888190.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:46:00'),
(399, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated Monitor (ID: 81) — Brand: HP, Serial: 3CM9040C11. Assigned to employee ID 888190.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:46:16'),
(400, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 08:46:44'),
(401, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — HP SMART TANK 515, Serial: CN32J440BT, Year: 1990. Assigned to employee ID 888190.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:47:23'),
(402, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: DTBGXSP00114206DEE9600, CPU: i5 - 10th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 1TB HDD, Year: 2022. Assigned to employee ID 156379.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:50:02'),
(403, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: ACER, Serial: MMTJDSS002136025593W01, Size: 21 inches, Year: 1990. Assigned to employee ID 156379.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:52:31'),
(404, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — CANON G3020, Serial: KMTK07285, Year: 1990. Assigned to employee ID 156379.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-02-27 08:53:54'),
(405, 2, 'superadmin', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-27 09:00:43'),
(406, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 00:29:52'),
(407, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '192.168.1.108', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 00:58:03'),
(408, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 01:00:09'),
(409, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:01:13'),
(410, 3, 'javs3116@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:05:44'),
(411, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 47) — Brand: ACER, Serial: DTB8ASP012812060413000. Assigned to employee ID 932573.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:10:16'),
(412, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3CM82707LR, Size: 24 inches, Year: 1990. Assigned to employee ID 932573.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:12:12'),
(413, 3, 'javs3116@gmail.com', 'CREATE', 'Printers', 'Added Printer — BROTHER DCP-T720DW, Serial: E80726M2H823122, Year: 1990. Assigned to employee ID 932573.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:14:30'),
(414, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated Monitor (ID: 83) — Brand: HP, Serial: 3CM82707LR. Assigned to employee ID 932573.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:15:02'),
(415, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 01:15:25'),
(416, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 45) — Brand: DELL, Serial: 3799MP2. Assigned to employee ID 170722.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:17:15'),
(417, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 46) — Brand: ACER AIO PC, Serial: DQB80SP00271904848300. Assigned to employee ID 907875.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:18:23'),
(418, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: CNK62306FS, Size: 21 inches, Year: 1990. Assigned to employee ID 170722.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:19:17'),
(419, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 44) — Brand: DELL AIO PC, Serial: GWJJDF2. Assigned to employee ID 858115.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:20:38'),
(420, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated Monitor (ID: 84) — Brand: HP, Serial: CNK62306FS. Assigned to employee ID 170722.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:21:12'),
(421, 3, 'javs3116@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 44) — Brand: DELL AIO PC, Serial: GWJJDF2. Assigned to employee ID 777388.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:21:16'),
(422, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON WF-C5790, Serial: X3BC005496, Year: 1990. Assigned to employee ID 777388.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:22:59'),
(423, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:25:44'),
(424, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee FILIPINA SOMBILLO (Employee ID: 907875, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:26:18'),
(425, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee LILIBETH SISON (Employee ID: 932573, Position: Accounting Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:26:47'),
(426, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee EDNA FERMIN (Employee ID: 777388, Position: Accountant III, Status: Job Order).', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:27:07'),
(427, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee DEBBIE ANNE DE LEON (Employee ID: 170722, Position: Procurement Analyst A, Status: Casual).', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:27:24'),
(428, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee DEBBIE ANNE DE LEON (Employee ID: 170722, Position: Procurement Analyst A, Status: Casual).', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:33:28'),
(429, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added All-in-One — Brand: DELL, Serial: 3DF6G92, CPU: i7 - 11 Gen, RAM: 8GB DDR4, Storage: 256 SSD / 1TB HDD. Assigned to employee ID 170722.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:36:09'),
(430, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 8CG9081YPM, CPU: i7-8th Gen, RAM: 4GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2019. Assigned to employee ID 902132.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:37:11'),
(431, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L3250, Serial: X8JZL72586, Year: 1990. Assigned to employee ID 170722.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:37:34'),
(432, 5, 'angelitopadolina@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 01:38:59'),
(433, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: VIEWSONIC, Serial: TSN1623E0148, Size: 21 INCHES, Year: 2017. Assigned to employee ID 902132.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 01:39:00'),
(434, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated All-in-One (ID: 19) — Brand: DELL, Serial: 3DF6G92. Assigned to employee ID 893880.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:39:29'),
(435, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Printers', 'Updated Printer (ID: 16) — EPSON L3250, Serial: X8JZL72586. Assigned to employee ID 893880.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 01:40:52'),
(436, 2, 'superadmin', 'CREATE', 'Computers', 'Added System Unit — Brand: test (Pre-Built), Serial: test, CPU: test, RAM: test, Storage: test, Year: 2002.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:16:50'),
(437, 2, 'superadmin', 'DELETE', 'Computers', 'Deleted System Unit (ID: 60) — Brand: test, Serial: test.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:17:00'),
(438, 2, 'superadmin', 'CREATE', 'Computers', 'Added System Unit — Brand: tets (Pre-Built), Serial: test, CPU: test, RAM: test, Storage: test, Year: 2002. Assigned to employee ID 902132.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:18:43'),
(439, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated System Unit (ID: 61) — Brand: tets, Serial: tests. Assigned to employee ID 902132.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:18:49'),
(440, 2, 'superadmin', 'DELETE', 'Computers', 'Deleted System Unit (ID: 61) — Brand: tets, Serial: tests.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:18:53'),
(441, 2, 'superadmin', 'CREATE', 'Maintenance', 'Created maintenance template \'ICT PREVENTIVE MAINTENANCE\' (ID: 1).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:19:30'),
(442, 2, 'superadmin', 'CREATE', 'Maintenance', 'Created maintenance template \'ICT PREVENTIVE MAINTENANCE\' (ID: 2).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:22:54'),
(443, 2, 'superadmin', 'DELETE', 'Maintenance', 'Soft-deleted maintenance template ID 2.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:36:42'),
(444, 2, 'superadmin', 'DELETE', 'Maintenance', 'Soft-deleted maintenance template ID 1.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:36:47'),
(445, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 03:38:31'),
(446, 2, 'superadmin', 'CREATE', 'Maintenance', 'Created maintenance template \'ICT PREVENTIVE MAINTENANCE\' (ID: 3).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 05:09:18'),
(447, 2, 'superadmin', 'DELETE', 'Maintenance', 'Soft-deleted maintenance template ID 3.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 05:09:23'),
(448, 5, 'angelitopadolina@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 05:32:09'),
(449, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 06:22:41'),
(450, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 06:27:10'),
(451, 2, 'superadmin', 'CREATE', 'Maintenance', 'Created maintenance template \'ICT PREVENTIVE MAINTENANCE\' (ID: 4).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 06:36:24'),
(452, 2, 'superadmin', 'DELETE', 'Maintenance', 'Soft-deleted maintenance template ID 4.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 06:36:37'),
(453, 5, 'angelitopadolina@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 13; RMX3081 Build/RKQ1.211119.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.79 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/548.0.0.37.65;]', 1, '2026-03-02 06:38:10'),
(454, 3, 'javs3116@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 07:00:54'),
(455, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 07:02:27'),
(456, 3, 'javs3116@gmail.com', 'CREATE', 'Printers', 'Added Printer — HP P1102, Serial: VNF5R48770, Year: 1998. Assigned to employee ID 902132.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 07:03:53'),
(457, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 07:05:34'),
(458, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee MELVIN SANTIAGO (Employee ID: 170456, Position: Principal Engineer C, Status: Permanent).', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 07:12:18'),
(459, 5, 'angelitopadolina@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 07:17:52'),
(460, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 8CG8413BH5, CPU: i7 - 8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2018.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 07:18:51'),
(461, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: HP, Serial: 3CM82706DQ, Size: 22inches, Year: 2018.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 07:20:48'),
(462, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L1210, Serial: X8LG004855, Year: 1990.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 07:22:25'),
(463, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: ACER (Pre-Built), Serial: DTBGXSP005128011AB9600, CPU: intel i7-11th Gen, RAM: 8GB RAM, Storage: 256GB SSD / 1TB HDD, Year: 2022. Assigned to employee ID 249874.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 07:23:48'),
(464, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: ACER, Serial: 416018832233, Size: 21 inches, Year: 2024. Assigned to employee ID 249874.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 07:27:58'),
(465, 3, 'javs3116@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L5290, Serial: X8H5383549, Year: 2025. Assigned to employee ID 249874.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 07:29:53'),
(466, 2, 'superadmin', 'CREATE', 'Maintenance', 'Created maintenance template \'ICT PREVENTIVE MAINTENANCE\' (ID: 5).', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 07:42:29'),
(467, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: AMD (Custom Built), Serial: M2A07200241, CPU: Ryzen 5 5600G, RAM: 16GB DDR4, Storage: 1TB SSD, Year: 2022. Assigned to employee ID 685880.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 07:48:20'),
(468, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: N-VISION, Serial: N240BCSJH23051120, Size: 24 inches, Year: 2023. Assigned to employee ID 685880.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 07:50:35'),
(469, 3, 'javs3116@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L14150, Serial: X6QU055644, Year: 2023. Assigned to employee ID 685880.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 07:52:52'),
(470, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 07:54:22'),
(471, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: suepradmin', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-02 07:55:09'),
(472, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: suepradmin', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-02 07:55:14'),
(473, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 07:55:26'),
(474, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated Monitor (ID: 90) — Brand: HP, Serial: 3CM82706F5. Assigned to employee ID 903089.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 07:57:48'),
(475, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: DELL (Pre-Built), Serial: 37L9MP2, CPU: i7-7th Gen, RAM: 16GB DDR4, Storage: 128GB SSB/1TB HDD, Year: 2018. Assigned to employee ID 573917.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 08:00:47'),
(476, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: DELL, Serial: CN04XPPCTV2007B6028BA01, Size: 24 inches, Year: 2018. Assigned to employee ID 573917.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 08:02:41'),
(477, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Employees', 'Added employee Deiciree Del Valle (Employee ID: 382312, Position: Community Relations Officer B, Status: Job Order).', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 08:02:54'),
(478, 3, 'javs3116@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L14150, Serial: X6QU032639, Year: 2024. Assigned to employee ID 573917.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 08:03:57'),
(479, 3, 'javs3116@gmail.com', 'UPDATE', 'Printers', 'Updated Printer (ID: 24) — EPSON L14150, Serial: X6QU032639. Assigned to employee ID 573917.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 08:04:27'),
(480, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 13; Infinix X6831 Build/TP1A.220624.014; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.79 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/548.0.0.37.65;]', 1, '2026-03-02 08:06:56'),
(481, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: javs3116@gmail.con', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 0, '2026-03-02 08:10:01'),
(482, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for unknown email: javs3116@gmail.con', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 0, '2026-03-02 08:10:07'),
(483, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: javs3116@gmail.com (wrong password)', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 0, '2026-03-02 08:10:19'),
(484, 3, 'javs3116@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 08:10:31'),
(485, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: superadmin (wrong password)', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-02 08:16:28'),
(486, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 08:16:40'),
(487, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 08:16:41'),
(488, 3, 'javs3116@gmail.com', 'CREATE', 'Computers', 'Added All-in-One — Brand: ASUS, Serial: CCAh18LP2340T5, CPU: i7-8th Gen, RAM: 16GB, Storage: 256 GB SSD/ 1TB HDD. Assigned to employee ID 890613.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 08:19:32'),
(489, 3, 'javs3116@gmail.com', 'CREATE', 'Printers', 'Added Printer — EPSON L1210, Serial: X8LG005598, Year: 2024. Assigned to employee ID 890613.', '::1', 'Mozilla/5.0 (iPad; CPU OS 26_3_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/145.0.7632.108 Mobile/15E148 Safari/604.1', 1, '2026-03-02 08:20:59'),
(490, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added System Unit — Brand: HP (Pre-Built), Serial: 3CM82706F5, CPU: i5 - 8th Gen, RAM: 8GB DDR4, Storage: 256GB SSD / 2TB HDD, Year: 2018. Assigned to employee ID 382312.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 08:23:55'),
(491, 4, 'ronieljjadee@gmail.com', 'CREATE', 'Computers', 'Added Monitor — Brand: SAMSUNG, Serial: ZZQSH4TJ902176R, Size: 21.5 inches, Year: 2018. Assigned to employee ID 382312.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-02 08:25:59'),
(492, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated security settings (5 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 08:53:05'),
(493, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 08:54:21'),
(494, 2, 'superadmin', 'CREATE', 'Maintenance', 'Batch initialized 2 maintenance schedules for General Services Security Unit  (location_id: 12). Frequency: Semi-Annual, Start: 2026-03-09. Skipped 4 (already scheduled). Total equipment: 6.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 08:56:19'),
(495, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-02 23:57:37'),
(496, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:29:43'),
(497, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee ALMA MANAOIS (Employee ID: 156379, Position: Senior Financial Planning Analyst, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:33:27'),
(498, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee ALMA MANAOIS (Employee ID: 156379, Position: Senior Financial Planning Analyst, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:33:28');
INSERT INTO `activity_log` (`id`, `user_id`, `email`, `action`, `module`, `description`, `ip_address`, `user_agent`, `success`, `timestamp`) VALUES
(499, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee ALMA MANAOIS (Employee ID: 156379, Position: Senior Financial Planning Analyst, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:33:29'),
(500, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee BENJ OLIVER CASTRO (Employee ID: 685880, Position: Community Relations Officer B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:34:25'),
(501, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee DEBBIE ANNE DE LEON (Employee ID: 170722, Position: Procurement Analyst A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:35:17'),
(502, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee DEICEREE DEL VALLE (Employee ID: 382312, Position: Community Relations Officer B, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:36:29'),
(503, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee EDNA FERMIN (Employee ID: 777388, Position: Accountant III, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:37:03'),
(504, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee ELLEN JANE SORIANO (Employee ID: 220749, Position: Chief Corporate Accountant B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:37:50'),
(505, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee FILIPINA SOMBILLO (Employee ID: 907875, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:38:39'),
(506, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee HONEY LEITH DELA CRUZ (Employee ID: 249874, Position: Secretary A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:39:23'),
(507, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee JANA TEE (Employee ID: 879471, Position: Senior Data Encoder Controller, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:40:11'),
(508, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee JING ALEXIS SANTIAGO (Employee ID: 911855, Position: Office Equipment Technician A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:41:05'),
(509, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee JOCELYN DELA CRUZ (Employee ID: 410918, Position: Accounting Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:41:47'),
(510, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-03 00:42:26'),
(511, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 67) — Brand: AMD, Serial: 2AN2A17703817. Assigned to employee ID 527628.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-03 00:44:26'),
(512, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated Monitor (ID: 90) — Brand: HP, Serial: 3CM82706F5. Assigned to employee ID 527628.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-03 00:44:49'),
(513, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated All-in-One (ID: 20) — Brand: ACER, Serial: DQBM4SP001524023563000. Assigned to employee ID 527628.', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', 1, '2026-03-03 00:45:13'),
(514, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: angelitopadolina@gmail.com (wrong password)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-03 00:46:40'),
(515, 5, 'angelitopadolina@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:46:44'),
(516, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee AIME MARIE JACINTO (Employee ID: 148826, Position: Senior Engineer A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:47:18'),
(517, 4, 'ronieljjadee@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:47:45'),
(518, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee ALBERTO LISING (Employee ID: 805509, Position: Driver Mechanic B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:47:59'),
(519, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated System Unit (ID: 67) — Brand: AMD, Serial: 2AN2A17703817. Assigned to employee ID 527628.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:48:29'),
(520, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee BERNAN ALEXIS DUCUSIN (Employee ID: 288636, Position: Senior Engineer A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:49:56'),
(521, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee ANGELICA MALLARI (Employee ID: 680891, Position: Data Encoder, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:51:40'),
(522, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee MERRY DAWN HONORIO (Employee ID: 890613, Position: Community Relations Chief B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:52:20'),
(523, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee ARMANDO TALPLACIDO (Employee ID: 144784, Position: Senior Draftsman, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:54:46'),
(524, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee JONALYN TAN (Employee ID: 389637, Position: Data Encoder, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:55:19'),
(525, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee JONALYN TAN (Employee ID: 389637, Position: Data Encoder, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:55:21'),
(526, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:56:09'),
(527, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee BENELYN RONQUILLO (Employee ID: 353441, Position: Clerk Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:56:14'),
(528, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee JONALYN TAN (Employee ID: 389637, Position: Data Encoder, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 00:58:06'),
(529, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee KRISSIALEN CALPITO (Employee ID: 888190, Position: Accounting Processor B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:00:04'),
(530, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee LILIBETH SISON (Employee ID: 932573, Position: Accounting Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:00:56'),
(531, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee LILIBETH SISON (Employee ID: 932573, Position: Accounting Processor A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:01:08'),
(532, 2, 'superadmin', 'UPDATE', 'Employees', 'Updated employee MYRENE MANIEGO (Employee ID: 839423, Position: Secretary B, Status: Job Order).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:04:02'),
(533, 2, 'superadmin', 'CREATE', 'Computers', 'Added System Unit — Brand: test, Serial: tets.', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:08:32'),
(534, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated System Unit (ID: 30027) — Brand: test, Serial: tets.', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:09:59'),
(535, 2, 'superadmin', 'CREATE', 'Employees', 'Added employee Mark Angelo Palacay (Employee ID: 46213, Position: OJT Trainee, Status: Permanent).', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:10:46'),
(536, 2, 'superadmin', 'DELETE', 'Computers', 'Deleted System Unit (ID: 30027).', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:14:36'),
(537, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee CARLO JAY CRUZ (Employee ID: 952072, Position: Driver Mechanic A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:30:52'),
(538, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee CATHERINE JOY QUINTAO (Employee ID: 115386, Position: Utility Worker A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:33:22'),
(539, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee CHERRY BAUTISTA (Employee ID: 205867, Position: Utility Worker A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:34:14'),
(540, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated System Unit (ID: 67) — Brand: AMD, Serial: 2AN2A17703817.', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:36:37'),
(541, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee DANIEL MACASAYA (Employee ID: 864059, Position: Automotive Mechanic C, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 01:37:59'),
(542, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee DANIEL JOSON (Employee ID: 464803, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:27:09'),
(543, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee SOLOMON AREJA (Employee ID: 417554, Position: Housekeeping Services Assistant, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:28:02'),
(544, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee SHERMAINE ATRAJE (Employee ID: 296288, Position: Procurement Assistant A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:28:42'),
(545, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee SHERMAINE ATRAJE (Employee ID: 296288, Position: Procurement Assistant A, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:28:45'),
(546, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee EDGARDO BAGUISA (Employee ID: 858115, Position: Heavy Equipment Operator, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:29:26'),
(547, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee JEAN AIRA BALAGTAS (Employee ID: 975919, Position: Office Equipment Technician B, Status: Casual).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:30:24'),
(548, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee JESSIE BAYLON (Employee ID: 994948, Position: Water Resources Facilities Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:32:09'),
(549, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee JEFFREY BUSTILLOS (Employee ID: 390778, Position: Electronics Communication System Operator B, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:33:49'),
(550, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee MERNAN BUSUEGO (Employee ID: 937830, Position: Utility Worker A, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:34:30'),
(551, 2, 'superadmin', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:41:26'),
(552, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Employees', 'Updated employee SHERILYN CAMACHO (Employee ID: 343449, Position: Data Encoder, Status: Permanent).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:41:58'),
(553, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:43:22'),
(554, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated Monitor (ID: 20090) — Brand: HP.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:43:39'),
(555, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated All-in-One (ID: 10020) — Brand: ACER.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:44:01'),
(556, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated Printer (ID: 30023) — Brand: EPSON.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:45:55'),
(557, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:46:08'),
(558, 3, 'javs3116@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:46:24'),
(559, 2, 'superadmin', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:46:31'),
(560, 4, 'ronieljjadee@gmail.com', 'UPDATE', 'Computers', 'Updated Printer (ID: 30021) — Brand: EPSON.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:46:35'),
(561, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:47:49'),
(562, 5, 'angelitopadolina@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:50:46'),
(563, 2, 'superadmin', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (March 2026)', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:55:49'),
(564, 2, 'superadmin', 'CREATE', 'Computers', 'Added System Unit — Brand: test, Serial: test.', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:56:41'),
(565, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated System Unit (ID: 30028) — Brand: test, Serial: test.', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:56:58'),
(566, 2, 'superadmin', 'UPDATE', 'Computers', 'Updated System Unit (ID: 30028) — Brand: test, Serial: test.', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:58:18'),
(567, 2, 'superadmin', 'DELETE', 'Computers', 'Deleted System Unit (ID: 30028).', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 02:58:45'),
(568, 2, 'superadmin', 'CREATE', 'Other Equipment', 'Added test — test test, Serial: test, Status: Available, Year: .', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 03:05:21'),
(569, 2, 'superadmin', 'DELETE', 'Other Equipment', 'Deleted test (ID: 30029) — test test, Serial: test.', '192.168.1.84', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 03:05:39'),
(570, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '100.100.96.82', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-03-03 03:30:35'),
(571, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '192.168.1.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 03:52:06'),
(572, 2, 'superadmin', 'LOGOUT', 'Authentication', 'User logged out', '192.168.1.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 03:52:12'),
(573, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: superadmin (wrong password)', '192.168.1.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-03 03:53:51'),
(574, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '192.168.1.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 03:53:56'),
(575, 2, 'superadmin', 'LOGOUT', 'Authentication', 'User logged out', '192.168.1.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 03:54:20'),
(576, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: superadmin (wrong password)', '192.168.1.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-03-03 04:01:44'),
(577, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '192.168.1.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 04:01:50'),
(578, 2, 'superadmin', 'LOGOUT', 'Authentication', 'User logged out', '192.168.1.162', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 04:02:03'),
(579, 5, 'angelitopadolina@gmail.com', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for ALMA DE LEON MANAOIS (ID: 156379)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 04:41:44'),
(580, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 05:40:06'),
(581, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated security settings (5 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 06:55:23'),
(582, 2, 'superadmin', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 06:56:08'),
(583, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 06:56:16'),
(584, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 07:25:40'),
(585, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated security settings (5 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 07:25:52'),
(586, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated organization settings (5 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 08:03:55'),
(587, 2, 'superadmin', 'UPDATE', 'Settings', 'Updated organization settings (5 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-03 08:04:11'),
(588, 2, 'superadmin', 'LOGIN', 'Authentication', 'User logged in successfully', '100.80.183.93', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-04 02:45:26');

-- --------------------------------------------------------

--
-- Table structure for table `data_change_tracker`
--

CREATE TABLE `data_change_tracker` (
  `category` varchar(50) NOT NULL,
  `updated_at` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `data_change_tracker`
--

INSERT INTO `data_change_tracker` (`category`, `updated_at`) VALUES
('accounts', '2026-02-27 13:49:38.597'),
('authentication', '2026-03-04 10:45:26.754'),
('employees', '2026-03-03 10:41:58.322'),
('equipment', '2026-03-03 11:05:39.915'),
('maintenance', '2026-03-02 16:56:19.866'),
('organization', '2026-02-27 13:49:38.597'),
('reports', '2026-03-03 12:41:44.168'),
('settings', '2026-03-03 16:04:11.172'),
('software', '2026-02-27 13:49:38.597');

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

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip_address`, `user_agent`, `attempt_time`, `success`) VALUES
(4, 'inventory@upriis.local', '175.158.198.114', NULL, '2026-02-26 05:44:10', 0),
(5, 'inventory@upriis.local', '175.158.198.114', NULL, '2026-02-26 05:44:20', 0),
(7, 'lexternmanuel@gmail.com', '175.158.198.114', NULL, '2026-02-26 05:47:50', 0),
(8, 'lexternmanuel@gmail.com', '175.158.198.114', NULL, '2026-02-26 05:47:59', 0),
(9, 'lexternmanuel@gmail.com', '175.158.198.114', NULL, '2026-02-26 05:48:11', 0),
(10, 'lextermanuel@gmail.com', '175.158.198.114', NULL, '2026-02-26 05:48:25', 0);

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
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(64) NOT NULL,
  `hashed_validator` varchar(128) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
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
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `label` varchar(200) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`, `label`, `description`, `updated_at`, `updated_by`) VALUES
('backup_retention_days', '30', 'system', 'Log Retention (days)', 'Days to retain activity logs before cleanup', '2026-02-27 07:53:39', 2),
('date_format', 'M d, Y', 'system', 'Date Display Format', 'PHP date format for display', '2026-02-27 07:53:39', 2),
('enable_activity_log', '1', 'system', 'Enable Activity Logging', 'Log user actions in the system', '2026-02-27 07:53:39', 2),
('enforce_2fa', '0', 'security', 'Enforce 2FA', 'Require two-factor authentication for all users', '2026-03-03 07:25:52', 2),
('items_per_page', '25', 'system', 'Default Items Per Page', 'Default pagination size', '2026-02-27 07:53:39', 2),
('lockout_duration', '900', 'security', 'Lockout Duration (seconds)', 'How long account stays locked', '2026-03-03 07:25:52', 2),
('maint_auto_schedule', '1', 'maintenance', 'Auto-Schedule Next', 'Automatically create next schedule after completion', NULL, NULL),
('maint_default_frequency', 'quarterly', 'maintenance', 'Default Frequency', 'Default maintenance schedule frequency', NULL, NULL),
('maint_overdue_threshold_days', '7', 'maintenance', 'Overdue Threshold (days)', 'Days past due before flagged overdue', NULL, NULL),
('maint_reminder_days_before', '7', 'maintenance', 'Reminder Lead Days', 'Days before due date to show reminders', NULL, NULL),
('max_login_attempts', '5', 'security', 'Max Login Attempts', 'Failed attempts before lockout', '2026-03-03 07:25:52', 2),
('org_address', '', 'organization', 'Office Address', 'Physical address of the office', '2026-03-03 08:04:11', 2),
('org_contact_email', '', 'organization', 'Contact Email', 'Primary contact email', '2026-03-03 08:04:11', 2),
('org_contact_phone', '', 'organization', 'Contact Phone', 'Primary contact phone number', '2026-03-03 08:04:11', 2),
('org_name', 'NIA UPRIIS', 'organization', 'Organization Name', 'Full name of the organization', '2026-03-03 08:04:11', 2),
('org_short_name', 'UPRIIS', 'organization', 'Short Name / Acronym', 'Abbreviated name used in headers', '2026-03-03 08:04:11', 2),
('password_min_length', '8', 'security', 'Minimum Password Length', 'Minimum characters for passwords', '2026-03-03 07:25:52', 2),
('session_timeout', '86400', 'security', 'Session Timeout (seconds)', 'Auto-logout after inactivity', '2026-03-03 07:25:52', 2);

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
(2, 'SystemSuperAdmin', 'superadmin', '$2y$12$neRwNSt0OLjeyWxaZgNyy.DvChBeoxmPKZkR4vlfDcgfXIv1I.VeC', 'Super Admin', 'Active', 0, NULL, '2026-03-04 10:45:26', '100.80.183.93', 0, NULL, '2026-02-25 20:57:14', '2026-03-04 02:45:26', NULL),
(3, 'Jing Alexis Santiago', 'javs3116@gmail.com', '$2y$12$c5HeOb0o7u9hP3NLmQ57qOFOfGKtGiC5tpe1IccKowTMKazI25u5.', 'Admin', 'Active', 0, NULL, '2026-03-03 10:46:24', '180.191.20.238', 0, NULL, '2026-02-26 05:45:20', '2026-03-03 02:46:24', 2),
(4, 'Roniel Jade Verdadero', 'ronieljjadee@gmail.com', '$2y$12$/TGTbZmWhNbLy4TajNPSC.lBwvjZLj0OYV0e7kKQoTOxeZjqFB9IG', 'Admin', 'Active', 0, NULL, '2026-03-03 08:47:45', '180.191.20.238', 0, NULL, '2026-02-26 05:47:18', '2026-03-03 00:47:45', 2),
(5, 'Angelito Padolina', 'angelitopadolina@gmail.com', '$2y$12$O5bfraJy8HDDaws7AOS2oOTcSoir0yLD2EgnX3ymV//nTVThjwm8C', 'Admin', 'Active', 0, NULL, '2026-03-03 10:50:46', '180.191.20.238', 0, NULL, '2026-02-26 06:57:59', '2026-03-03 02:50:46', 2),
(6, 'Lexter N. Manuel', 'lextermanuel@gmail.com', '$2y$12$P16AKb66Omo/o7I7KRWXFOT3hkCx97.jOxmVSEkCy7qjQJ9aL77vq', 'Admin', 'Active', 0, NULL, '2026-02-27 13:25:09', '103.144.157.168', 0, NULL, '2026-02-27 05:24:49', '2026-02-27 05:25:09', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_activity_logs`
--

CREATE TABLE `tbl_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_allinone`
--

CREATE TABLE `tbl_allinone` (
  `allinoneId` int(11) NOT NULL,
  `allinoneBrand` varchar(100) NOT NULL,
  `allinoneSerial` varchar(100) DEFAULT NULL,
  `specificationProcessor` varchar(255) NOT NULL,
  `specificationMemory` varchar(255) NOT NULL,
  `specificationGPU` varchar(255) NOT NULL,
  `specificationStorage` varchar(255) NOT NULL,
  `yearAcquired` year(4) NOT NULL,
  `employeeId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_allinone`
--

INSERT INTO `tbl_allinone` (`allinoneId`, `allinoneBrand`, `allinoneSerial`, `specificationProcessor`, `specificationMemory`, `specificationGPU`, `specificationStorage`, `yearAcquired`, `employeeId`) VALUES
(1, 'ACER AIO PC', '0046', 'i3 - 7th Gen', '4GB DDR4', 'Integrated GPU', '1TB HDD', '2018', NULL),
(2, 'DELL AIO PC', '0044', 'i7 - 11th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '2020', NULL),
(3, 'DELL AIO PC', '0042', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '2020', NULL),
(4, 'DELL AIO PC', '0039', 'i7 - 11th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '2020', NULL),
(5, 'HP AIO PC', '0038', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '2019', NULL),
(6, 'DELL AIO PC', '0034', 'i7 - 11th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '2020', NULL),
(7, 'HP AIO PC', '0028', 'i3 - 7th Gen', '4GB DDR4', 'Integrated GPU', '512GB SSD', '2017', NULL),
(8, 'HP AIO PC', '0025', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '2019', NULL),
(9, 'ASUS AIO PC', '0024', 'i5- 8th Gen', 'i5- 8th Gen', 'Integrated GPU', '256GB SSD', '2018', NULL),
(10, 'HP AIO PC', '0012', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '2019', NULL),
(11, 'DELL AIO PC', '0008', 'i5- 11th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '2020', NULL),
(12, 'ASUS AIO PC', '0004', 'i7-8th Gen', '16GB DDR4', 'GTX 1050', '256GB SSD / 1TB HDD', '2019', NULL),
(13, 'ACER AIO PC', '0003', 'i5- 8th Gen', '4GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '2020', NULL),
(15, 'Lenovo', 'MF1TL1LG', 'i3 10th Gen', '4GB', 'Integrated', '1TB HDD', '1990', 962770),
(16, 'ACER', 'DQBBUSP0039470692A3000', 'i5 8th Gen', '4GB DDR4', 'Integrated', '256 SSD / 1TB HDD', '2020', 220749),
(17, 'HP', '8CC4080CJR', 'Ultra 5 125U', '16GB DDR5', 'Integrated', '512GB SSD', '2024', 667663),
(18, 'HP', '8CC72316NP', 'i3 7th Gen', '4GB DDR4', 'Integrated', '1TB HDD', '2017', 389637),
(19, 'DELL', '3DF6G92', 'i7 - 11 Gen', '8GB DDR4', 'MX330', '256 SSD / 1TB HDD', '2021', 893880),
(20, 'ACER', 'DQBM4SP001524023563000', 'Ultra 5 125U', '8gb', 'Integrated', '512 SSD', '1990', 527628),
(21, 'ASUS', 'CCAh18LP2340T5', 'i7-8th Gen', '16GB', 'GTX 1050', '256 GB SSD/ 1TB HDD', '2019', 890613);

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

--
-- Dumping data for table `tbl_checklist_category`
--

INSERT INTO `tbl_checklist_category` (`categoryId`, `templateId`, `categoryName`, `sequenceOrder`) VALUES
(1, 1, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(2, 1, 'II. HARDWARE PERFORMANCE CHECK', 2),
(3, 1, 'Untitled', 3),
(4, 2, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(5, 2, 'II. HARDWARE PERFORMANCE CHECK', 2),
(6, 2, 'Untitled', 3),
(7, 3, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(8, 3, 'II. HARDWARE PERFORMANCE CHECK', 2),
(9, 3, 'Untitled', 3),
(10, 4, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(11, 4, 'II. HARDWARE PERFORMANCE CHECK', 2),
(12, 4, 'Untitled', 3),
(13, 5, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(14, 5, 'II. HARDWARE PERFORMANCE CHECK', 2),
(15, 5, 'Untitled', 3);

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

--
-- Dumping data for table `tbl_checklist_item`
--

INSERT INTO `tbl_checklist_item` (`itemId`, `categoryId`, `taskDescription`, `sequenceOrder`) VALUES
(1, 1, 'Dust removal performed', 1),
(2, 1, 'Parts are intact', 2),
(3, 2, 'Power Supply is working properly', 1),
(4, 4, 'Dust removal performed', 1),
(5, 4, 'Parts are intact', 2),
(6, 5, 'Power Supply is working properly', 1),
(7, 7, 'Dust removal performed', 1),
(8, 7, 'Parts are intact', 2),
(9, 8, 'Power Supply is working properly', 1),
(10, 10, 'Dust removal performed', 1),
(11, 10, 'Parts are intact', 2),
(12, 11, 'Power Supply is working properly', 1),
(13, 13, 'Dust removal performed', 1),
(14, 13, 'Parts are intact', 2),
(15, 14, 'Power Supply is working properly', 1);

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
  `is_archive` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_employee`
--

INSERT INTO `tbl_employee` (`employeeId`, `firstName`, `middleName`, `lastName`, `suffixName`, `position`, `birthDate`, `sex`, `employmentStatus`, `photoPath`, `location_id`, `createdAt`, `updatedAt`, `is_archive`) VALUES
(46213, 'Mark Angelo', NULL, 'Palacay', '', 'OJT Trainee', '2000-11-11', 'Male', 'Permanent', NULL, 4, '2026-03-03 01:10:46', '2026-03-03 01:10:46', 0),
(100752, 'ANGELO BRANDO', 'DAYAG', 'CALLEJO', '', 'Utility Worker A', '1982-07-14', 'Male', 'Job Order', NULL, 9, '2026-02-27 01:18:56', '2026-02-27 01:18:56', 0),
(102892, 'PATRICIA CARRIELIN', 'MARIANO', 'PEREZ', '', '-', '2001-06-22', 'Female', 'Job Order', NULL, 10, '2026-02-27 05:05:13', '2026-02-27 05:05:13', 0),
(104074, 'MARIA KRISTINA', 'CAYANGA', 'REYES', '', 'Nurse I', '1986-03-23', 'Female', 'Casual', NULL, 9, '2026-02-26 08:37:51', '2026-02-26 08:37:51', 0),
(108341, 'SHIELA', 'DUNGAO', 'PERALTA', '', 'Clerk Processor A', '1997-12-04', 'Female', 'Casual', NULL, 21, '2026-02-27 01:01:17', '2026-02-27 01:01:17', 0),
(115386, 'CATHERINE JOY', 'SANTIAGO', 'QUINTAO', NULL, 'Utility Worker A', '1997-02-02', 'Female', 'Casual', 'employee_115386_1772501602.jpeg', 9, '2026-02-26 08:42:26', '2026-03-03 01:33:22', 0),
(127188, 'KARYL LANE', 'BONZO', 'MARTINEZ', '', 'Accounting Processor A', '1993-06-03', 'Female', 'Permanent', NULL, 9, '2026-02-26 07:23:29', '2026-02-26 07:23:29', 0),
(129437, 'ALMA', 'ALVAREZ', 'OCAMPO', NULL, 'Household Attendant III', '1976-09-14', 'Female', 'Casual', NULL, 12, '2026-02-26 08:36:22', '2026-02-27 06:35:18', 0),
(130281, 'MICHAEL JOHN', 'DEL MUNDO', 'SANTOS', '', 'Senior Artist Illustrator', '1989-05-14', 'Male', 'Casual', NULL, 34, '2026-02-26 08:21:03', '2026-02-26 08:21:03', 0),
(144784, 'ARMANDO', 'ALFARO', 'TALPLACIDO', NULL, 'Senior Draftsman', '1962-02-06', 'Male', 'Permanent', 'employee_144784_1772499286.jpeg', 19, '2026-02-26 07:52:40', '2026-03-03 00:54:46', 0),
(148826, 'AIME MARIE', 'NICOLAS', 'JACINTO', NULL, 'Senior Engineer A', '1975-11-14', 'Female', 'Permanent', 'employee_148826_1772498838.jpeg', 18, '2026-02-26 07:47:39', '2026-03-03 00:47:18', 0),
(154219, 'ELEONOR', 'ARENAS', 'NOCUM', '', 'Industrial Relations Management/ Development Officer A', '2000-10-15', 'Female', 'Casual', NULL, 34, '2026-02-26 08:18:46', '2026-02-26 08:18:46', 0),
(156379, 'ALMA', 'DE LEON', 'MANAOIS', NULL, 'Senior Financial Planning Analyst', '1978-02-27', 'Female', 'Permanent', 'employee_156379_1772498009.jpeg', 10, '2026-02-26 07:40:55', '2026-03-03 00:33:29', 0),
(157351, 'CRESTA-LEE', 'CABIGAO', 'CASTILLO', '', 'Procurement Analyst A', '1986-07-27', 'Female', 'Casual', NULL, 9, '2026-02-26 08:28:15', '2026-02-26 08:28:15', 0),
(158835, 'MICHAEL', 'VILLAFLORES', 'MARQUEZ', '', 'Chef', '1977-10-06', 'Male', 'Casual', NULL, 9, '2026-02-27 00:23:57', '2026-02-27 00:23:57', 0),
(170456, 'MELVIN', 'MENDOZA', 'SANTIAGO', NULL, 'Principal Engineer C', '1975-02-08', 'Male', 'Permanent', NULL, 7, '2026-02-26 07:50:19', '2026-03-02 07:12:18', 0),
(170722, 'DEBBIE ANNE', 'COMA', 'DE LEON', NULL, 'Procurement Analyst A', '1975-09-28', 'Female', 'Casual', 'employee_170722_1772498117.jpeg', 7, '2026-02-26 08:33:07', '2026-03-03 00:35:17', 0),
(174096, 'RODEL', 'DELA MERCED', 'RUBI', '', 'Industrial Security Guard A', '1967-07-03', 'Male', 'Permanent', NULL, 24, '2026-02-26 07:35:01', '2026-02-26 07:35:01', 0),
(190331, 'INOCENCIO', 'REYES', 'SOMERA', 'Jr.', 'Senior Automotive Mechanic', '1970-08-20', 'Male', 'Permanent', NULL, 20, '2026-02-26 08:11:06', '2026-02-26 08:11:06', 0),
(190650, 'MANILYN', 'BREIS', 'MACAPAGAL', '', 'Office Equipment Technician B', '1990-01-11', 'Female', 'Casual', NULL, 9, '2026-02-26 08:45:48', '2026-02-26 08:45:48', 0),
(201374, 'MARK', 'VALDEZ', 'PAJARILLAGA', '', 'Store Keeper B', '1992-01-04', 'Male', 'Permanent', NULL, 24, '2026-02-26 07:27:26', '2026-02-26 07:27:26', 0),
(205867, 'CHERRY', 'BARTIDO', 'BAUTISTA', NULL, 'Utility Worker A', '1977-05-29', 'Female', 'Permanent', 'employee_205867_1772501654.jpeg', 24, '2026-02-26 07:30:00', '2026-03-03 01:34:14', 0),
(206693, 'BENJAMIN', 'ORMACIDO', 'ABAD', 'Jr.', 'Utility Worker A', '1969-10-27', 'Male', 'Casual', NULL, 9, '2026-02-27 00:20:34', '2026-02-27 00:20:34', 0),
(220749, 'ELLEN JANE', 'SANTOS', 'SORIANO', NULL, 'Chief Corporate Accountant B', '1993-09-06', 'Female', 'Permanent', 'employee_220749_1772498270.jpeg', 10, '2026-02-26 07:38:54', '2026-03-03 00:37:50', 0),
(221480, 'MARYJANE', 'MADRID', 'CALLANTA', '', 'Electronics Communication System Operator B', '1970-08-04', 'Female', 'Permanent', NULL, 19, '2026-02-26 07:53:38', '2026-02-26 07:53:38', 0),
(221985, 'LEIFE', 'BARTIDO', 'VILLAFLOR', '', 'Principal Engineer C', '1972-12-07', 'Male', 'Permanent', NULL, 20, '2026-02-26 08:03:49', '2026-02-26 08:03:49', 0),
(234567, 'MARK JOSEPH', 'DELOS SANTOS', 'PERIA', '', 'Utility Worker A', '1995-06-15', 'Male', 'Job Order', NULL, 9, '2026-02-27 01:18:00', '2026-02-27 01:18:00', 0),
(236784, 'Angelito', 'M.', 'Padolina', '', 'OJT Trainee', '2004-02-26', 'Male', 'Permanent', NULL, 14, '2026-02-26 08:59:08', '2026-02-26 08:59:08', 0),
(236943, 'CHRISTOPHER', 'NAGAÑO', 'CAÑON', '', 'Industrial Security Guard A', '1975-12-11', 'Male', 'Permanent', NULL, 24, '2026-02-26 07:34:18', '2026-02-26 07:34:18', 0),
(249441, 'PINKY', 'BARBACENA', 'SANTOS', '', 'Utility Worker A', '1970-03-24', 'Female', 'Casual', NULL, 9, '2026-02-26 08:43:01', '2026-02-26 08:43:01', 0),
(249874, 'HONEY LEITH', 'ALVAREZ', 'DELA CRUZ', NULL, 'Secretary A', '1994-03-20', 'Female', 'Casual', 'employee_249874_1772498363.jpeg', 21, '2026-02-27 01:00:42', '2026-03-03 00:39:23', 0),
(252229, 'MARC LOUISE', 'ADRINEDA', 'DE GUZMAN', '', 'Clerk Processor B', '2000-07-18', 'Male', 'Casual', NULL, 9, '2026-02-27 01:10:56', '2026-02-27 01:10:56', 0),
(256056, 'BARRY JOSE', 'CAMACHO', 'ORTIZ', 'III', 'Industrial Security Guard A', '1975-12-13', 'Male', 'Permanent', NULL, 24, '2026-02-26 07:33:30', '2026-02-26 07:33:30', 0),
(257989, 'CHRISTIAN', 'GABRIEL', 'AUSTRIA', '', 'Assistant Cook', '2000-09-16', 'Male', 'Job Order', NULL, 9, '2026-02-27 01:15:16', '2026-02-27 01:15:16', 0),
(261775, 'JEAN CAYLA', 'DOMINGO', 'SANTOS', '', 'Corporate Accounts Analyst', '1995-11-26', 'Female', 'Casual', NULL, 10, '2026-02-26 07:41:41', '2026-02-26 07:41:41', 0),
(263618, 'RAPHAEL', 'VESTIDAS', 'MARIANO', '', 'Data Encoder', '1993-11-04', 'Male', 'Job Order', NULL, 19, '2026-02-27 05:12:20', '2026-02-27 05:12:20', 0),
(265374, 'ROEL', 'BAGAN', 'VERAYO', '', 'Industrial Security Guard A', '1986-05-11', 'Male', 'Casual', NULL, 9, '2026-02-26 08:39:06', '2026-02-26 08:39:06', 0),
(268060, 'VERONICA ANN', 'COMA', 'LIWAG', '', 'Secretary B', '2000-06-27', 'Female', 'Job Order', NULL, 18, '2026-02-27 05:11:32', '2026-02-27 05:11:32', 0),
(269219, 'DAISYROSE', 'SORIANO', 'HIPOLITO', NULL, 'Accounting Processor A', '2001-03-10', 'Female', 'Job Order', NULL, 10, '2026-02-27 05:05:53', '2026-02-27 07:56:42', 0),
(276120, 'ERICKSON', 'DEL ROSARIO', 'SAN GABRIEL', '', 'Industrial Security Guard A', '1980-05-05', 'Male', 'Casual', NULL, 9, '2026-02-26 08:39:42', '2026-02-26 08:39:42', 0),
(284449, 'JOSEPH PAOLO', 'ABRATIGUE', 'GERONIMO', '', 'Senior Engineer A', '1998-02-09', 'Male', 'Casual', NULL, 18, '2026-02-27 00:31:28', '2026-02-27 00:31:28', 0),
(285419, 'VIVENCIA', 'CASTILLO', 'DELA CRUZ', NULL, 'Division Manager A', '1961-01-04', 'Female', 'Permanent', NULL, 18, '2026-02-26 07:00:34', '2026-02-26 07:11:41', 0),
(285583, 'MICHAEL JEROME', 'TORRES', 'TORRES', '', 'Engineer A', '1995-10-01', 'Male', 'Permanent', NULL, 20, '2026-02-26 08:05:09', '2026-02-26 08:05:09', 0),
(288636, 'BERNAN ALEXIS', 'PALOMO', 'DUCUSIN', NULL, 'Senior Engineer A', '1995-11-29', 'Male', 'Casual', 'employee_288636_1772498996.jpeg', 18, '2026-02-27 00:31:59', '2026-03-03 00:49:56', 0),
(290868, 'ROSE ANN', 'SANGIL', 'IGNACIO', '', 'Attorney IV', '1986-08-18', 'Female', 'Casual', NULL, 6, '2026-02-26 08:22:37', '2026-02-26 08:22:37', 0),
(296288, 'SHERMAINE', 'MARTINEZ', 'ATRAJE', NULL, 'Procurement Assistant A', '1993-08-14', 'Female', 'Casual', 'employee_296288_1772504925.jpeg', 10, '2026-02-26 08:49:50', '2026-03-03 02:28:45', 0),
(302215, 'KENNETH', 'MAGTALAS', 'TOLENTINO', '', 'Data Encoder', '2001-11-28', 'Male', 'Job Order', NULL, 19, '2026-02-27 05:13:13', '2026-02-27 05:13:13', 0),
(304314, 'MICOLE BRYLLE', 'LAJOM', 'VILLANUEVA', '', 'Data Encoder', '2001-09-28', 'Male', 'Casual', NULL, 9, '2026-02-26 08:32:12', '2026-02-26 08:32:12', 0),
(307027, 'ROMEO EMERALD', 'GARCIA', 'PLAZA', '', 'Airconditioning Technician I', '1988-06-18', 'Male', 'Casual', NULL, 9, '2026-02-26 08:45:04', '2026-02-26 08:45:04', 0),
(313227, 'MARVIN', 'GARCIA', 'PURIFICACION', '', 'Legal Researcher III', '1987-07-27', 'Male', 'Casual', NULL, 6, '2026-02-26 08:23:23', '2026-02-26 08:23:23', 0),
(321110, 'MARIA ISOBEL', 'FERMIN', 'PADOLINA', '', 'Administrative Services Chief A', '1979-02-13', 'Female', 'Permanent', NULL, 9, '2026-02-26 07:22:30', '2026-02-26 07:22:30', 0),
(329651, 'ORLY', 'LABUCAY', 'BENEMERITO', '', 'Water Resources Facilities Operator B', '1984-07-31', 'Male', 'Permanent', NULL, 19, '2026-02-26 07:59:53', '2026-02-26 07:59:53', 0),
(329759, 'REYNALDO', 'ESCANO', 'BANEZ', 'Jr.', 'Hotel Operations Officer B', '1997-11-27', 'Male', 'Casual', NULL, 9, '2026-02-27 00:19:59', '2026-02-27 00:19:59', 0),
(333908, 'MON KEVIN', 'RODRIGUEZ', 'FLORES', '', 'Engineer B', '1993-11-20', 'Male', 'Casual', NULL, 19, '2026-02-27 00:32:56', '2026-02-27 00:32:56', 0),
(339003, 'JERICO LOUIS', 'CRUZ', 'MANGAHAS', '', 'Clerk Processor B', '1997-07-05', 'Male', 'Casual', NULL, 34, '2026-02-27 01:09:25', '2026-02-27 01:09:25', 0),
(343449, 'SHERILYN', 'ANCHETA', 'CAMACHO', NULL, 'Data Encoder', '1990-06-16', 'Female', 'Permanent', 'employee_343449_1772505718.jpeg', 9, '2026-02-26 07:25:06', '2026-03-03 02:41:58', 0),
(353441, 'BENELYN', 'TIMBANG', 'RONQUILLO', NULL, 'Clerk Processor A', '1973-02-17', 'Female', 'Casual', 'employee_353441_1772499374.jpeg', 18, '2026-02-27 00:27:43', '2026-03-03 00:56:14', 0),
(361044, 'NAPOLEON', 'ALFONSO', 'PASCUAL', '', 'Computer Services Programmer B', '1980-06-06', 'Male', 'Casual', NULL, 9, '2026-02-26 08:29:53', '2026-02-26 08:29:53', 0),
(367049, 'FLAVIANO', 'JUAN', 'BAGUISA', 'Jr.', 'Welder A', '1973-05-22', 'Male', 'Permanent', NULL, 20, '2026-02-26 08:12:29', '2026-02-26 08:12:29', 0),
(369176, 'GARY LEO', 'DOMINGO', 'SANTURAY', '', 'Driver Mechanic B', '1979-03-22', 'Male', 'Permanent', NULL, 20, '2026-02-26 08:06:43', '2026-02-26 08:06:43', 0),
(376614, 'NIMROD', 'JACINTO', 'RUFINO', '', 'Driver Mechanic A', '1970-11-25', 'Male', 'Casual', NULL, 20, '2026-02-26 08:55:21', '2026-02-26 08:55:21', 0),
(377264, 'RAUL', 'DE GUZMAN', 'PLACIDO', '', 'Watchman III', '1968-08-08', 'Male', 'Casual', NULL, 9, '2026-02-27 00:18:17', '2026-02-27 00:18:17', 0),
(382312, 'DEICEREE', 'HONDRADO', 'DEL VALLE', NULL, 'Community Relations Officer B', '2001-10-06', 'Female', 'Job Order', 'employee_382312_1772498189.jpeg', 21, '2026-03-02 08:02:54', '2026-03-03 00:36:29', 0),
(383802, 'FRANCIA', 'BUENDIA', 'MACASAYA', '', 'Utility Worker A', '1963-09-18', 'Female', 'Casual', NULL, 9, '2026-02-26 08:43:34', '2026-02-26 08:43:34', 0),
(386196, 'MICHAEL', 'SAULO', 'REYES', '', 'Water Resources Facilities Operator B', '1979-11-12', 'Male', 'Permanent', NULL, 19, '2026-02-26 07:57:39', '2026-02-26 07:57:39', 0),
(389637, 'JONALYN', 'BERMUDEZ', 'TAN', NULL, 'Data Encoder', '1988-07-21', 'Female', 'Casual', 'employee_389637_1772499486.jpeg', 10, '2026-02-26 08:50:22', '2026-03-03 00:58:06', 0),
(390778, 'JEFFREY', 'PESTANO', 'BUSTILLOS', NULL, 'Electronics Communication System Operator B', '1984-12-26', 'Male', 'Permanent', 'employee_390778_1772505229.jpeg', 19, '2026-02-26 08:01:51', '2026-03-03 02:33:49', 0),
(393709, 'EMILY', 'CAPUYON', 'FRANCISCO', '', 'Irrigators Development Officer A', '1979-09-12', 'Female', 'Permanent', NULL, 21, '2026-02-26 08:17:48', '2026-02-26 08:17:48', 0),
(396602, 'CHRISTIAN GYVER', 'BANIAGA', 'RED', '', 'Supply Officer III', '1991-06-13', 'Male', 'Casual', NULL, 34, '2026-02-26 08:26:52', '2026-02-26 08:26:52', 0),
(408115, 'JOHN ARIEL ARISTOTLE', 'MARTIN', 'MENDOZA', '', 'Senior Engineer A', '1994-07-23', 'Male', 'Casual', NULL, 18, '2026-02-26 08:53:52', '2026-02-26 08:53:52', 0),
(408994, 'KATE', 'ZAFRA', 'DAGAWIN', '', 'Information Officer C', '1999-09-09', 'Female', 'Casual', NULL, 34, '2026-02-27 00:09:14', '2026-02-27 00:09:14', 0),
(410918, 'JOCELYN', 'MIRANDA', 'DELA CRUZ', NULL, 'Accounting Processor A', '1982-06-23', 'Female', 'Casual', 'employee_410918_1772498507.jpeg', 10, '2026-02-26 08:48:50', '2026-03-03 00:41:47', 0),
(411863, 'NOEL JEROME', 'PALAD', 'PAPA', '', 'Senior Computer Operator', '1992-11-08', 'Male', 'Casual', NULL, 34, '2026-02-26 08:20:23', '2026-02-26 08:20:23', 0),
(417554, 'SOLOMON', 'SALONGA', 'AREJA', NULL, 'Housekeeping Services Assistant', '1989-02-19', 'Male', 'Casual', 'employee_417554_1772504882.jpeg', 12, '2026-02-26 08:37:11', '2026-03-03 02:28:02', 0),
(424481, 'ROCHELLE ANGELA', 'SABLAY', 'RELUSCO', '', 'Clerk Processor A', '2000-06-26', 'Female', 'Job Order', NULL, 9, '2026-02-27 01:10:11', '2026-02-27 01:10:11', 0),
(451973, 'GRACE', 'LIBUNAO', 'BADILLA', '', 'Supervising Engineer A', '1984-10-08', 'Female', 'Permanent', NULL, 18, '2026-02-26 07:44:14', '2026-02-26 07:44:14', 0),
(463292, 'VIRGILIO', 'JACINTO', 'PANGAN', '', 'Heavy Equipment Operator', '1976-06-26', 'Male', 'Permanent', NULL, 20, '2026-02-26 08:07:26', '2026-02-26 08:07:26', 0),
(464803, 'DANIEL', 'DELA CRUZ', 'JOSON', NULL, 'Water Resources Facilities Operator B', '1968-01-03', 'Male', 'Permanent', 'employee_464803_1772504829.jpeg', 19, '2026-02-26 07:56:39', '2026-03-03 02:27:09', 0),
(492366, 'IVAN', 'VIVAR', 'CUEVAS', '', 'Industrial Security Guard A', '1990-07-22', 'Male', 'Casual', NULL, 9, '2026-02-26 08:38:32', '2026-02-26 08:38:32', 0),
(492520, 'ZANDIE', 'VILLAGRACIA', 'ESTEBAN', '', 'Utility Worker A', '1996-09-28', 'Male', 'Job Order', NULL, 9, '2026-02-27 01:15:54', '2026-02-27 01:15:54', 0),
(498561, 'JAYSON', 'RAMILO', 'TOMINEZ', '', 'Water Resources Facilities Operator B', '1983-10-26', 'Male', 'Casual', NULL, 9, '2026-02-26 08:41:07', '2026-02-26 08:41:07', 0),
(501518, 'JERICO', 'FULGUERAS', 'GALDORES', '', 'Engineer A', '1995-03-01', 'Male', 'Permanent', NULL, 20, '2026-02-26 08:04:27', '2026-02-26 08:04:27', 0),
(506073, 'JET', 'VENTURINA', 'LEGASPI', '', 'Cashier A', '1978-01-27', 'Female', 'Permanent', NULL, 10, '2026-02-26 07:42:27', '2026-02-26 07:42:27', 0),
(508425, 'RODOLFO', 'CIPRIANJO', 'PADUNAN', 'Jr.', 'Industrial Security Guard A', '1977-01-07', 'Male', 'Permanent', NULL, 24, '2026-02-26 07:32:37', '2026-02-26 07:32:37', 0),
(516453, 'EMMANUEL', 'MAGNO', 'NAGAÑO', '', 'Driver Mechanic B', '1982-07-29', 'Male', 'Casual', NULL, 20, '2026-02-27 00:40:55', '2026-02-27 00:40:55', 0),
(517043, 'RUSSELLENIE', 'DAMACIO', 'PRIETO', '', 'Industrial Nurse', '1986-09-10', 'Female', 'Casual', NULL, 9, '2026-02-27 00:13:46', '2026-02-27 00:13:46', 0),
(527628, 'EDNA', 'TIQUIA', 'CRISTOBAL', '', 'Supervising Irrigators Development Officer', '1973-04-19', 'Female', 'Permanent', NULL, 21, '2026-02-26 08:16:05', '2026-02-26 08:16:05', 0),
(533109, 'RICA NICHOLE', 'TORRES', 'MACABATA', '', 'Data Encoder', '1999-08-25', 'Female', 'Job Order', NULL, 10, '2026-02-27 05:04:16', '2026-02-27 05:04:16', 0),
(536111, 'ALGER', 'SANTIAGO', 'PASCUAL', NULL, 'Photographer IV', '1989-09-23', 'Male', 'Casual', 'employee_536111_1772173063.jpeg', 34, '2026-02-26 08:21:42', '2026-02-27 06:17:43', 0),
(548437, 'ROSE', 'PASCUAL', 'QUEJADA', '', 'Records Assistant', '1981-03-01', 'Female', 'Casual', NULL, 9, '2026-02-26 08:30:34', '2026-02-26 08:30:34', 0),
(548486, 'FREDERICK', 'SANGALANG', 'NANGEL', '', 'Utility Worker A', '1969-06-23', 'Male', 'Casual', NULL, 20, '2026-02-26 08:44:07', '2026-02-26 08:44:07', 0),
(552752, 'ROSEMARIE', 'PANGAN', 'LORENZO', '', 'Housekeeping Services Headman A', '1962-02-26', 'Female', 'Casual', NULL, 9, '2026-02-26 08:35:36', '2026-02-26 08:35:36', 0),
(555124, 'NOEL', 'BELTRAN', 'NUPALIA', '', '-', '1987-04-21', 'Male', 'Job Order', NULL, 34, '2026-02-27 05:09:37', '2026-02-27 05:09:37', 0),
(559092, 'LAILA KRISCHELLE', 'URGENTE', 'GARCIA', '', 'Cashiering Assistant', '1994-09-09', 'Female', 'Casual', NULL, 10, '2026-02-26 08:51:34', '2026-02-26 08:51:34', 0),
(562171, 'JEFFREY', 'CUNANAN', 'PANGILINAN', '', 'Survey Aide', '1990-09-02', 'Male', 'Casual', NULL, 9, '2026-02-26 08:41:56', '2026-02-26 08:41:56', 0),
(569494, 'MARK JEFFREY', 'PASCUAL', 'CASTRO', '', 'Housekeeping Services Headman B', '1997-11-10', 'Male', 'Casual', NULL, 9, '2026-02-27 00:24:44', '2026-02-27 00:24:44', 0),
(573917, 'SHARON', 'PALOMAR', 'ORENA', '', 'Senior Irrigators Development Officer', '1987-07-07', 'Female', 'Permanent', NULL, 21, '2026-02-26 08:16:57', '2026-02-26 08:16:57', 0),
(578203, 'ORLANDO', 'BALLESTEROS', 'GAMIT', '', 'Foreman B', '1962-01-16', 'Male', 'Casual', NULL, 9, '2026-02-27 00:19:11', '2026-02-27 00:19:11', 0),
(579667, 'SAMUEL', 'TRANQUILINO', 'SAYSON', '', 'Driver Mechanic A', '1975-11-20', 'Male', 'Casual', NULL, 20, '2026-02-27 00:36:48', '2026-02-27 00:36:48', 0),
(580106, 'NAPOLEON FERDINAND', 'DELGADO', 'MENDOZA', '', 'Data Encoder', '1977-04-28', 'Male', 'Permanent', NULL, 18, '2026-02-26 07:49:22', '2026-02-26 07:49:22', 0),
(595357, 'JANFERSON', 'MAURE', 'MIRANDA', '', 'Watchman III', '1998-12-24', 'Male', 'Casual', NULL, 9, '2026-02-27 00:16:05', '2026-02-27 00:16:05', 0),
(608389, 'MARLON', 'DELA CRUZ', 'SORIANO', '', 'Engineer A', '1992-08-22', 'Male', 'Permanent', NULL, 18, '2026-02-26 07:48:23', '2026-02-26 07:48:23', 0),
(609250, 'PINKY', 'PEREZ', 'GONZALES', '', 'Accounting Processor B', '1969-01-30', 'Female', 'Casual', NULL, 10, '2026-02-26 08:47:16', '2026-02-26 08:47:16', 0),
(609813, 'DALE JOSEPH', 'SALAZAR', 'PARARUAN', NULL, 'Data Encoder', '2001-10-02', 'Male', 'Job Order', NULL, 9, '2026-02-27 01:12:54', '2026-02-27 01:13:38', 0),
(619974, 'HARIZA', 'SANTIAGO', 'SALE', '', 'Administrative Aide III', '1998-01-22', 'Female', 'Casual', NULL, 3, '2026-02-27 05:10:47', '2026-02-27 05:10:47', 0),
(644136, 'RICHARD', 'RAYMUNDO', 'MANABAT', '', 'Senior Engineer A', '1988-02-21', 'Male', 'Casual', NULL, 18, '2026-02-26 08:52:11', '2026-02-26 08:52:11', 0),
(651936, 'MARIO', 'DATOR', 'VILLANUEVA', 'Jr.', 'Driver Mechanic A', '1982-05-28', 'Male', 'Casual', NULL, 20, '2026-02-26 08:57:03', '2026-02-26 08:57:03', 0),
(661369, 'RAQUEL', 'ESMABE', 'LIWAG', '', 'Records Officer D', '1971-02-24', 'Female', 'Casual', NULL, 9, '2026-02-26 08:25:21', '2026-02-26 08:25:21', 0),
(665009, 'REYNALDO', 'ORDONEZ', 'REYES', 'Jr.', 'Automotive Mechanic A', '1977-10-16', 'Male', 'Permanent', NULL, 20, '2026-02-26 08:11:48', '2026-02-26 08:11:48', 0),
(667663, 'ROSALINDA', 'ESPIRITU', 'SORIANO', '', 'Financial Planning Specialist B', '1972-10-23', 'Female', 'Permanent', NULL, 10, '2026-02-26 07:39:43', '2026-02-26 07:39:43', 0),
(680891, 'ANGELICA', 'REYES', 'MALLARI', NULL, 'Data Encoder', '1986-02-18', 'Female', 'Permanent', 'employee_680891_1772499100.jpeg', 9, '2026-02-26 07:24:18', '2026-03-03 00:51:40', 0),
(681270, 'JERMAINE', 'TOBIAS', 'AGATON', '', 'Clerk Processor B', '1987-03-21', 'Female', 'Casual', NULL, 9, '2026-02-27 05:08:57', '2026-02-27 05:08:57', 0),
(685880, 'BENJ OLIVER', 'JAVIER', 'CASTRO', NULL, 'Community Relations Officer B', '1992-08-21', 'Male', 'Casual', 'employee_685880_1772498065.jpeg', 21, '2026-02-27 01:00:00', '2026-03-03 00:34:25', 0),
(691402, 'REGIE BOY', 'BENITO', 'REYES', '', 'Utility Worker A', '1981-11-14', 'Male', 'Job Order', NULL, 9, '2026-02-27 01:19:35', '2026-02-27 01:19:35', 0),
(707158, 'HEDGIE', 'LUTUACO', 'NICOLAS', '', 'Electronics Communication System Operator B', '2000-08-31', 'Male', 'Permanent', NULL, 19, '2026-02-26 07:54:49', '2026-02-26 07:54:49', 0),
(730691, 'LANCER', 'ESMABE', 'GALANG', '', 'Senior Engineer A', '1980-01-24', 'Male', 'Permanent', NULL, 18, '2026-02-26 07:45:47', '2026-02-26 07:45:47', 0),
(731802, 'NUELZON', 'LABASAN', 'LOPEZ', '', 'Industrial Security Guard A', '1979-08-19', 'Male', 'Casual', NULL, 9, '2026-02-26 08:40:24', '2026-02-26 08:40:24', 0),
(732960, 'JUNE', 'BINUYA', 'NARITO', '', 'Electronics Communication System Operator', '1965-06-07', 'Male', 'Permanent', NULL, 34, '2026-02-26 08:00:48', '2026-02-26 08:00:48', 0),
(749446, 'MARIBEL', 'DIONISIO', 'OROBIA', '', 'Records Assistant', '1995-10-16', 'Female', 'Casual', NULL, 9, '2026-02-26 08:31:12', '2026-02-26 08:31:12', 0),
(751447, 'DEXTER', 'GREFIEL', 'CRUZ', '', 'Senior Engineer A', '1995-01-04', 'Male', 'Casual', NULL, 34, '2026-02-26 08:27:30', '2026-02-26 08:27:30', 0),
(777388, 'EDNA', 'VILLAROMAN', 'FERMIN', NULL, 'Accountant III', '1958-01-20', 'Female', 'Job Order', 'employee_777388_1772498223.jpeg', 16, '2026-02-27 01:14:24', '2026-03-03 00:37:03', 0),
(781007, 'PAUL KEVIN', 'GOYAL', 'MENDOZA', '', 'Watchman III', '1992-07-31', 'Male', 'Job Order', NULL, 9, '2026-02-27 01:16:29', '2026-02-27 01:16:29', 0),
(784148, 'IRENE', 'P', 'ESTACIO', '', 'Senior Engineer A', '1976-07-09', 'Female', 'Permanent', NULL, 19, '2026-02-26 07:51:02', '2026-02-26 07:51:02', 0),
(790761, 'JUNE', 'JAGON', 'BARIOGA', '', 'Division Manager A', '1977-04-10', 'Male', 'Permanent', NULL, 24, '2026-02-26 07:21:41', '2026-02-26 07:21:41', 0),
(800625, 'FRANCES ANGELIQUE', 'EUGENIO', 'DE GUZMAN', '', 'Corporate Budget Analyst B', '1997-03-25', 'Female', 'Casual', NULL, 10, '2026-02-27 00:27:04', '2026-02-27 00:27:04', 0),
(805509, 'ALBERTO', 'DELA CRUZ', 'LISING', NULL, 'Driver Mechanic B', '1965-08-08', 'Male', 'Permanent', 'employee_805509_1772498879.jpeg', 20, '2026-02-26 08:06:02', '2026-03-03 00:47:59', 0),
(812241, 'Roniel Jade', 'Dugo', 'Verdadero', '', 'Data Controller I', '2002-02-20', 'Male', 'Job Order', NULL, 4, '2026-02-27 05:07:19', '2026-02-27 05:07:19', 0),
(813438, 'VICENTE', 'ESTEBAN', 'VIBAL', '', 'Watchman III', '1983-06-25', 'Male', 'Casual', NULL, 9, '2026-02-26 08:46:28', '2026-02-26 08:46:28', 0),
(817307, 'PAULO', 'FERRER', 'DUCUSIN', '', 'Watchman III', '1993-03-09', 'Male', 'Casual', NULL, 9, '2026-02-27 00:14:34', '2026-02-27 00:14:34', 0),
(831571, 'MA. CELINE', 'LAGUIMUN', 'ESTEBAN', '', 'Data Encoder', '1997-07-28', 'Male', 'Casual', NULL, 6, '2026-02-26 08:24:14', '2026-02-26 08:24:14', 0),
(832208, 'MA. ELIZABETH', 'NOCUM', 'LOPEZ', '', 'Public Relations Officer A', '1981-03-10', 'Female', 'Permanent', NULL, 34, '2026-02-26 07:16:47', '2026-02-26 07:16:47', 0),
(832805, 'ARIEL', 'RAMIREZ', 'MACAPAGAL', '', 'Watchman III', '1974-04-06', 'Male', 'Job Order', NULL, 9, '2026-02-27 01:17:28', '2026-02-27 01:17:28', 0),
(836645, 'MELVIN', 'GALVEZ', 'NAZAR', '', 'Driver Mechanic B', '1990-07-30', 'Male', 'Casual', NULL, 20, '2026-02-27 00:34:18', '2026-02-27 00:34:18', 0),
(839423, 'MYRENE', 'FLORES', 'MANIEGO', NULL, 'Secretary B', '2002-05-20', 'Female', 'Job Order', 'employee_839423_1772499842.jpeg', 10, '2026-02-27 05:03:28', '2026-03-03 01:04:02', 0),
(846559, 'GERALDINE', 'GULAPA', 'DARIO', '', 'Supervising Engineer A', '1972-12-20', 'Female', 'Permanent', NULL, 18, '2026-02-26 07:44:52', '2026-02-26 07:44:52', 0),
(852567, 'MARIBETH', 'OANES', 'CRUZ', '', 'Senior Supply Officer', '1974-03-04', 'Female', 'Permanent', NULL, 24, '2026-02-26 07:26:23', '2026-02-26 07:26:23', 0),
(858115, 'EDGARDO', 'BUENAVENTURA', 'BAGUISA', NULL, 'Heavy Equipment Operator', '1964-07-31', 'Male', 'Permanent', 'employee_858115_1772504966.jpeg', 20, '2026-02-26 08:09:46', '2026-03-03 02:29:26', 0),
(862856, 'HAJIE', 'AGLIBA', 'BENEMERITO', '', 'Water Resources Facilities Operator B', '1996-11-18', 'Male', 'Casual', NULL, 19, '2026-02-27 05:14:05', '2026-02-27 05:14:05', 0),
(864059, 'DANIEL', 'BUENDIA', 'MACASAYA', NULL, 'Automotive Mechanic C', '1989-12-29', 'Male', 'Casual', 'employee_864059_1772501879.jpeg', 20, '2026-02-27 00:59:20', '2026-03-03 01:37:59', 0),
(879471, 'JANA', 'GUMISAD', 'TEE', NULL, 'Senior Data Encoder Controller', '1988-10-26', 'Female', 'Job Order', 'employee_879471_1772498411.jpeg', 4, '2026-02-27 01:02:03', '2026-03-03 00:40:11', 0),
(881731, 'JASMIN', 'CRUZ', 'FERRY', '', 'Utility Worker A', '1982-04-23', 'Female', 'Permanent', NULL, 21, '2026-02-26 07:12:20', '2026-02-26 07:12:20', 0),
(884207, 'PRINCESS', 'DAYAG', 'TAMONDONG', '', 'Engineer I', '1995-05-14', 'Female', 'Casual', NULL, 19, '2026-02-27 00:33:40', '2026-02-27 00:33:40', 0),
(888190, 'KRISSIALEN', 'DOROGA', 'CALPITO', NULL, 'Accounting Processor B', '1998-10-26', 'Female', 'Casual', 'employee_888190_1772499604.jpeg', 10, '2026-02-26 08:50:55', '2026-03-03 01:00:04', 0),
(888651, 'ALVIN', 'VILLAJUAN', 'HIPOLITO', '', 'Watchman III', '1976-08-29', 'Male', 'Casual', NULL, 9, '2026-02-27 00:25:22', '2026-02-27 00:25:22', 0),
(889537, 'ROMMEL', 'TOQUERO', 'GROSPE', '', 'Industrial Security Guard A', '1980-11-03', 'Male', 'Permanent', NULL, 24, '2026-02-26 07:36:16', '2026-02-26 07:36:16', 0),
(890613, 'MERRY DAWN', 'FRONDA', 'HONORIO', NULL, 'Community Relations Chief B', '1986-12-09', 'Female', 'Permanent', 'employee_890613_1772499140.jpeg', 21, '2026-02-26 08:14:11', '2026-03-03 00:52:20', 0),
(893880, 'DEBBIE DAWN', 'ARENAS', 'SANTIAGO', '', 'Accounting Processor A', '1996-03-21', 'Female', 'Casual', NULL, 7, '2026-02-27 00:28:38', '2026-02-27 00:28:38', 0),
(897577, 'MARIA LOURDES', 'ADRINEDA', 'DE GUZMAN', '', 'Cashier B', '1974-03-22', 'Female', 'Casual', NULL, 10, '2026-02-26 08:48:06', '2026-02-26 08:48:06', 0),
(900261, 'MICHAEL', 'INDUCTIVO', 'GARCIA', '', 'Driver Mechanic A', '1973-01-31', 'Male', 'Casual', NULL, 20, '2026-02-27 00:08:23', '2026-02-27 00:08:23', 0),
(902132, 'MARIA THERESA', 'VALDERAMA', 'BUADO', '', 'Water Resources Facilities Operator B', '1969-01-07', 'Female', 'Permanent', NULL, 19, '2026-02-26 07:55:46', '2026-02-26 07:55:46', 0),
(902680, 'JUNE', 'LABUCAY', 'BENEMERITO', '', 'Water Resources Facilities Operator B', '1990-06-04', 'Male', 'Permanent', NULL, 19, '2026-02-26 07:58:54', '2026-02-26 07:58:54', 0),
(902843, 'ROEL', 'LEAÑO', 'VEGIGA', '', 'Division Manager A', '1968-01-19', 'Male', 'Casual', NULL, 9, '2026-02-26 08:19:37', '2026-02-26 08:19:37', 0),
(903089, 'CAMILLA MARIE', 'DIMALIWAT', 'ONDRADE', '', 'Supervising Irrigators Development Officer', '1990-03-24', 'Female', 'Permanent', NULL, 21, '2026-02-26 08:15:18', '2026-02-26 08:15:18', 0),
(907875, 'FILIPINA', 'BARTIDO', 'SOMBILLO', NULL, 'Water Resources Facilities Operator B', '1983-05-07', 'Female', 'Permanent', 'employee_907875_1772498319.jpeg', 16, '2026-02-26 07:30:57', '2026-03-03 00:38:39', 0),
(911855, 'JING ALEXIS', 'VICENTE', 'SANTIAGO', NULL, 'Office Equipment Technician A', '1993-09-01', 'Male', 'Casual', 'employee_911855_1772498465.jpeg', 34, '2026-02-26 08:26:10', '2026-03-03 00:41:05', 0),
(913515, 'NESTOR', 'PASCUAL', 'PORTANA', 'Jr.', 'Driver Mechanic B', '1984-10-06', 'Male', 'Casual', NULL, 9, '2026-02-27 00:58:40', '2026-02-27 00:58:40', 0),
(915863, 'TROY', 'URAGA', 'GUBA', '', 'Watchman III', '1991-09-05', 'Male', 'Casual', NULL, 9, '2026-02-27 00:15:13', '2026-02-27 00:15:13', 0),
(921537, 'RYAN', 'ALMIROL', 'ENCOMIENDA', '', 'Engineer A', '1986-05-25', 'Male', 'Casual', NULL, 20, '2026-02-27 00:34:55', '2026-02-27 00:34:55', 0),
(930795, 'JOHN CARLO', 'CATALAN', 'CORDOVA', '', 'Hydrologist', '1987-08-20', 'Male', 'Permanent', NULL, 19, '2026-02-26 07:51:55', '2026-02-26 07:51:55', 0),
(932573, 'LILIBETH', 'MADRID', 'SISON', NULL, 'Accounting Processor A', '1973-11-20', 'Female', 'Casual', 'employee_932573_1772499668.jpeg', 16, '2026-02-26 08:33:43', '2026-03-03 01:01:08', 0),
(935640, 'MARK IAN', 'DATOR', 'VILLANUEVA', '', 'Senior Computer Services Programmer', '1986-07-17', 'Male', 'Permanent', NULL, 4, '2026-02-26 07:20:15', '2026-02-26 07:20:15', 0),
(937830, 'MERNAN', 'MACASAYA', 'BUSUEGO', NULL, 'Utility Worker A', '1986-07-13', 'Male', 'Permanent', 'employee_937830_1772505270.jpeg', 24, '2026-02-26 07:38:05', '2026-03-03 02:34:30', 0),
(939862, 'MA. JANE EZRELA', 'PADILLA', 'MANALO', '', 'Clerk Processor B', '2001-01-11', 'Female', 'Job Order', NULL, 5, '2026-02-27 05:08:02', '2026-02-27 05:08:02', 0),
(940159, 'DARREN JOSHUA', 'LUSTRE', 'GUEVARRA', '', 'Senior Engineer A', '1995-06-26', 'Male', 'Casual', NULL, 18, '2026-02-27 00:30:49', '2026-02-27 00:30:49', 0),
(952072, 'CARLO JAY', 'CAYANGA', 'CRUZ', NULL, 'Driver Mechanic A', '1994-06-09', 'Male', 'Casual', 'employee_952072_1772501452.jpeg', 20, '2026-02-27 00:35:34', '2026-03-03 01:30:52', 0),
(958418, 'OSCAR', 'UERA', 'DELOS REYES', 'Jr.', 'Supervising Engineer A', '2000-01-29', 'Male', 'Permanent', NULL, 12, '2026-02-26 07:37:16', '2026-02-27 06:34:40', 0),
(962770, 'RODOLFO', 'BOTE', 'QUIZON', '', 'Industrial Security Guard A', '1976-04-24', 'Male', 'Permanent', NULL, 24, '2026-02-26 07:31:55', '2026-02-26 07:31:55', 0),
(975919, 'JEAN AIRA', 'INGALLA', 'BALAGTAS', NULL, 'Office Equipment Technician B', '1998-10-28', 'Female', 'Casual', 'employee_975919_1772505024.jpeg', 20, '2026-02-27 00:30:14', '2026-03-03 02:30:24', 0),
(977936, 'SHERILL', 'DOROGA', 'HERNANDEZ', '', 'Senior Data Encoder', '1975-01-29', 'Female', 'Permanent', NULL, 24, '2026-02-26 07:28:39', '2026-02-26 07:28:39', 0),
(979781, 'JOEL', 'EMPAYNADO', 'DE GUZMAN', '', 'Driver Mechanic A', '1981-12-28', 'Male', 'Casual', NULL, 20, '2026-02-27 00:36:15', '2026-02-27 00:36:15', 0),
(987654, 'PHILIP', 'RIOS', 'JUAN', '', 'Housekeeping Services Headman B', '1989-12-11', 'Male', 'Job Order', NULL, 9, '2026-02-27 04:59:07', '2026-02-27 04:59:07', 0),
(994948, 'JESSIE', 'PUNTIL', 'BAYLON', NULL, 'Water Resources Facilities Operator B', '1967-01-03', 'Male', 'Permanent', 'employee_994948_1772505129.jpeg', 20, '2026-02-26 08:02:51', '2026-03-03 02:32:09', 0),
(998987, 'ALVIN', 'DE LEON', 'MANUEL', '', 'Acting Department Manager', '1965-03-01', 'Male', 'Permanent', NULL, 34, '2026-02-26 06:58:01', '2026-02-26 06:58:01', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_equipment`
--

CREATE TABLE `tbl_equipment` (
  `equipment_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL COMMENT 'FK to tbl_equipment_type_registry.typeId',
  `employee_id` int(11) DEFAULT NULL COMMENT 'Assigned employee (NULL = unassigned)',
  `location_id` int(11) DEFAULT NULL COMMENT 'Direct location assignment (for location-context equipment like CCTV)',
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `property_number` varchar(100) DEFAULT NULL,
  `status` enum('Available','In Use','Under Maintenance','Disposed') NOT NULL DEFAULT 'Available',
  `year_acquired` year(4) DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `is_archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_equipment`
--

INSERT INTO `tbl_equipment` (`equipment_id`, `type_id`, `employee_id`, `location_id`, `brand`, `model`, `serial_number`, `property_number`, `status`, `year_acquired`, `acquisition_date`, `created_at`, `updated_at`, `is_archived`) VALUES
(1, 1, 998987, 34, 'AMD', 'Custom Built', '1111', NULL, 'In Use', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(2, 1, NULL, NULL, 'AMD', 'Custom Built', '0002', NULL, 'Available', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(3, 1, NULL, NULL, 'ACER AIO PC', 'Pre-Built', '0003', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(4, 1, NULL, NULL, 'ASUS AIO PC', 'Pre-Built', '0004', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(5, 1, NULL, NULL, 'INTEL', 'Custom Built', '0005', NULL, 'Available', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(6, 1, NULL, NULL, 'AMD', 'Custom Built', '0006', NULL, 'Available', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(7, 1, NULL, NULL, 'AMD', 'Custom Built', '0007', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(8, 1, NULL, NULL, 'DELL AIO PC', 'Pre-Built', '0008', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(9, 1, NULL, NULL, 'AMD', 'Custom Built', '0009', NULL, 'Available', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(10, 1, NULL, NULL, 'LENOVO', 'Pre-Built', '0010', NULL, 'Available', '2016', NULL, '2026-03-03 00:17:49', NULL, 0),
(11, 1, 935640, 4, 'AMD', 'Custom Built', 'NR200PV2KCNNS001241600132', NULL, 'In Use', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(12, 1, 911855, 34, 'HP AIO PC', 'Pre-Built', '8CC84607V5', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(13, 1, NULL, NULL, 'AMD', 'Custom Built', 'MCBB600LKA5NS001192400324', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(14, 1, 879471, 4, 'ACER', 'Pre-Built', 'DTBK7SP00241101E009600', NULL, 'In Use', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(15, 1, NULL, NULL, 'ACER', 'Pre-Built', 'DTBK7SP00241101E329600', NULL, 'Available', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(16, 1, NULL, NULL, 'ACER', 'Pre-Built', 'MMTX5SP003416049AD2X00', NULL, 'Available', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(17, 1, NULL, NULL, 'ACER', 'Pre-Built', '0017', NULL, 'Available', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(18, 1, NULL, NULL, 'AMD', 'Custom Built', '0018', NULL, 'Available', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(19, 1, NULL, NULL, 'HP', 'Pre-Built', '0019', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(20, 1, NULL, NULL, 'HP', 'Pre-Built', '0020', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(21, 1, NULL, NULL, 'ACER', 'Pre-Built', '0021', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(22, 1, NULL, NULL, 'ACER', 'Pre-Built', '0022', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(23, 1, NULL, NULL, 'HP', 'Pre-Built', '0023', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(24, 1, NULL, NULL, 'ASUS AIO PC', 'Pre-Built', '0024', NULL, 'Available', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(25, 1, NULL, NULL, 'HP AIO PC', 'Pre-Built', '0025', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(26, 1, NULL, NULL, 'HP', 'Pre-Built', '0026', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(27, 1, NULL, NULL, 'ACER', 'Pre-Built', '0027', NULL, 'Available', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(28, 1, NULL, NULL, 'HP AIO PC', 'Pre-Built', '0028', NULL, 'Available', '2017', NULL, '2026-03-03 00:17:49', NULL, 0),
(29, 1, NULL, NULL, 'LENOVO', 'Pre-Built', '0029', NULL, 'Available', '2016', NULL, '2026-03-03 00:17:49', NULL, 0),
(30, 1, NULL, NULL, 'ACER', 'Pre-Built', '0030', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(31, 1, NULL, NULL, 'HP', 'Pre-Built', '0031', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(32, 1, NULL, NULL, 'HP', 'Pre-Built', '0032', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(33, 1, NULL, NULL, 'ACER', 'Pre-Built', '0033', NULL, 'Available', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(34, 1, NULL, NULL, 'DELL AIO PC', 'Pre-Built', '0034', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(35, 1, NULL, NULL, 'ACER', 'Pre-Built', '0035', NULL, 'Available', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(36, 1, NULL, NULL, 'HP', 'Pre-Built', '0036', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(37, 1, NULL, NULL, 'HP', 'Pre-Built', '0037', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(38, 1, NULL, NULL, 'HP AIO PC', 'Pre-Built', '0038', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(39, 1, NULL, NULL, 'DELL AIO PC', 'Pre-Built', '0039', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(40, 1, NULL, NULL, 'HP', 'Pre-Built', '0040', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(41, 1, NULL, NULL, 'HP', 'Pre-Built', '0041', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(42, 1, NULL, NULL, 'DELL AIO PC', 'Pre-Built', '0042', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(43, 1, NULL, NULL, 'HP', 'Pre-Built', '0043', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(44, 1, 777388, 16, 'DELL AIO PC', 'Pre-Built', 'GWJJDF2', NULL, 'In Use', '2022', NULL, '2026-03-03 00:17:49', NULL, 0),
(45, 1, 170722, 7, 'DELL', 'Pre-Built', '3799MP2', NULL, 'In Use', '2020', NULL, '2026-03-03 00:17:49', NULL, 0),
(46, 1, 907875, 16, 'ACER AIO PC', 'Pre-Built', 'DQB80SP00271904848300', NULL, 'In Use', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(47, 1, 932573, 16, 'ACER', 'Pre-Built', 'DTB8ASP012812060413000', NULL, 'In Use', '2017', NULL, '2026-03-03 00:17:49', NULL, 0),
(48, 1, 958418, 12, 'ACER', 'Pre-Built', 'DTVPUSP121831022879600', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(49, 1, 417554, 12, 'ACER', 'Pre-Built', 'DTSXLSP02655102A973000', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(50, 1, 410918, 10, 'HP', 'Pre-Built', '8CG9201GT4', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(51, 1, 261775, 10, 'HP', 'Pre-Built', '8CG85040G0', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(52, 1, 296288, 10, 'HP', 'Pre-Built', '8CG85040F3', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(53, 1, 609250, 10, 'ACER', 'Pre-Built', 'DTBEVSP00E03901CB89600', NULL, 'In Use', '2021', NULL, '2026-03-03 00:17:49', NULL, 0),
(54, 1, 839423, 10, 'LENOVO', 'Pre-Built', '8SSC80K13486F1WH63101D2', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(55, 1, 897577, 10, 'HP', 'Pre-Built', '8CG833312P', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(56, 1, 102892, 10, 'HP', 'Pre-Built', '8CG9081YP6', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(57, 1, 888190, 10, 'HP', 'Pre-Built', '8CG9081YN2', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(58, 1, 156379, 10, 'ACER', 'Pre-Built', 'DTBGXSP00114206DEE9600', NULL, 'In Use', '2022', NULL, '2026-03-03 00:17:49', NULL, 0),
(59, 1, 902132, 19, 'HP', 'Pre-Built', '8CG9081YPM', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(62, 1, 170456, 7, 'HP', 'Pre-Built', '8CG9201GT8', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(63, 1, NULL, NULL, 'HP', 'Pre-Built', '8CG8413BH5', NULL, 'Available', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(64, 1, 249874, 21, 'ACER', 'Pre-Built', 'DTBGXSP005128011AB9600', NULL, 'In Use', '2022', NULL, '2026-03-03 00:17:49', NULL, 0),
(65, 1, 393709, 21, 'HP PAVILION', 'Pre-Built', '8CG8413BHT', NULL, 'In Use', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(66, 1, 685880, 21, 'AMD', 'Custom Built', 'M2A07200241', NULL, 'In Use', '2022', NULL, '2026-03-03 00:17:49', NULL, 0),
(67, 1, 527628, 21, 'AMD', 'Custom Built', '2AN2A17703817', NULL, 'In Use', '2023', NULL, '2026-03-03 00:17:49', '2026-03-03 01:36:37', 0),
(68, 1, 573917, 21, 'DELL', 'Pre-Built', '37L9MP2', NULL, 'In Use', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(69, 1, 382312, 21, 'HP', 'Pre-Built', '3CM82706F5', NULL, 'In Use', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(10001, 2, NULL, NULL, 'ACER AIO PC', NULL, '0046', NULL, 'Available', '2018', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10002, 2, NULL, NULL, 'DELL AIO PC', NULL, '0044', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10003, 2, NULL, NULL, 'DELL AIO PC', NULL, '0042', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10004, 2, NULL, NULL, 'DELL AIO PC', NULL, '0039', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10005, 2, NULL, NULL, 'HP AIO PC', NULL, '0038', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10006, 2, NULL, NULL, 'DELL AIO PC', NULL, '0034', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10007, 2, NULL, NULL, 'HP AIO PC', NULL, '0028', NULL, 'Available', '2017', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10008, 2, NULL, NULL, 'HP AIO PC', NULL, '0025', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10009, 2, NULL, NULL, 'ASUS AIO PC', NULL, '0024', NULL, 'Available', '2018', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10010, 2, NULL, NULL, 'HP AIO PC', NULL, '0012', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10011, 2, NULL, NULL, 'DELL AIO PC', NULL, '0008', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10012, 2, NULL, NULL, 'ASUS AIO PC', NULL, '0004', NULL, 'Available', '2019', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10013, 2, NULL, NULL, 'ACER AIO PC', NULL, '0003', NULL, 'Available', '2020', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10015, 2, 962770, 24, 'Lenovo', NULL, 'MF1TL1LG', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10016, 2, 220749, 10, 'ACER', NULL, 'DQBBUSP0039470692A3000', NULL, 'In Use', '2020', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10017, 2, 667663, 10, 'HP', NULL, '8CC4080CJR', NULL, 'In Use', '2024', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10018, 2, 389637, 10, 'HP', NULL, '8CC72316NP', NULL, 'In Use', '2017', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10019, 2, 893880, 7, 'DELL', NULL, '3DF6G92', NULL, 'In Use', '2021', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(10020, 2, 527628, NULL, 'ACER', NULL, 'DQBM4SP001524023563000', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', '2026-03-03 02:44:01', 0),
(10021, 2, 890613, 21, 'ASUS', NULL, 'CCAh18LP2340T5', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', '2026-03-03 02:17:04', 0),
(20008, 3, 935640, 4, 'ViewSonic', NULL, '34534', NULL, 'In Use', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(20072, 3, 417554, 12, 'Samsung', NULL, 'ZZCLH4LG602521T', NULL, 'In Use', '2016', NULL, '2026-03-03 00:17:49', NULL, 0),
(20073, 3, 958418, 12, 'Asus', NULL, 'B4LMIZ014138', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(20074, 3, 410918, 10, 'HP', NULL, '3CM9040C85', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(20075, 3, 261775, 10, 'HP', NULL, '3CM9040C8K', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(20076, 3, 296288, 10, 'HP', NULL, '3CM9040C98', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(20077, 3, 609250, 10, 'ACER', NULL, '03911016542', NULL, 'In Use', '2021', NULL, '2026-03-03 00:17:49', NULL, 0),
(20078, 3, 839423, 10, 'LENOVO', NULL, '1S65BAACC1WWU38DG787', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(20079, 3, 897577, 10, 'HP', NULL, '3CM9040CB7', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(20080, 3, 102892, 10, 'HP', NULL, '3CM8450WTT', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(20081, 3, 888190, 10, 'HP', NULL, '3CM9040C11', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(20082, 3, 156379, 10, 'ACER', NULL, 'MMTJDSS002136025593W01', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(20083, 3, 932573, 16, 'HP', NULL, '3CM82707LR', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(20084, 3, 170722, 7, 'HP', NULL, 'CNK62306FS', NULL, 'In Use', '2017', NULL, '2026-03-03 00:17:49', NULL, 0),
(20085, 3, 902132, 19, 'VIEWSONIC', NULL, 'TSN1623E0148', NULL, 'In Use', '2017', NULL, '2026-03-03 00:17:49', NULL, 0),
(20086, 3, 170456, 7, 'HP', NULL, '3CM835048Q', NULL, 'In Use', '2019', NULL, '2026-03-03 00:17:49', NULL, 0),
(20087, 3, NULL, NULL, 'HP', NULL, '3CM82706DQ', NULL, 'Available', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(20088, 3, 249874, 21, 'ACER', NULL, '416018832233', NULL, 'In Use', '2024', NULL, '2026-03-03 00:17:49', NULL, 0),
(20089, 3, 393709, 21, 'HP', NULL, '3CM8250DJ7', NULL, 'In Use', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(20090, 3, 527628, NULL, 'HP', NULL, '3CM82706F5', NULL, 'In Use', '2018', NULL, '2026-03-03 00:17:49', '2026-03-03 02:43:39', 0),
(20091, 3, 685880, 21, 'N-VISION', NULL, 'N240BCSJH23051120', NULL, 'In Use', '2023', NULL, '2026-03-03 00:17:49', NULL, 0),
(20092, 3, 573917, 21, 'DELL', NULL, 'CN04XPPCTV2007B6028BA01', NULL, 'In Use', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(20093, 3, 382312, 21, 'SAMSUNG', NULL, 'ZZQSH4TJ902176R', NULL, 'In Use', '2018', NULL, '2026-03-03 00:17:49', NULL, 0),
(30001, 4, 958418, 12, 'Epson', 'L3150', 'X93K025953', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30002, 4, 417554, 12, 'Epson', 'L360', 'VGFK284880', NULL, 'In Use', '2017', NULL, '2026-03-03 00:17:49', NULL, 0),
(30003, 4, 220749, 10, 'Canon', 'G4010', 'KNLD14042', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30004, 4, 667663, 10, 'EPSON', 'WF-C5790', 'X3BC005492', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30005, 4, 261775, 10, 'HP', 'SMART TANK 515', 'CN1414S2NT', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30006, 4, 389637, 10, 'EPSON', 'L3550', 'XBCF014593', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30007, 4, 296288, 10, 'EPSON', '3150', 'X93K048868', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30008, 4, 609250, 10, 'EPSON', 'L3110', 'X93P189494', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30009, 4, 839423, 10, 'EPSON', 'L3550', 'XBCF005397', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30010, 4, 897577, 10, 'EPSON', 'L3550', 'XBCF003754', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30011, 4, 102892, 10, 'EPSON', 'L3550', 'XBCF005420', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30012, 4, 888190, 10, 'HP', 'SMART TANK 515', 'CN32J440BT', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30013, 4, 156379, 10, 'CANON', 'G3020', 'KMTK07285', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30014, 4, 932573, 16, 'BROTHER', 'DCP-T720DW', 'E80726M2H823122', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30015, 4, 777388, 16, 'EPSON', 'WF-C5790', 'X3BC005496', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30016, 4, 893880, 7, 'EPSON', 'L3250', 'X8JZL72586', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30017, 4, 902132, 19, 'HP', 'P1102', 'VNF5R48770', NULL, 'In Use', '1998', NULL, '2026-03-03 00:17:49', NULL, 0),
(30018, 4, NULL, NULL, 'EPSON', 'L1210', 'X8LG004855', NULL, 'Available', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30019, 4, 249874, 21, 'EPSON', 'L5290', 'X8H5383549', NULL, 'In Use', '2025', NULL, '2026-03-03 00:17:49', NULL, 0),
(30020, 4, 393709, 21, 'EPSON', 'L5290', 'X8H5383186', NULL, 'In Use', '2025', NULL, '2026-03-03 00:17:49', NULL, 0),
(30021, 4, 527628, NULL, 'EPSON', 'L3210', 'X8HV517965', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', '2026-03-03 02:46:35', 0),
(30022, 4, 685880, 21, 'EPSON', 'L14150', 'X6QU055644', NULL, 'In Use', '2023', NULL, '2026-03-03 00:17:49', NULL, 0),
(30023, 4, 527628, NULL, 'EPSON', 'L3210', 'X8HV519343', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', '2026-03-03 02:45:55', 0),
(30024, 4, 573917, 21, 'EPSON', 'L14150', 'X6QU032639', NULL, 'In Use', '2023', NULL, '2026-03-03 00:17:49', NULL, 0),
(30025, 4, 382312, 21, 'EPSON', 'L3150', 'X5EN023118', NULL, 'In Use', '1990', NULL, '2026-03-03 00:17:49', NULL, 0),
(30026, 4, 890613, 21, 'EPSON', 'L1210', 'X8LG005598', NULL, 'In Use', '2024', NULL, '2026-03-03 00:17:49', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_equipment_specs`
--

CREATE TABLE `tbl_equipment_specs` (
  `spec_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `spec_key` varchar(50) NOT NULL COMMENT 'e.g. Processor, Memory, GPU, Storage, Monitor Size, Category',
  `spec_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_equipment_specs`
--

INSERT INTO `tbl_equipment_specs` (`spec_id`, `equipment_id`, `spec_key`, `spec_value`) VALUES
(1, 1, 'Category', 'Custom Built'),
(2, 2, 'Category', 'Custom Built'),
(3, 3, 'Category', 'Pre-Built'),
(4, 4, 'Category', 'Pre-Built'),
(5, 5, 'Category', 'Custom Built'),
(6, 6, 'Category', 'Custom Built'),
(7, 7, 'Category', 'Custom Built'),
(8, 8, 'Category', 'Pre-Built'),
(9, 9, 'Category', 'Custom Built'),
(10, 10, 'Category', 'Pre-Built'),
(11, 11, 'Category', 'Custom Built'),
(12, 12, 'Category', 'Pre-Built'),
(13, 13, 'Category', 'Custom Built'),
(14, 14, 'Category', 'Pre-Built'),
(15, 15, 'Category', 'Pre-Built'),
(16, 16, 'Category', 'Pre-Built'),
(17, 17, 'Category', 'Pre-Built'),
(18, 18, 'Category', 'Custom Built'),
(19, 19, 'Category', 'Pre-Built'),
(20, 20, 'Category', 'Pre-Built'),
(21, 21, 'Category', 'Pre-Built'),
(22, 22, 'Category', 'Pre-Built'),
(23, 23, 'Category', 'Pre-Built'),
(24, 24, 'Category', 'Pre-Built'),
(25, 25, 'Category', 'Pre-Built'),
(26, 26, 'Category', 'Pre-Built'),
(27, 27, 'Category', 'Pre-Built'),
(28, 28, 'Category', 'Pre-Built'),
(29, 29, 'Category', 'Pre-Built'),
(30, 30, 'Category', 'Pre-Built'),
(31, 31, 'Category', 'Pre-Built'),
(32, 32, 'Category', 'Pre-Built'),
(33, 33, 'Category', 'Pre-Built'),
(34, 34, 'Category', 'Pre-Built'),
(35, 35, 'Category', 'Pre-Built'),
(36, 36, 'Category', 'Pre-Built'),
(37, 37, 'Category', 'Pre-Built'),
(38, 38, 'Category', 'Pre-Built'),
(39, 39, 'Category', 'Pre-Built'),
(40, 40, 'Category', 'Pre-Built'),
(41, 41, 'Category', 'Pre-Built'),
(42, 42, 'Category', 'Pre-Built'),
(43, 43, 'Category', 'Pre-Built'),
(44, 44, 'Category', 'Pre-Built'),
(45, 45, 'Category', 'Pre-Built'),
(46, 46, 'Category', 'Pre-Built'),
(47, 47, 'Category', 'Pre-Built'),
(48, 48, 'Category', 'Pre-Built'),
(49, 49, 'Category', 'Pre-Built'),
(50, 50, 'Category', 'Pre-Built'),
(51, 51, 'Category', 'Pre-Built'),
(52, 52, 'Category', 'Pre-Built'),
(53, 53, 'Category', 'Pre-Built'),
(54, 54, 'Category', 'Pre-Built'),
(55, 55, 'Category', 'Pre-Built'),
(56, 56, 'Category', 'Pre-Built'),
(57, 57, 'Category', 'Pre-Built'),
(58, 58, 'Category', 'Pre-Built'),
(59, 59, 'Category', 'Pre-Built'),
(60, 62, 'Category', 'Pre-Built'),
(61, 63, 'Category', 'Pre-Built'),
(62, 64, 'Category', 'Pre-Built'),
(63, 65, 'Category', 'Pre-Built'),
(64, 66, 'Category', 'Custom Built'),
(66, 68, 'Category', 'Pre-Built'),
(67, 69, 'Category', 'Pre-Built'),
(128, 1, 'Processor', 'Ryzen 5700X'),
(129, 2, 'Processor', 'Ryzen 5700X'),
(130, 3, 'Processor', 'i5- 8th Gen'),
(131, 4, 'Processor', 'i7-8th Gen'),
(132, 5, 'Processor', 'i7-8th'),
(133, 6, 'Processor', 'Ryzen 5700X'),
(134, 7, 'Processor', 'Ryzen 5 3600'),
(135, 8, 'Processor', 'i5- 11th Gen'),
(136, 9, 'Processor', 'Ryzen 5700X'),
(137, 10, 'Processor', 'i3 - 4th Gen'),
(138, 11, 'Processor', 'Ryzen 7 7800X3D'),
(139, 12, 'Processor', 'i7-8th Gen'),
(140, 13, 'Processor', 'Ryzen 5 3400G'),
(141, 14, 'Processor', 'i5 - 13th Gen'),
(142, 15, 'Processor', 'i5 - 13th Gen'),
(143, 16, 'Processor', 'i5 - 13th Gen'),
(144, 17, 'Processor', 'i5 - 7th Gen'),
(145, 18, 'Processor', 'Ryzen 5700X'),
(146, 19, 'Processor', 'i7-8th Gen'),
(147, 20, 'Processor', 'i7-8th Gen'),
(148, 21, 'Processor', 'i7-8th Gen'),
(149, 22, 'Processor', 'i7-8th Gen'),
(150, 23, 'Processor', 'i7-8th Gen'),
(151, 24, 'Processor', 'i5- 8th Gen'),
(152, 25, 'Processor', 'i7-8th Gen'),
(153, 26, 'Processor', 'i7-8th Gen'),
(154, 27, 'Processor', 'i5 - 13th Gen'),
(155, 28, 'Processor', 'i3 - 7th Gen'),
(156, 29, 'Processor', 'i3 - 4th Gen'),
(157, 30, 'Processor', 'i7-8th Gen'),
(158, 31, 'Processor', 'i5- 8th Gen'),
(159, 32, 'Processor', 'i7-8th Gen'),
(160, 33, 'Processor', 'i7 - 12th Gen'),
(161, 34, 'Processor', 'i7 - 11th Gen'),
(162, 35, 'Processor', 'i5 - 13th Gen'),
(163, 36, 'Processor', 'i7-8th Gen'),
(164, 37, 'Processor', 'i7-8th Gen'),
(165, 38, 'Processor', 'i7-8th Gen'),
(166, 39, 'Processor', 'i7 - 11th Gen'),
(167, 40, 'Processor', 'i7-8th Gen'),
(168, 41, 'Processor', 'i7-8th Gen'),
(169, 42, 'Processor', 'i7-8th Gen'),
(170, 43, 'Processor', 'i7-8th Gen'),
(171, 44, 'Processor', 'i7 - 11th Gen'),
(172, 45, 'Processor', 'i7 - 7th Gen'),
(173, 46, 'Processor', 'i3 - 7th Gen'),
(174, 47, 'Processor', 'i5 - 7th Gen'),
(175, 48, 'Processor', 'i5 7th Gen'),
(176, 49, 'Processor', 'i3 4th Gen'),
(177, 50, 'Processor', 'i7 8th Gen'),
(178, 51, 'Processor', 'i7 8th Gen'),
(179, 52, 'Processor', 'i7-8TH GEN'),
(180, 53, 'Processor', 'i5 - 10th Gen'),
(181, 54, 'Processor', 'i3 - 4th Gen'),
(182, 55, 'Processor', 'i7 - 8th Gen'),
(183, 56, 'Processor', 'i7 - 8th Gen'),
(184, 57, 'Processor', 'i7 - 8th Gen'),
(185, 58, 'Processor', 'i5 - 10th Gen'),
(186, 59, 'Processor', 'i7-8th Gen'),
(187, 62, 'Processor', 'i7 - 8th Gen'),
(188, 63, 'Processor', 'i7 - 8th Gen'),
(189, 64, 'Processor', 'intel i7-11th Gen'),
(190, 65, 'Processor', 'i7 8th Gen'),
(191, 66, 'Processor', 'Ryzen 5 5600G'),
(193, 68, 'Processor', 'i7-7th Gen'),
(194, 69, 'Processor', 'i5 - 8th Gen'),
(255, 1, 'Memory', '16GB DDR4'),
(256, 2, 'Memory', '16GB DDR4'),
(257, 3, 'Memory', '4GB DDR4'),
(258, 4, 'Memory', '16GB DDR4'),
(259, 5, 'Memory', '16GB DDR4'),
(260, 6, 'Memory', '16GB DDR4'),
(261, 7, 'Memory', '32GB DDR4'),
(262, 8, 'Memory', '8GB DDR4'),
(263, 9, 'Memory', '16GB DDR4'),
(264, 10, 'Memory', '4GB DDR4'),
(265, 11, 'Memory', '32GB DDR5'),
(266, 12, 'Memory', '8GB DDR4'),
(267, 13, 'Memory', '16GB DDR4'),
(268, 14, 'Memory', '8GB DDR4'),
(269, 15, 'Memory', '8GB DDR4'),
(270, 16, 'Memory', '8GB DDR4'),
(271, 17, 'Memory', '16GB DDR4'),
(272, 18, 'Memory', '16GB DDR4'),
(273, 19, 'Memory', '4GB DDR4'),
(274, 20, 'Memory', '8GB DDR4'),
(275, 21, 'Memory', '8GB DDR4'),
(276, 22, 'Memory', '8GB DDR4'),
(277, 23, 'Memory', '8GB DDR4'),
(278, 24, 'Memory', '8GB DDR4'),
(279, 25, 'Memory', '8GB DDR4'),
(280, 26, 'Memory', '8GB DDR4'),
(281, 27, 'Memory', '8GB DDR4'),
(282, 28, 'Memory', '4GB DDR4'),
(283, 29, 'Memory', '4GB DDR4'),
(284, 30, 'Memory', '8GB DDR4'),
(285, 31, 'Memory', '8GB DDR4'),
(286, 32, 'Memory', '8GB DDR4'),
(287, 33, 'Memory', '8GB DDR4'),
(288, 34, 'Memory', '8GB DDR4'),
(289, 35, 'Memory', '8GB DDR4'),
(290, 36, 'Memory', '8GB DDR4'),
(291, 37, 'Memory', '4GB DDR4'),
(292, 38, 'Memory', '8GB DDR4'),
(293, 39, 'Memory', '8GB DDR4'),
(294, 40, 'Memory', '8GB DDR4'),
(295, 41, 'Memory', '8GB DDR4'),
(296, 42, 'Memory', '8GB DDR4'),
(297, 43, 'Memory', '8GB DDR4'),
(298, 44, 'Memory', '8GB DDR4'),
(299, 45, 'Memory', '8GB DDR4'),
(300, 46, 'Memory', '4GB DDR4'),
(301, 47, 'Memory', '8GB DDR4'),
(302, 48, 'Memory', '8gb DDR4'),
(303, 49, 'Memory', '4GB DDR4'),
(304, 50, 'Memory', '4GB DDR4'),
(305, 51, 'Memory', '16GB DDR4'),
(306, 52, 'Memory', '8GB DDR4'),
(307, 53, 'Memory', '8GB'),
(308, 54, 'Memory', '4GB DDR4'),
(309, 55, 'Memory', '4GB DDR4'),
(310, 56, 'Memory', '8GB DDR4'),
(311, 57, 'Memory', '8GB DDR4'),
(312, 58, 'Memory', '8GB DDR4'),
(313, 59, 'Memory', '4GB DDR4'),
(314, 62, 'Memory', '8GB DDR4'),
(315, 63, 'Memory', '8GB DDR4'),
(316, 64, 'Memory', '8GB RAM'),
(317, 65, 'Memory', '8gb DDR4'),
(318, 66, 'Memory', '16GB DDR4'),
(320, 68, 'Memory', '16GB DDR4'),
(321, 69, 'Memory', '8GB DDR4'),
(382, 1, 'GPU', 'RTX 3050'),
(383, 2, 'GPU', 'RTX 3050'),
(384, 3, 'GPU', 'Integrated GPU'),
(385, 4, 'GPU', 'GTX 1050'),
(386, 5, 'GPU', 'GTX 1050'),
(387, 6, 'GPU', 'RTX 3050'),
(388, 7, 'GPU', 'GTX 1650'),
(389, 8, 'GPU', 'Integrated GPU'),
(390, 9, 'GPU', 'RTX 3050'),
(391, 10, 'GPU', 'Integrated GPU'),
(392, 11, 'GPU', 'AMD RX 7800 XT'),
(393, 12, 'GPU', 'Integrated GPU'),
(394, 13, 'GPU', 'Integrated GPU'),
(395, 14, 'GPU', 'Integrated GPU'),
(396, 15, 'GPU', 'Integrated GPU'),
(397, 16, 'GPU', 'Integrated GPU'),
(398, 17, 'GPU', 'Integrated GPU'),
(399, 18, 'GPU', 'RTX 3050'),
(400, 19, 'GPU', 'Integrated GPU'),
(401, 20, 'GPU', 'Integrated GPU'),
(402, 21, 'GPU', 'GTX 1050'),
(403, 22, 'GPU', 'GTX 1050'),
(404, 23, 'GPU', 'Integrated GPU'),
(405, 24, 'GPU', 'Integrated GPU'),
(406, 25, 'GPU', 'Integrated GPU'),
(407, 26, 'GPU', 'Integrated GPU'),
(408, 27, 'GPU', 'Integrated GPU'),
(409, 28, 'GPU', 'Integrated GPU'),
(410, 29, 'GPU', 'Integrated GPU'),
(411, 30, 'GPU', 'GTX 1050'),
(412, 31, 'GPU', 'Integrated GPU'),
(413, 32, 'GPU', 'Integrated GPU'),
(414, 33, 'GPU', 'Integrated GPU'),
(415, 34, 'GPU', 'Integrated GPU'),
(416, 35, 'GPU', 'Integrated GPU'),
(417, 36, 'GPU', 'Integrated GPU'),
(418, 37, 'GPU', 'Integrated GPU'),
(419, 38, 'GPU', 'Integrated GPU'),
(420, 39, 'GPU', 'Integrated GPU'),
(421, 40, 'GPU', 'Integrated GPU'),
(422, 41, 'GPU', 'Integrated GPU'),
(423, 42, 'GPU', 'Integrated GPU'),
(424, 43, 'GPU', 'Integrated GPU'),
(425, 44, 'GPU', 'Integrated GPU'),
(426, 45, 'GPU', 'Integrated GPU'),
(427, 46, 'GPU', 'Integrated GPU'),
(428, 47, 'GPU', 'Integrated GPU'),
(429, 48, 'GPU', 'Integrated'),
(430, 49, 'GPU', 'Integrated'),
(431, 50, 'GPU', 'Integrated'),
(432, 51, 'GPU', 'Integrated'),
(433, 52, 'GPU', 'INTEGRATED'),
(434, 53, 'GPU', 'Integrated'),
(435, 54, 'GPU', 'Integrated'),
(436, 55, 'GPU', 'Integrated'),
(437, 56, 'GPU', 'Integrated'),
(438, 57, 'GPU', 'Integrated'),
(439, 58, 'GPU', 'Integrated'),
(440, 59, 'GPU', 'INTEGRATED'),
(441, 62, 'GPU', 'integrated'),
(442, 63, 'GPU', 'Integrated'),
(443, 64, 'GPU', 'integrated'),
(444, 65, 'GPU', 'Integrated GPU'),
(445, 66, 'GPU', 'RTX3050'),
(447, 68, 'GPU', 'GTX 1050'),
(448, 69, 'GPU', 'GT 730'),
(509, 1, 'Storage', '1TB SSD'),
(510, 2, 'Storage', '1TB SSD'),
(511, 3, 'Storage', '256GB SSD / 1TB HDD'),
(512, 4, 'Storage', '256GB SSD / 1TB HDD'),
(513, 5, 'Storage', '256GB SSD / 1TB HDD'),
(514, 6, 'Storage', '1TB SSD'),
(515, 7, 'Storage', '256GB SSD / 2TB HDD'),
(516, 8, 'Storage', '256GB SSD / 1TB HDD'),
(517, 9, 'Storage', '1TB SSD'),
(518, 10, 'Storage', '256GB SSD / 1TB HDD'),
(519, 11, 'Storage', '1TB SSD'),
(520, 12, 'Storage', '256GB SSD / 1TB HDD'),
(521, 13, 'Storage', '256GB SSD'),
(522, 14, 'Storage', '256GB SSD / 1TB HDD'),
(523, 15, 'Storage', '256GB SSD / 1TB HDD'),
(524, 16, 'Storage', '256GB SSD / 1TB HDD'),
(525, 17, 'Storage', '1TB HDD'),
(526, 18, 'Storage', '1TB SSD'),
(527, 19, 'Storage', '256GB SSD / 1TB HDD'),
(528, 20, 'Storage', '256GB SSD / 2TB HDD'),
(529, 21, 'Storage', '1TB HDD'),
(530, 22, 'Storage', '1TB HDD'),
(531, 23, 'Storage', '256GB SSD / 1TB HDD'),
(532, 24, 'Storage', '256GB SSD'),
(533, 25, 'Storage', '256GB SSD / 1TB HDD'),
(534, 26, 'Storage', '256GB SSD'),
(535, 27, 'Storage', '256GB SSD / 1TB HDD'),
(536, 28, 'Storage', '512GB SSD'),
(537, 29, 'Storage', '1TB HDD'),
(538, 30, 'Storage', '1TB HDD'),
(539, 31, 'Storage', '2TB HDD'),
(540, 32, 'Storage', '256GB SSD / 2TB HDD'),
(541, 33, 'Storage', '256GB SSD / 1TB HDD'),
(542, 34, 'Storage', '256GB SSD / 1TB HDD'),
(543, 35, 'Storage', '256GB SSD / 1TB HDD'),
(544, 36, 'Storage', '256GB SSD / 2TB HDD'),
(545, 37, 'Storage', '1TB HDD'),
(546, 38, 'Storage', '256GB SSD / 1TB HDD'),
(547, 39, 'Storage', '256GB SSD / 1TB HDD'),
(548, 40, 'Storage', '256GB SSD / 2TB HDD'),
(549, 41, 'Storage', '256GB SSD / 2TB HDD'),
(550, 42, 'Storage', '256GB SSD / 1TB HDD'),
(551, 43, 'Storage', '512GB SSD / 2TB HDD'),
(552, 44, 'Storage', '256GB SSD / 1TB HDD'),
(553, 45, 'Storage', '128GB SSD / 1TB HDD'),
(554, 46, 'Storage', '1TB HDD'),
(555, 47, 'Storage', '1TB HDD'),
(556, 48, 'Storage', '1TB HDD'),
(557, 49, 'Storage', '1TB HDD'),
(558, 50, 'Storage', '1TB HDD'),
(559, 51, 'Storage', '512SSD / 2TB HDD'),
(560, 52, 'Storage', '256GB SSD / 1TB HDD'),
(561, 53, 'Storage', '256GB SSD / 1TB HDD'),
(562, 54, 'Storage', '1TB HDD'),
(563, 55, 'Storage', '2TB HDD'),
(564, 56, 'Storage', '256GB SSD / 2TB HDD'),
(565, 57, 'Storage', '256GB SSD / 2TB HDD'),
(566, 58, 'Storage', '256GB SSD / 1TB HDD'),
(567, 59, 'Storage', '256GB SSD / 2TB HDD'),
(568, 62, 'Storage', '256 SSD / 2TB HDD'),
(569, 63, 'Storage', '256GB SSD / 2TB HDD'),
(570, 64, 'Storage', '256GB SSD / 1TB HDD'),
(571, 65, 'Storage', '256SSD / 2TB HDD'),
(572, 66, 'Storage', '1TB SSD'),
(574, 68, 'Storage', '128GB SSB/1TB HDD'),
(575, 69, 'Storage', '256GB SSD / 2TB HDD'),
(636, 10001, 'Processor', 'i3 - 7th Gen'),
(637, 10002, 'Processor', 'i7 - 11th Gen'),
(638, 10003, 'Processor', 'i7-8th Gen'),
(639, 10004, 'Processor', 'i7 - 11th Gen'),
(640, 10005, 'Processor', 'i7-8th Gen'),
(641, 10006, 'Processor', 'i7 - 11th Gen'),
(642, 10007, 'Processor', 'i3 - 7th Gen'),
(643, 10008, 'Processor', 'i7-8th Gen'),
(644, 10009, 'Processor', 'i5- 8th Gen'),
(645, 10010, 'Processor', 'i7-8th Gen'),
(646, 10011, 'Processor', 'i5- 11th Gen'),
(647, 10012, 'Processor', 'i7-8th Gen'),
(648, 10013, 'Processor', 'i5- 8th Gen'),
(649, 10015, 'Processor', 'i3 10th Gen'),
(650, 10016, 'Processor', 'i5 8th Gen'),
(651, 10017, 'Processor', 'Ultra 5 125U'),
(652, 10018, 'Processor', 'i3 7th Gen'),
(653, 10019, 'Processor', 'i7 - 11 Gen'),
(655, 10021, 'Processor', 'i7-8th Gen'),
(667, 10001, 'Memory', '4GB DDR4'),
(668, 10002, 'Memory', '8GB DDR4'),
(669, 10003, 'Memory', '8GB DDR4'),
(670, 10004, 'Memory', '8GB DDR4'),
(671, 10005, 'Memory', '8GB DDR4'),
(672, 10006, 'Memory', '8GB DDR4'),
(673, 10007, 'Memory', '4GB DDR4'),
(674, 10008, 'Memory', '8GB DDR4'),
(675, 10009, 'Memory', 'i5- 8th Gen'),
(676, 10010, 'Memory', '8GB DDR4'),
(677, 10011, 'Memory', '8GB DDR4'),
(678, 10012, 'Memory', '16GB DDR4'),
(679, 10013, 'Memory', '4GB DDR4'),
(680, 10015, 'Memory', '4GB'),
(681, 10016, 'Memory', '4GB DDR4'),
(682, 10017, 'Memory', '16GB DDR5'),
(683, 10018, 'Memory', '4GB DDR4'),
(684, 10019, 'Memory', '8GB DDR4'),
(686, 10021, 'Memory', '16GB'),
(698, 10001, 'GPU', 'Integrated GPU'),
(699, 10002, 'GPU', 'Integrated GPU'),
(700, 10003, 'GPU', 'Integrated GPU'),
(701, 10004, 'GPU', 'Integrated GPU'),
(702, 10005, 'GPU', 'Integrated GPU'),
(703, 10006, 'GPU', 'Integrated GPU'),
(704, 10007, 'GPU', 'Integrated GPU'),
(705, 10008, 'GPU', 'Integrated GPU'),
(706, 10009, 'GPU', 'Integrated GPU'),
(707, 10010, 'GPU', 'Integrated GPU'),
(708, 10011, 'GPU', 'Integrated GPU'),
(709, 10012, 'GPU', 'GTX 1050'),
(710, 10013, 'GPU', 'Integrated GPU'),
(711, 10015, 'GPU', 'Integrated'),
(712, 10016, 'GPU', 'Integrated'),
(713, 10017, 'GPU', 'Integrated'),
(714, 10018, 'GPU', 'Integrated'),
(715, 10019, 'GPU', 'MX330'),
(717, 10021, 'GPU', 'GTX 1050'),
(729, 10001, 'Storage', '1TB HDD'),
(730, 10002, 'Storage', '256GB SSD / 1TB HDD'),
(731, 10003, 'Storage', '256GB SSD / 1TB HDD'),
(732, 10004, 'Storage', '256GB SSD / 1TB HDD'),
(733, 10005, 'Storage', '256GB SSD / 1TB HDD'),
(734, 10006, 'Storage', '256GB SSD / 1TB HDD'),
(735, 10007, 'Storage', '512GB SSD'),
(736, 10008, 'Storage', '256GB SSD / 1TB HDD'),
(737, 10009, 'Storage', '256GB SSD'),
(738, 10010, 'Storage', '256GB SSD / 1TB HDD'),
(739, 10011, 'Storage', '256GB SSD / 1TB HDD'),
(740, 10012, 'Storage', '256GB SSD / 1TB HDD'),
(741, 10013, 'Storage', '256GB SSD / 1TB HDD'),
(742, 10015, 'Storage', '1TB HDD'),
(743, 10016, 'Storage', '256 SSD / 1TB HDD'),
(744, 10017, 'Storage', '512GB SSD'),
(745, 10018, 'Storage', '1TB HDD'),
(746, 10019, 'Storage', '256 SSD / 1TB HDD'),
(748, 10021, 'Storage', '256 GB SSD/ 1TB HDD'),
(760, 20008, 'Monitor Size', '27 inches'),
(761, 20072, 'Monitor Size', '18 inches'),
(762, 20073, 'Monitor Size', '18 inches'),
(763, 20074, 'Monitor Size', '24 inches'),
(764, 20075, 'Monitor Size', '24 inches'),
(765, 20076, 'Monitor Size', '24 INCHES'),
(766, 20077, 'Monitor Size', '24 inches'),
(767, 20078, 'Monitor Size', '18 inches'),
(768, 20079, 'Monitor Size', '24 inches'),
(769, 20080, 'Monitor Size', '24 inches'),
(770, 20081, 'Monitor Size', '24 inches'),
(771, 20082, 'Monitor Size', '21 inches'),
(772, 20083, 'Monitor Size', '24 inches'),
(773, 20084, 'Monitor Size', '21 inches'),
(774, 20085, 'Monitor Size', '21 INCHES'),
(775, 20086, 'Monitor Size', '24 inches'),
(776, 20087, 'Monitor Size', '22inches'),
(777, 20088, 'Monitor Size', '21 inches'),
(778, 20089, 'Monitor Size', '21 inches'),
(780, 20091, 'Monitor Size', '24 inches'),
(781, 20092, 'Monitor Size', '24 inches'),
(782, 20093, 'Monitor Size', '21.5 inches'),
(800, 67, 'Category', 'Custom Built'),
(801, 67, 'Processor', 'Ryzen 5 Pro 4650G'),
(802, 67, 'Memory', '8GB DDR4'),
(803, 67, 'GPU', 'Integrated'),
(804, 67, 'Storage', '1TB SSD'),
(805, 20090, 'Monitor Size', '21.5 inches'),
(806, 10020, 'Processor', 'Ultra 5 125U'),
(807, 10020, 'Memory', '8gb'),
(808, 10020, 'GPU', 'undefined'),
(809, 10020, 'Storage', '512 SSD');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_equipment_type_registry`
--

CREATE TABLE `tbl_equipment_type_registry` (
  `typeId` int(11) NOT NULL,
  `typeName` varchar(50) NOT NULL,
  `defaultFrequency` int(11) DEFAULT 180,
  `context` enum('Employee','Location') NOT NULL DEFAULT 'Location',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_equipment_type_registry`
--

INSERT INTO `tbl_equipment_type_registry` (`typeId`, `typeName`, `defaultFrequency`, `context`, `created_at`) VALUES
(1, 'System Unit', 180, 'Employee', '2026-02-15 19:18:42'),
(2, 'All-in-One', 180, 'Employee', '2026-02-15 19:18:42'),
(3, 'Monitor', 180, 'Employee', '2026-02-15 19:18:42'),
(4, 'Printer', 180, 'Employee', '2026-02-15 19:18:42'),
(5, 'Laptop', 180, 'Employee', '2026-02-15 19:18:42'),
(8, 'test', 180, 'Location', '2026-03-03 03:05:21');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_maintenance_frequency`
--

CREATE TABLE `tbl_maintenance_frequency` (
  `frequencyId` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `intervalDays` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_maintenance_metrics`
--

CREATE TABLE `tbl_maintenance_metrics` (
  `metricId` int(11) NOT NULL,
  `equipmentType` varchar(50) NOT NULL COMMENT 'typeId from registry',
  `equipmentId` int(11) NOT NULL,
  `avg_interval_days` decimal(8,1) DEFAULT NULL COMMENT 'Average days between maintenance events',
  `total_records` int(11) DEFAULT 0 COMMENT 'Total maintenance records for this equipment',
  `off_schedule_count` int(11) DEFAULT 0 COMMENT 'Times maintained >7 days away from scheduled date',
  `last_computed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `suggested_frequency` enum('Monthly','Quarterly','Semi-Annual','Annual') DEFAULT NULL COMMENT 'Computed optimal frequency based on history'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_maintenance_record`
--

CREATE TABLE `tbl_maintenance_record` (
  `recordId` int(11) NOT NULL,
  `scheduleId` int(11) NOT NULL,
  `templateId` int(11) DEFAULT NULL COMMENT 'FK ??? tbl_maintenance_template.templateId ??? which template was used',
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
-- Table structure for table `tbl_maintenance_response`
--

CREATE TABLE `tbl_maintenance_response` (
  `responseId` int(11) NOT NULL,
  `recordId` int(11) NOT NULL COMMENT 'FK ??? tbl_maintenance_record.recordId',
  `itemId` int(11) DEFAULT NULL COMMENT 'FK ??? tbl_checklist_item.itemId (NULL for legacy/unlinked)',
  `categoryId` int(11) DEFAULT NULL COMMENT 'FK ??? tbl_checklist_category.categoryId',
  `categoryName` varchar(150) NOT NULL COMMENT 'Snapshot of category name at time of inspection',
  `taskDescription` text NOT NULL COMMENT 'Snapshot of task text at time of inspection',
  `response` enum('Yes','No','N/A') NOT NULL DEFAULT 'N/A' COMMENT 'The technician''s answer',
  `sequenceOrder` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order (preserves template ordering)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Individual checklist responses per maintenance record';

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
  `location_group_id` int(11) DEFAULT NULL COMMENT 'The unit/section location_id this schedule was batched with',
  `is_synced` tinyint(1) DEFAULT 1 COMMENT '1 = still aligned with group schedule, 0 = diverged (maintained off-cycle)',
  `frequency_override_days` int(11) DEFAULT NULL COMMENT 'System-suggested frequency in days based on metrics (NULL = use maintenanceFrequency)',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Maintenance schedule for equipment';

--
-- Dumping data for table `tbl_maintenance_schedule`
--

INSERT INTO `tbl_maintenance_schedule` (`scheduleId`, `equipmentType`, `equipmentId`, `maintenanceFrequency`, `lastMaintenanceDate`, `nextDueDate`, `isActive`, `location_group_id`, `is_synced`, `frequency_override_days`, `createdAt`, `updatedAt`) VALUES
(10, '1', 49, '', NULL, '2026-08-26', 1, 12, 1, NULL, '2026-02-27 07:15:39', '2026-03-02 08:56:19'),
(11, '3', 20072, '', NULL, '2026-08-26', 1, 12, 1, NULL, '2026-02-27 07:17:17', '2026-03-03 00:17:49'),
(12, '4', 30002, '', NULL, '2026-08-26', 1, 12, 1, NULL, '2026-02-27 07:18:21', '2026-03-03 00:17:49'),
(13, '3', 20073, '', NULL, '2026-08-26', 1, 12, 1, NULL, '2026-02-27 07:44:48', '2026-03-03 00:17:49'),
(14, '4', 30003, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 07:49:26', '2026-03-03 00:17:49'),
(15, '2', 10016, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 07:52:36', '2026-03-03 00:17:49'),
(16, '1', 50, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 07:55:05', '2026-02-27 07:55:05'),
(17, '3', 20074, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 07:56:15', '2026-03-03 00:17:49'),
(18, '2', 10017, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 07:59:30', '2026-03-03 00:17:49'),
(19, '4', 30004, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:03:11', '2026-03-03 00:17:49'),
(20, '1', 51, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:06:53', '2026-02-27 08:06:53'),
(21, '3', 20075, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:08:54', '2026-03-03 00:17:49'),
(22, '4', 30005, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:12:14', '2026-03-03 00:17:49'),
(23, '2', 10018, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:16:14', '2026-03-03 00:17:49'),
(24, '4', 30006, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:17:13', '2026-03-03 00:17:49'),
(25, '1', 52, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:20:55', '2026-02-27 08:20:55'),
(26, '3', 20076, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:21:50', '2026-03-03 00:17:49'),
(27, '4', 30007, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:22:55', '2026-03-03 00:17:49'),
(28, '1', 53, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:24:29', '2026-02-27 08:24:29'),
(29, '3', 20077, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:26:06', '2026-03-03 00:17:49'),
(30, '4', 30008, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:27:28', '2026-03-03 00:17:49'),
(31, '3', 20078, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:30:33', '2026-03-03 00:17:49'),
(32, '1', 54, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:32:15', '2026-02-27 08:32:15'),
(33, '4', 30009, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:32:48', '2026-03-03 00:17:49'),
(34, '1', 55, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:34:59', '2026-02-27 08:34:59'),
(35, '3', 20079, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:36:03', '2026-03-03 00:17:49'),
(36, '4', 30010, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:36:54', '2026-03-03 00:17:49'),
(37, '1', 56, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:38:59', '2026-02-27 08:38:59'),
(38, '3', 20080, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:39:43', '2026-03-03 00:17:49'),
(39, '4', 30011, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:40:25', '2026-03-03 00:17:49'),
(40, '3', 20081, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:43:14', '2026-03-03 00:17:49'),
(41, '1', 57, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:46:00', '2026-02-27 08:46:00'),
(42, '4', 30012, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:47:23', '2026-03-03 00:17:49'),
(43, '1', 58, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:50:02', '2026-02-27 08:50:02'),
(44, '3', 20082, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:52:31', '2026-03-03 00:17:49'),
(45, '4', 30013, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-02-27 08:53:54', '2026-03-03 00:17:49'),
(46, '3', 20083, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 01:12:12', '2026-03-03 00:17:49'),
(47, '4', 30014, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 01:14:30', '2026-03-03 00:17:49'),
(48, '3', 20084, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 01:19:17', '2026-03-03 00:17:49'),
(49, '4', 30015, '', NULL, '2026-08-26', 1, NULL, 1, NULL, '2026-03-02 01:22:59', '2026-03-03 00:17:49'),
(50, '2', 10019, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 01:36:09', '2026-03-03 00:17:49'),
(51, '1', 59, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 01:37:11', '2026-03-02 01:37:11'),
(52, '4', 30016, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 01:37:34', '2026-03-03 00:17:49'),
(53, '3', 20085, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 01:39:00', '2026-03-03 00:17:49'),
(54, '1', 60, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 03:16:50', '2026-03-02 03:16:50'),
(55, '1', 61, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 03:18:43', '2026-03-02 03:18:43'),
(56, '4', 30017, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:03:53', '2026-03-03 00:17:49'),
(57, '1', 63, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:18:51', '2026-03-02 07:18:51'),
(58, '3', 20087, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:20:48', '2026-03-03 00:17:49'),
(59, '4', 30018, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:22:25', '2026-03-03 00:17:49'),
(60, '1', 64, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:23:48', '2026-03-02 07:23:48'),
(61, '3', 20088, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:27:58', '2026-03-03 00:17:49'),
(62, '4', 30019, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:29:53', '2026-03-03 00:17:49'),
(63, '1', 66, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:48:20', '2026-03-02 07:48:20'),
(64, '3', 20091, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:50:35', '2026-03-03 00:17:49'),
(65, '4', 30022, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 07:52:52', '2026-03-03 00:17:49'),
(66, '1', 68, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 08:00:47', '2026-03-02 08:00:47'),
(67, '3', 20092, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 08:02:41', '2026-03-03 00:17:49'),
(68, '4', 30024, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 08:03:57', '2026-03-03 00:17:49'),
(69, '2', 10021, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 08:19:32', '2026-03-03 00:17:49'),
(70, '4', 30026, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 08:20:59', '2026-03-03 00:17:49'),
(71, '1', 69, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 08:23:55', '2026-03-02 08:23:55'),
(72, '3', 20093, '', NULL, '2026-08-29', 1, NULL, 1, NULL, '2026-03-02 08:25:59', '2026-03-03 00:17:49'),
(73, '1', 48, 'Semi-Annual', NULL, '2026-03-09', 1, 12, 1, NULL, '2026-03-02 08:56:19', '2026-03-02 08:56:19'),
(74, '4', 30001, 'Semi-Annual', NULL, '2026-03-09', 1, 12, 1, NULL, '2026-03-02 08:56:19', '2026-03-03 00:17:49');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_maintenance_template`
--

CREATE TABLE `tbl_maintenance_template` (
  `templateId` int(11) NOT NULL,
  `templateName` varchar(100) NOT NULL,
  `targetTypeId` varchar(50) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `structure_json` longtext DEFAULT NULL,
  `signatories_json` longtext DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT 1,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_maintenance_template`
--

INSERT INTO `tbl_maintenance_template` (`templateId`, `templateName`, `targetTypeId`, `frequency`, `structure_json`, `signatories_json`, `isActive`, `createdAt`) VALUES
(5, 'ICT PREVENTIVE MAINTENANCE', '1', 'Semi-Annual', '{\"categories\":[{\"order\":1,\"title\":\"I. PHYSICAL INSPECTION, INTERIORS AND CLEANING\",\"items\":[{\"order\":1,\"text\":\"Dust removal performed\"},{\"order\":2,\"text\":\"Parts are intact\"}]},{\"order\":2,\"title\":\"II. HARDWARE PERFORMANCE CHECK\",\"items\":[{\"order\":1,\"text\":\"Power Supply is working properly\"}]},{\"order\":3,\"title\":\"Untitled\",\"items\":[]}]}', '{\"verifiedByName\":\"[Select Supervisor Name]\",\"verifiedByTitle\":\"DIVISION \\/ SECTION HEAD\",\"notedByName\":\"[Select Head of Office]\",\"notedByTitle\":\"HEAD OF OFFICE\"}', 1, '2026-03-02 07:42:29');

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
(8, 'ViewSonic', '27 inches', '34534', '2024', 935640),
(72, 'Samsung', '18 inches', 'ZZCLH4LG602521T', '2016', 417554),
(73, 'Asus', '18 inches', 'B4LMIZ014138', '1990', 958418),
(74, 'HP', '24 inches', '3CM9040C85', '2019', 410918),
(75, 'HP', '24 inches', '3CM9040C8K', '2019', 261775),
(76, 'HP', '24 INCHES', '3CM9040C98', '2019', 296288),
(77, 'ACER', '24 inches', '03911016542', '2021', 609250),
(78, 'LENOVO', '18 inches', '1S65BAACC1WWU38DG787', '1990', 839423),
(79, 'HP', '24 inches', '3CM9040CB7', '2019', 897577),
(80, 'HP', '24 inches', '3CM8450WTT', '2019', 102892),
(81, 'HP', '24 inches', '3CM9040C11', '2019', 888190),
(82, 'ACER', '21 inches', 'MMTJDSS002136025593W01', '1990', 156379),
(83, 'HP', '24 inches', '3CM82707LR', '2019', 932573),
(84, 'HP', '21 inches', 'CNK62306FS', '2017', 170722),
(85, 'VIEWSONIC', '21 INCHES', 'TSN1623E0148', '2017', 902132),
(86, 'HP', '24 inches', '3CM835048Q', '2019', 170456),
(87, 'HP', '22inches', '3CM82706DQ', '2018', NULL),
(88, 'ACER', '21 inches', '416018832233', '2024', 249874),
(89, 'HP', '21 inches', '3CM8250DJ7', '2018', 393709),
(90, 'HP', '21.5 inches', '3CM82706F5', '2018', 527628),
(91, 'N-VISION', '24 inches', 'N240BCSJH23051120', '2023', 685880),
(92, 'DELL', '24 inches', 'CN04XPPCTV2007B6028BA01', '2018', 573917),
(93, 'SAMSUNG', '21.5 inches', 'ZZQSH4TJ902176R', '2018', 382312);

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
(1, 'Epson', 'L3150', 'X93K025953', '1990', 958418),
(2, 'Epson', 'L360', 'VGFK284880', '2017', 417554),
(3, 'Canon', 'G4010', 'KNLD14042', '1990', 220749),
(4, 'EPSON', 'WF-C5790', 'X3BC005492', '1990', 667663),
(5, 'HP', 'SMART TANK 515', 'CN1414S2NT', '1990', 261775),
(6, 'EPSON', 'L3550', 'XBCF014593', '1990', 389637),
(7, 'EPSON', '3150', 'X93K048868', '1990', 296288),
(8, 'EPSON', 'L3110', 'X93P189494', '1990', 609250),
(9, 'EPSON', 'L3550', 'XBCF005397', '1990', 839423),
(10, 'EPSON', 'L3550', 'XBCF003754', '1990', 897577),
(11, 'EPSON', 'L3550', 'XBCF005420', '1990', 102892),
(12, 'HP', 'SMART TANK 515', 'CN32J440BT', '1990', 888190),
(13, 'CANON', 'G3020', 'KMTK07285', '1990', 156379),
(14, 'BROTHER', 'DCP-T720DW', 'E80726M2H823122', '1990', 932573),
(15, 'EPSON', 'WF-C5790', 'X3BC005496', '1990', 777388),
(16, 'EPSON', 'L3250', 'X8JZL72586', '1990', 893880),
(17, 'HP', 'P1102', 'VNF5R48770', '1998', 902132),
(18, 'EPSON', 'L1210', 'X8LG004855', '1990', NULL),
(19, 'EPSON', 'L5290', 'X8H5383549', '2025', 249874),
(20, 'EPSON', 'L5290', 'X8H5383186', '2025', 393709),
(21, 'EPSON', 'L3210', 'X8HV517965', '1990', 903089),
(22, 'EPSON', 'L14150', 'X6QU055644', '2023', 685880),
(23, 'EPSON', 'L3210', 'X8HV519343', '1990', 903089),
(24, 'EPSON', 'L14150', 'X6QU032639', '2023', 573917),
(25, 'EPSON', 'L3150', 'X5EN023118', '1990', 382312),
(26, 'EPSON', 'L1210', 'X8LG005598', '2024', 890613);

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
(1, 'Custom Built', 'AMD', 'Ryzen 5700X', '16GB DDR4', 'RTX 3050', '1TB SSD', '1111', '2024', 998987),
(2, 'Custom Built', 'AMD', 'Ryzen 5700X', '16GB DDR4', 'RTX 3050', '1TB SSD', '0002', '2024', NULL),
(3, 'Pre-Built', 'ACER AIO PC', 'i5- 8th Gen', '4GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0003', '2020', NULL),
(4, 'Pre-Built', 'ASUS AIO PC', 'i7-8th Gen', '16GB DDR4', 'GTX 1050', '256GB SSD / 1TB HDD', '0004', '2019', NULL),
(5, 'Custom Built', 'INTEL', 'i7-8th', '16GB DDR4', 'GTX 1050', '256GB SSD / 1TB HDD', '0005', '2018', NULL),
(6, 'Custom Built', 'AMD', 'Ryzen 5700X', '16GB DDR4', 'RTX 3050', '1TB SSD', '0006', '2024', NULL),
(7, 'Custom Built', 'AMD', 'Ryzen 5 3600', '32GB DDR4', 'GTX 1650', '256GB SSD / 2TB HDD', '0007', '2020', NULL),
(8, 'Pre-Built', 'DELL AIO PC', 'i5- 11th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0008', '2020', NULL),
(9, 'Custom Built', 'AMD', 'Ryzen 5700X', '16GB DDR4', 'RTX 3050', '1TB SSD', '0009', '2024', NULL),
(10, 'Pre-Built', 'LENOVO', 'i3 - 4th Gen', '4GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0010', '2016', NULL),
(11, 'Custom Built', 'AMD', 'Ryzen 7 7800X3D', '32GB DDR5', 'AMD RX 7800 XT', '1TB SSD', 'NR200PV2KCNNS001241600132', '2024', 935640),
(12, 'Pre-Built', 'HP AIO PC', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '8CC84607V5', '2019', 911855),
(13, 'Custom Built', 'AMD', 'Ryzen 5 3400G', '16GB DDR4', 'Integrated GPU', '256GB SSD', 'MCBB600LKA5NS001192400324', '2020', NULL),
(14, 'Pre-Built', 'ACER', 'i5 - 13th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', 'DTBK7SP00241101E009600', '2024', 879471),
(15, 'Pre-Built', 'ACER', 'i5 - 13th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', 'DTBK7SP00241101E329600', '2024', NULL),
(16, 'Pre-Built', 'ACER', 'i5 - 13th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', 'MMTX5SP003416049AD2X00', '2024', NULL),
(17, 'Pre-Built', 'ACER', 'i5 - 7th Gen', '16GB DDR4', 'Integrated GPU', '1TB HDD', '0017', '2018', NULL),
(18, 'Custom Built', 'AMD', 'Ryzen 5700X', '16GB DDR4', 'RTX 3050', '1TB SSD', '0018', '2024', NULL),
(19, 'Pre-Built', 'HP', 'i7-8th Gen', '4GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0019', '2019', NULL),
(20, 'Pre-Built', 'HP', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 2TB HDD', '0020', '2019', NULL),
(21, 'Pre-Built', 'ACER', 'i7-8th Gen', '8GB DDR4', 'GTX 1050', '1TB HDD', '0021', '2020', NULL),
(22, 'Pre-Built', 'ACER', 'i7-8th Gen', '8GB DDR4', 'GTX 1050', '1TB HDD', '0022', '2020', NULL),
(23, 'Pre-Built', 'HP', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0023', '2019', NULL),
(24, 'Pre-Built', 'ASUS AIO PC', 'i5- 8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD', '0024', '2018', NULL),
(25, 'Pre-Built', 'HP AIO PC', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0025', '2019', NULL),
(26, 'Pre-Built', 'HP', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD', '0026', '2019', NULL),
(27, 'Pre-Built', 'ACER', 'i5 - 13th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0027', '2024', NULL),
(28, 'Pre-Built', 'HP AIO PC', 'i3 - 7th Gen', '4GB DDR4', 'Integrated GPU', '512GB SSD', '0028', '2017', NULL),
(29, 'Pre-Built', 'LENOVO', 'i3 - 4th Gen', '4GB DDR4', 'Integrated GPU', '1TB HDD', '0029', '2016', NULL),
(30, 'Pre-Built', 'ACER', 'i7-8th Gen', '8GB DDR4', 'GTX 1050', '1TB HDD', '0030', '2020', NULL),
(31, 'Pre-Built', 'HP', 'i5- 8th Gen', '8GB DDR4', 'Integrated GPU', '2TB HDD', '0031', '2020', NULL),
(32, 'Pre-Built', 'HP', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 2TB HDD', '0032', '2019', NULL),
(33, 'Pre-Built', 'ACER', 'i7 - 12th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0033', '2024', NULL),
(34, 'Pre-Built', 'DELL AIO PC', 'i7 - 11th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0034', '2020', NULL),
(35, 'Pre-Built', 'ACER', 'i5 - 13th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0035', '2024', NULL),
(36, 'Pre-Built', 'HP', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 2TB HDD', '0036', '2019', NULL),
(37, 'Pre-Built', 'HP', 'i7-8th Gen', '4GB DDR4', 'Integrated GPU', '1TB HDD', '0037', '2019', NULL),
(38, 'Pre-Built', 'HP AIO PC', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0038', '2019', NULL),
(39, 'Pre-Built', 'DELL AIO PC', 'i7 - 11th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0039', '2020', NULL),
(40, 'Pre-Built', 'HP', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 2TB HDD', '0040', '2019', NULL),
(41, 'Pre-Built', 'HP', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 2TB HDD', '0041', '2019', NULL),
(42, 'Pre-Built', 'DELL AIO PC', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', '0042', '2020', NULL),
(43, 'Pre-Built', 'HP', 'i7-8th Gen', '8GB DDR4', 'Integrated GPU', '512GB SSD / 2TB HDD', '0043', '2019', NULL),
(44, 'Pre-Built', 'DELL AIO PC', 'i7 - 11th Gen', '8GB DDR4', 'Integrated GPU', '256GB SSD / 1TB HDD', 'GWJJDF2', '2022', 777388),
(45, 'Pre-Built', 'DELL', 'i7 - 7th Gen', '8GB DDR4', 'Integrated GPU', '128GB SSD / 1TB HDD', '3799MP2', '2020', 170722),
(46, 'Pre-Built', 'ACER AIO PC', 'i3 - 7th Gen', '4GB DDR4', 'Integrated GPU', '1TB HDD', 'DQB80SP00271904848300', '2018', 907875),
(47, 'Pre-Built', 'ACER', 'i5 - 7th Gen', '8GB DDR4', 'Integrated GPU', '1TB HDD', 'DTB8ASP012812060413000', '2017', 932573),
(48, 'Pre-Built', 'ACER', 'i5 7th Gen', '8gb DDR4', 'Integrated', '1TB HDD', 'DTVPUSP121831022879600', '1990', 958418),
(49, 'Pre-Built', 'ACER', 'i3 4th Gen', '4GB DDR4', 'Integrated', '1TB HDD', 'DTSXLSP02655102A973000', '1990', 417554),
(50, 'Pre-Built', 'HP', 'i7 8th Gen', '4GB DDR4', 'Integrated', '1TB HDD', '8CG9201GT4', '2019', 410918),
(51, 'Pre-Built', 'HP', 'i7 8th Gen', '16GB DDR4', 'Integrated', '512SSD / 2TB HDD', '8CG85040G0', '2019', 261775),
(52, 'Pre-Built', 'HP', 'i7-8TH GEN', '8GB DDR4', 'INTEGRATED', '256GB SSD / 1TB HDD', '8CG85040F3', '2019', 296288),
(53, 'Pre-Built', 'ACER', 'i5 - 10th Gen', '8GB', 'Integrated', '256GB SSD / 1TB HDD', 'DTBEVSP00E03901CB89600', '2021', 609250),
(54, 'Pre-Built', 'LENOVO', 'i3 - 4th Gen', '4GB DDR4', 'Integrated', '1TB HDD', '8SSC80K13486F1WH63101D2', '1990', 839423),
(55, 'Pre-Built', 'HP', 'i7 - 8th Gen', '4GB DDR4', 'Integrated', '2TB HDD', '8CG833312P', '2019', 897577),
(56, 'Pre-Built', 'HP', 'i7 - 8th Gen', '8GB DDR4', 'Integrated', '256GB SSD / 2TB HDD', '8CG9081YP6', '2019', 102892),
(57, 'Pre-Built', 'HP', 'i7 - 8th Gen', '8GB DDR4', 'Integrated', '256GB SSD / 2TB HDD', '8CG9081YN2', '2019', 888190),
(58, 'Pre-Built', 'ACER', 'i5 - 10th Gen', '8GB DDR4', 'Integrated', '256GB SSD / 1TB HDD', 'DTBGXSP00114206DEE9600', '2022', 156379),
(59, 'Pre-Built', 'HP', 'i7-8th Gen', '4GB DDR4', 'INTEGRATED', '256GB SSD / 2TB HDD', '8CG9081YPM', '2019', 902132),
(62, 'Pre-Built', 'HP', 'i7 - 8th Gen', '8GB DDR4', 'integrated', '256 SSD / 2TB HDD', '8CG9201GT8', '2019', 170456),
(63, 'Pre-Built', 'HP', 'i7 - 8th Gen', '8GB DDR4', 'Integrated', '256GB SSD / 2TB HDD', '8CG8413BH5', '2018', NULL),
(64, 'Pre-Built', 'ACER', 'intel i7-11th Gen', '8GB RAM', 'integrated', '256GB SSD / 1TB HDD', 'DTBGXSP005128011AB9600', '2022', 249874),
(65, 'Pre-Built', 'HP PAVILION', 'i7 8th Gen', '8gb DDR4', 'Integrated GPU', '256SSD / 2TB HDD', '8CG8413BHT', '2018', 393709),
(66, 'Custom Built', 'AMD', 'Ryzen 5 5600G', '16GB DDR4', 'RTX3050', '1TB SSD', 'M2A07200241', '2022', 685880),
(67, 'Custom Built', 'AMD', 'Ryzen 5 Pro 4650G', '8GB DDR4', 'Integrated', '1TB SSD', '2AN2A17703817', '2023', 527628),
(68, 'Pre-Built', 'DELL', 'i7-7th Gen', '16GB DDR4', 'GTX 1050', '128GB SSB/1TB HDD', '37L9MP2', '2018', 573917),
(69, 'Pre-Built', 'HP', 'i5 - 8th Gen', '8GB DDR4', 'GT 730', '256GB SSD / 2TB HDD', '3CM82706F5', '2018', 382312);

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
`type_name` varchar(50)
,`type_id` int(11)
,`id` int(11)
,`brand` varchar(100)
,`serial` varchar(255)
,`owner_name` varchar(201)
,`location_name` varchar(255)
,`context` enum('Employee','Location')
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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_maintenance_master`  AS SELECT `r`.`typeName` AS `type_name`, `r`.`typeId` AS `type_id`, `e`.`equipment_id` AS `id`, `e`.`brand` AS `brand`, coalesce(`e`.`serial_number`,'N/A') AS `serial`, CASE WHEN `r`.`context` = 'Employee' AND `emp`.`employeeId` is not null THEN concat(`emp`.`firstName`,' ',`emp`.`lastName`) ELSE 'N/A' END AS `owner_name`, CASE WHEN `r`.`context` = 'Employee' AND `emp`.`employeeId` is not null THEN `el`.`location_name` WHEN `r`.`context` = 'Location' AND `e`.`location_id` is not null THEN `ll`.`location_name` ELSE 'N/A' END AS `location_name`, `r`.`context` AS `context` FROM ((((`tbl_equipment` `e` join `tbl_equipment_type_registry` `r` on(`e`.`type_id` = `r`.`typeId`)) left join `tbl_employee` `emp` on(`e`.`employee_id` = `emp`.`employeeId`)) left join `location` `el` on(`emp`.`location_id` = `el`.`location_id`)) left join `location` `ll` on(`e`.`location_id` = `ll`.`location_id`)) WHERE `e`.`is_archived` = 0 ;

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
-- Indexes for table `data_change_tracker`
--
ALTER TABLE `data_change_tracker`
  ADD PRIMARY KEY (`category`);

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
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`);

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
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

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
-- Indexes for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `tbl_allinone`
--
ALTER TABLE `tbl_allinone`
  ADD PRIMARY KEY (`allinoneId`),
  ADD UNIQUE KEY `idx_allinone_serial` (`allinoneSerial`),
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
-- Indexes for table `tbl_equipment`
--
ALTER TABLE `tbl_equipment`
  ADD PRIMARY KEY (`equipment_id`),
  ADD UNIQUE KEY `uniq_type_serial` (`type_id`,`serial_number`),
  ADD KEY `idx_type` (`type_id`),
  ADD KEY `idx_employee` (`employee_id`),
  ADD KEY `idx_location` (`location_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_archived` (`is_archived`);

--
-- Indexes for table `tbl_equipment_specs`
--
ALTER TABLE `tbl_equipment_specs`
  ADD PRIMARY KEY (`spec_id`),
  ADD UNIQUE KEY `uniq_equipment_spec` (`equipment_id`,`spec_key`),
  ADD KEY `idx_equipment` (`equipment_id`),
  ADD KEY `idx_spec_key` (`spec_key`);

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
-- Indexes for table `tbl_maintenance_metrics`
--
ALTER TABLE `tbl_maintenance_metrics`
  ADD PRIMARY KEY (`metricId`),
  ADD UNIQUE KEY `idx_equipment` (`equipmentType`,`equipmentId`);

--
-- Indexes for table `tbl_maintenance_record`
--
ALTER TABLE `tbl_maintenance_record`
  ADD PRIMARY KEY (`recordId`),
  ADD KEY `idx_main_lookup` (`equipmentTypeId`,`equipmentId`),
  ADD KEY `idx_schedule` (`scheduleId`),
  ADD KEY `idx_equipment` (`equipmentTypeId`,`equipmentId`),
  ADD KEY `idx_date` (`maintenanceDate`),
  ADD KEY `idx_record_template` (`templateId`);

--
-- Indexes for table `tbl_maintenance_response`
--
ALTER TABLE `tbl_maintenance_response`
  ADD PRIMARY KEY (`responseId`),
  ADD KEY `idx_resp_record` (`recordId`),
  ADD KEY `idx_resp_item` (`itemId`),
  ADD KEY `idx_resp_category` (`categoryId`);

--
-- Indexes for table `tbl_maintenance_schedule`
--
ALTER TABLE `tbl_maintenance_schedule`
  ADD PRIMARY KEY (`scheduleId`),
  ADD KEY `idx_equipment` (`equipmentType`,`equipmentId`),
  ADD KEY `idx_due_date` (`nextDueDate`),
  ADD KEY `idx_active` (`isActive`),
  ADD KEY `idx_location_group` (`location_group_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=589;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_accounts`
--
ALTER TABLE `tbl_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_allinone`
--
ALTER TABLE `tbl_allinone`
  MODIFY `allinoneId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_checklist_category`
--
ALTER TABLE `tbl_checklist_category`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_checklist_item`
--
ALTER TABLE `tbl_checklist_item`
  MODIFY `itemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  MODIFY `employeeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=998988;

--
-- AUTO_INCREMENT for table `tbl_equipment`
--
ALTER TABLE `tbl_equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30030;

--
-- AUTO_INCREMENT for table `tbl_equipment_specs`
--
ALTER TABLE `tbl_equipment_specs`
  MODIFY `spec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=826;

--
-- AUTO_INCREMENT for table `tbl_equipment_type_registry`
--
ALTER TABLE `tbl_equipment_type_registry`
  MODIFY `typeId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_maintenance_frequency`
--
ALTER TABLE `tbl_maintenance_frequency`
  MODIFY `frequencyId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_maintenance_metrics`
--
ALTER TABLE `tbl_maintenance_metrics`
  MODIFY `metricId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_maintenance_record`
--
ALTER TABLE `tbl_maintenance_record`
  MODIFY `recordId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_maintenance_response`
--
ALTER TABLE `tbl_maintenance_response`
  MODIFY `responseId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_maintenance_schedule`
--
ALTER TABLE `tbl_maintenance_schedule`
  MODIFY `scheduleId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `tbl_maintenance_template`
--
ALTER TABLE `tbl_maintenance_template`
  MODIFY `templateId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_monitor`
--
ALTER TABLE `tbl_monitor`
  MODIFY `monitorId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `tbl_otherequipment`
--
ALTER TABLE `tbl_otherequipment`
  MODIFY `otherEquipmentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_printer`
--
ALTER TABLE `tbl_printer`
  MODIFY `printerId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_software`
--
ALTER TABLE `tbl_software`
  MODIFY `softwareId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_systemunit`
--
ALTER TABLE `tbl_systemunit`
  MODIFY `systemunitId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `location`
--
ALTER TABLE `location`
  ADD CONSTRAINT `location_type_id` FOREIGN KEY (`location_type_id`) REFERENCES `location_type` (`id`),
  ADD CONSTRAINT `parent_location_id` FOREIGN KEY (`parent_location_id`) REFERENCES `location` (`location_id`);

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
-- Constraints for table `tbl_checklist_item`
--
ALTER TABLE `tbl_checklist_item`
  ADD CONSTRAINT `fk_item_category_cascade` FOREIGN KEY (`categoryId`) REFERENCES `tbl_checklist_category` (`categoryId`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_employee`
--
ALTER TABLE `tbl_employee`
  ADD CONSTRAINT `fk_employee_location` FOREIGN KEY (`location_id`) REFERENCES `location` (`location_id`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_equipment`
--
ALTER TABLE `tbl_equipment`
  ADD CONSTRAINT `fk_equipment_employee` FOREIGN KEY (`employee_id`) REFERENCES `tbl_employee` (`employeeId`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_equipment_location` FOREIGN KEY (`location_id`) REFERENCES `location` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_equipment_type` FOREIGN KEY (`type_id`) REFERENCES `tbl_equipment_type_registry` (`typeId`);

--
-- Constraints for table `tbl_equipment_specs`
--
ALTER TABLE `tbl_equipment_specs`
  ADD CONSTRAINT `fk_spec_equipment` FOREIGN KEY (`equipment_id`) REFERENCES `tbl_equipment` (`equipment_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_maintenance_record`
--
ALTER TABLE `tbl_maintenance_record`
  ADD CONSTRAINT `fk_history_schedule` FOREIGN KEY (`scheduleId`) REFERENCES `tbl_maintenance_schedule` (`scheduleId`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_maintenance_response`
--
ALTER TABLE `tbl_maintenance_response`
  ADD CONSTRAINT `fk_resp_category` FOREIGN KEY (`categoryId`) REFERENCES `tbl_checklist_category` (`categoryId`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_resp_item` FOREIGN KEY (`itemId`) REFERENCES `tbl_checklist_item` (`itemId`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_resp_record` FOREIGN KEY (`recordId`) REFERENCES `tbl_maintenance_record` (`recordId`) ON DELETE CASCADE;

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
