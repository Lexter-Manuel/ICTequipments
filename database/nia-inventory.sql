-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3310
-- Generation Time: Feb 25, 2026 at 02:35 AM
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
(640, 3, 'inventory@upriis.local', 'UPDATE', 'Settings', 'Updated system settings (4 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 03:36:37'),
(641, 3, 'inventory@upriis.local', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 03:36:53'),
(642, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 03:37:05'),
(643, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported equipment summary report PDF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 03:50:02'),
(644, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported equipment summary report PDF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 04:02:37'),
(645, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 04:03:37'),
(646, 3, 'inventory@upriis.local', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 04:50:54'),
(647, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported equipment summary report PDF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 04:53:17'),
(648, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported equipment summary report PDF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 04:54:56'),
(649, 4, 'markpalacay515@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 04:55:57'),
(650, 3, 'inventory@upriis.local', 'UPDATE', 'Settings', 'Updated system settings (4 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 04:58:18'),
(651, 4, 'markpalacay515@gmail.com', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 04:59:30'),
(652, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: inventory@upriis.local (wrong password)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-24 04:59:41'),
(653, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 04:59:48'),
(654, 3, 'inventory@upriis.local', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:02:25'),
(655, 4, 'markpalacay515@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:02:36'),
(656, 4, 'markpalacay515@gmail.com', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:04:01'),
(657, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:04:17'),
(658, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported equipment summary report PDF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:09:55'),
(659, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported maintenance summary report PDF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:25:48'),
(660, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported maintenance summary report PDF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:26:53'),
(661, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:43:41'),
(662, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (Week 08, 2026 (2026-02-16 – 2026-02-22))', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:45:48'),
(663, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:52:21'),
(664, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:57:42'),
(665, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:58:50'),
(666, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 05:59:32'),
(667, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 06:09:38'),
(668, NULL, 'system', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for Ana G. Garcia (ID: 103)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 06:09:45'),
(669, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 06:14:02'),
(670, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for Ana G. Garcia (ID: 103)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 06:14:04'),
(671, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 06:17:34'),
(672, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for Ana G. Garcia (ID: 103)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 06:17:36'),
(673, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 06:17:58'),
(674, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for Ana G. Garcia (ID: 103)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 06:18:00'),
(675, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 06:32:15'),
(676, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for Ana G. Garcia (ID: 103)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 07:05:23'),
(677, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-02-24 07:13:18'),
(678, 3, 'inventory@upriis.local', 'CREATE', 'Maintenance', 'Recorded maintenance for schedule ID 45 (Equipment ID: 35, Type ID: 3). Status: Operational. Prepared by: Super Admin.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-24 07:14:30'),
(679, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-02-24 08:00:08'),
(680, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: inventory@upriis.local (wrong password)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-25 00:01:47'),
(681, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:01:52'),
(682, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: inventory@upriis.local (wrong password)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 0, '2026-02-25 00:09:40'),
(683, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:09:45'),
(684, 4, 'markpalacay515@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:11:46'),
(685, 4, 'markpalacay515@gmail.com', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:12:07'),
(686, 3, 'inventory@upriis.local', 'CREATE', 'Accounts', 'Created Admin account for Lexter N. Manuel (lexternmanuel@gmail.com), Status: Active.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:12:43'),
(687, 5, 'lexternmanuel@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:13:10'),
(688, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:13:29'),
(689, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:14:03'),
(690, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:14:19'),
(691, 4, 'markpalacay515@gmail.com', 'EXPORT', 'Reports', 'Exported maintenance history report PDF (February 2026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:15:03'),
(692, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported employee checklist report PDF for Demi Xochitl (ID: 645987)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:16:18'),
(693, 3, 'inventory@upriis.local', 'EXPORT', 'Reports', 'Exported checklist report PDF for record #60', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 00:16:27'),
(694, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: inventory@upriis.local (wrong password)', '::1', 'Mozilla/5.0 (Linux; Android 13; RMX3081 Build/RKQ1.211119.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.79 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/548.0.0.37.65;]', 0, '2026-02-25 00:22:48'),
(695, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: inventory@upriis.local (wrong password)', '::1', 'Mozilla/5.0 (Linux; Android 13; RMX3081 Build/RKQ1.211119.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.79 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/548.0.0.37.65;]', 0, '2026-02-25 00:23:04'),
(696, 5, 'lexternmanuel@gmail.com', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-02-25 00:55:19'),
(697, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 01:00:16'),
(698, 5, 'lexternmanuel@gmail.com', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-02-25 01:00:42'),
(699, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-02-25 01:00:55'),
(700, 3, 'inventory@upriis.local', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-02-25 01:01:58'),
(701, NULL, 'system', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for: inventory@upriis.local (wrong password)', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 0, '2026-02-25 01:05:26'),
(702, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', 1, '2026-02-25 01:05:32'),
(703, 4, 'markpalacay515@gmail.com', 'LOGOUT', 'Authentication', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 01:17:10'),
(704, 3, 'inventory@upriis.local', 'LOGIN', 'Authentication', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-02-25 01:17:20');

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
('backup_retention_days', '30', 'system', 'Log Retention (days)', 'Days to retain activity logs before cleanup', '2026-02-24 04:58:18', 3),
('date_format', 'Y-m-d', 'system', 'Date Display Format', 'PHP date format for display', '2026-02-24 04:58:18', 3),
('enable_activity_log', '1', 'system', 'Enable Activity Logging', 'Log user actions in the system', '2026-02-24 04:58:18', 3),
('enforce_2fa', '1', 'security', 'Enforce 2FA', 'Require two-factor authentication for all users', '2026-02-24 02:19:15', 3),
('items_per_page', '10', 'system', 'Default Items Per Page', 'Default pagination size', '2026-02-24 04:58:18', 3),
('lockout_duration', '900', 'security', 'Lockout Duration (seconds)', 'How long account stays locked', '2026-02-24 02:19:15', 3),
('maint_auto_schedule', '1', 'maintenance', 'Auto-Schedule Next', 'Automatically create next schedule after completion', '2026-02-24 01:55:41', 3),
('maint_default_frequency', 'semi-annual', 'maintenance', 'Default Frequency', 'Default maintenance schedule frequency', '2026-02-24 01:55:41', 3),
('maint_overdue_threshold_days', '7', 'maintenance', 'Overdue Threshold (days)', 'Days past due before flagged overdue', '2026-02-24 01:55:41', 3),
('maint_reminder_days_before', '7', 'maintenance', 'Reminder Lead Days', 'Days before due date to show reminders', '2026-02-24 01:55:41', 3),
('max_login_attempts', '5', 'security', 'Max Login Attempts', 'Failed attempts before lockout', '2026-02-24 02:19:15', 3),
('org_address', '', 'organization', 'Office Address', 'Physical address of the office', NULL, NULL),
('org_contact_email', '', 'organization', 'Contact Email', 'Primary contact email', NULL, NULL),
('org_contact_phone', '', 'organization', 'Contact Phone', 'Primary contact phone number', NULL, NULL),
('org_name', 'NIA UPRIIS', 'organization', 'Organization Name', 'Full name of the organization', NULL, NULL),
('org_short_name', 'UPRIIS', 'organization', 'Short Name / Acronym', 'Abbreviated name used in headers', NULL, NULL),
('password_min_length', '8', 'security', 'Minimum Password Length', 'Minimum characters for passwords', '2026-02-24 02:19:15', 3),
('session_timeout', '3600', 'security', 'Session Timeout (seconds)', 'Auto-logout after inactivity', '2026-02-24 02:19:15', 3);

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
(3, 'Super Admin', 'inventory@upriis.local', '$2y$12$.5filGBaRzpga/bgNhvqQ.DtoMDdBWkPZnXwOETP3wG14wx7FfUv2', 'Super Admin', 'Active', 0, NULL, '2026-02-25 09:17:20', '180.191.20.238', 0, NULL, '2026-02-09 23:26:19', '2026-02-25 01:17:20', NULL),
(4, 'Mark Angelo Palacay', 'markpalacay515@gmail.com', '$2y$12$43Qo9uzBsHjQZxiycJVVQu99LkKMrUGjv2AK7lovcYNO0uSg57uAO', 'Admin', 'Active', 0, NULL, '2026-02-25 08:11:46', '180.191.20.238', 0, NULL, '2026-02-24 01:44:20', '2026-02-25 00:11:46', 3),
(5, 'Lexter N. Manuel', 'lexternmanuel@gmail.com', '$2y$12$VEFLi2XvomybiNgm62NOdOeRXUpF2ko.vPjp5dxHZrnDzsCVu/DQK', 'Admin', 'Active', 0, NULL, '2026-02-25 08:55:19', '175.158.198.115', 0, NULL, '2026-02-25 00:12:43', '2026-02-25 00:55:19', 3);

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

--
-- Dumping data for table `tbl_activity_logs`
--

INSERT INTO `tbl_activity_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-20 08:46:32'),
(2, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-20 08:46:39'),
(3, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-20 08:46:50'),
(4, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-20 08:49:03'),
(5, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-20 08:49:24'),
(6, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 08:49:57'),
(7, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 08:50:14'),
(8, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 08:50:21'),
(9, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 08:50:25'),
(10, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 08:50:32'),
(11, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 09:55:19'),
(12, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 13; RMX3081 Build/RKQ1.211119.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/145.0.7632.79 Mobile Safari/537.36 [FB_IAB/FB4A;FBAV/548.0.0.37.65;]', '2026-02-20 10:12:31'),
(13, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 14:13:47'),
(14, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 14:14:22'),
(15, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-20 14:35:34'),
(16, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 15:57:58'),
(17, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 16:48:34'),
(18, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 08:25:29'),
(19, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 10:57:34'),
(20, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 13:01:57'),
(21, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 13:03:13'),
(22, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 13:03:18'),
(23, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 15:33:13'),
(24, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 15:33:19'),
(25, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 08:03:36'),
(26, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 08:04:19'),
(27, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 08:04:25'),
(28, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 08:13:49'),
(29, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 08:40:34'),
(30, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 08:40:40'),
(31, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:12:30'),
(32, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:12:36'),
(33, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-24 09:14:28'),
(34, 3, 'settings_update', 'Updated system settings (4 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:36:39'),
(35, 3, 'settings_update', 'Updated maintenance settings (4 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:37:57'),
(36, 3, 'settings_update', 'Updated maintenance settings (4 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:38:04'),
(37, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:44:37'),
(38, 4, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:44:46'),
(39, 4, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:50:25'),
(40, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:50:49'),
(41, 3, 'settings_update', 'Updated maintenance settings (4 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:55:41'),
(42, 3, 'settings_update', 'Updated system settings (4 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:56:08'),
(43, 3, 'profile_update', 'Changed display name', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:57:13'),
(44, 3, 'password_change', 'Changed account password', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:57:46'),
(45, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:57:50'),
(46, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:58:04'),
(47, 3, 'profile_update', 'Changed display name', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 09:58:13'),
(48, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Mobile Safari/537.36', '2026-02-24 10:10:43'),
(49, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 10:16:01'),
(50, 4, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 10:16:10'),
(51, 4, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 10:16:16'),
(52, 3, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 10:16:34'),
(53, 3, 'settings_update', 'Updated security settings (5 values)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 10:19:15'),
(54, 3, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 10:20:03'),
(55, 4, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 10:20:11');

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

--
-- Dumping data for table `tbl_checklist_category`
--

INSERT INTO `tbl_checklist_category` (`categoryId`, `templateId`, `categoryName`, `sequenceOrder`) VALUES
(1, 2, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(2, 2, 'II. HARDWARE PERFORMANCE CHECK', 2),
(3, 2, 'Untitled', 3),
(4, 3, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(5, 3, 'II. HARDWARE PERFORMANCE CHECK', 2),
(6, 3, 'Untitled', 3),
(7, 4, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(8, 4, 'II. HARDWARE PERFORMANCE CHECK', 2),
(9, 4, 'Untitled', 3),
(19, 8, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(20, 8, 'II. HARDWARE PERFORMANCE CHECK', 2),
(21, 8, 'Untitled', 3),
(25, 10, 'I. PHYSICAL INSPECTION & CLEANING', 1),
(26, 10, 'II. DISPLAY PERFORMANCE CHECK', 2),
(27, 10, 'III. CONNECTIVITY & CABLES', 3),
(28, 11, 'I. PHYSICAL INSPECTION & CLEANING', 1),
(29, 11, 'II. PRINT QUALITY CHECK', 2),
(30, 11, 'III. MECHANICAL & CONSUMABLES', 3),
(31, 12, 'I. PHYSICAL INSPECTION & CLEANING', 1),
(32, 12, 'II. HARDWARE PERFORMANCE CHECK', 2),
(33, 12, 'III. DISPLAY & PERIPHERALS', 3),
(34, 12, 'IV. SOFTWARE & SYSTEM CHECK', 4),
(35, 9, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(36, 9, 'II. HARDWARE PERFORMANCE CHECK', 2),
(37, 9, 'UNTITLED', 3),
(38, 9, 'Untitled', 4),
(39, 13, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 1),
(40, 13, 'II. HARDWARE PERFORMANCE CHECK', 2),
(41, 13, 'Untitled', 3);

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
(19, 19, 'Dust removal performed', 1),
(20, 19, 'Parts are intact', 2),
(21, 20, 'Power Supply is working properly', 1),
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
(35, 27, 'No visible cable damage or fraying', 3),
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
(47, 30, 'Rollers clean and in good condition', 4),
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
(64, 34, 'Essential software applications functional', 4),
(65, 35, 'Dust removal performed', 1),
(66, 35, 'Parts are intact', 2),
(67, 36, 'Power Supply is working properly', 1),
(68, 39, 'Dust removal performed', 1),
(69, 39, 'Parts are intact', 2),
(70, 40, 'Power Supply is working properly', 1);

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
(1, 'Lexter', 'N.', 'Manuel', '', 'OJT Trainee', '2002-11-06', 'Male', 'Casual', 'employee_1_1770881693.jpeg', 4, '2026-02-10 03:41:25', '2026-02-12 07:34:53', 1),
(100, 'Juan', 'P.', 'Dela Cruz', '', 'Project Engineer', '1990-05-15', 'Male', 'Permanent', NULL, 18, '2026-02-19 06:06:48', NULL, 1),
(101, 'Maria', 'S.', 'Santos', '', 'Accountant III', '1988-08-22', 'Female', 'Permanent', NULL, 29, '2026-02-19 06:06:48', NULL, 1),
(102, 'Pedro', 'R.', 'Reyes', '', 'O&M Technician', '1992-03-10', 'Male', 'Permanent', NULL, 19, '2026-02-19 06:06:48', NULL, 1),
(103, 'Ana', 'G.', 'Garcia', '', 'Property Custodian', '1985-11-30', 'Female', 'Permanent', NULL, 11, '2026-02-19 06:06:48', NULL, 1),
(104, 'Carlos', 'M.', 'Lopez', '', 'Legal Officer II', '1991-07-14', 'Male', 'Permanent', NULL, 6, '2026-02-19 06:06:48', NULL, 1),
(105, 'Carmen', 'D.', 'Villar', '', 'Records Officer', '1993-01-25', 'Female', 'Permanent', NULL, 25, '2026-02-19 06:06:48', NULL, 1),
(106, 'Roberto', 'T.', 'Manalo', '', 'Cashier II', '1989-09-03', 'Male', 'Permanent', NULL, 15, '2026-02-19 06:06:48', NULL, 1),
(107, 'Elena', 'C.', 'Ramos', '', 'PR Officer', '1994-04-18', 'Female', 'Permanent', NULL, 5, '2026-02-19 06:06:48', NULL, 1),
(108, 'Miguel', 'A.', 'Torres', '', 'BAC Secretary', '1987-12-08', 'Male', 'Permanent', NULL, 22, '2026-02-19 06:06:48', NULL, 1),
(109, 'Sofia', 'L.', 'Mendoza', '', 'Equipment Mgmt Staff', '1995-06-20', 'Female', 'Permanent', NULL, 20, '2026-02-19 06:06:48', NULL, 1),
(110, 'Jose', 'B.', 'Aquino', '', 'IDS Staff', '1996-02-11', 'Male', 'Casual', NULL, 21, '2026-02-19 06:06:48', NULL, 1),
(111, 'Lucia', 'N.', 'Bautista', '', 'FISA Analyst', '1990-10-05', 'Female', 'Permanent', NULL, 16, '2026-02-19 06:06:48', NULL, 1),
(645987, 'Demi', NULL, 'Xochitl', '', 'OJT Trainee', '2006-02-10', 'Male', 'Casual', 'employee_645987_1771379914.jpeg', 13, '2026-02-11 03:04:45', '2026-02-18 01:58:34', 1);

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
(9, 'Network Storage (NAS)', 'tbl_otherequipment', 'otherEquipmentId', 'equipmentType = \'NAS\'', 180, 'Location', '2026-02-16 03:18:42');

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

--
-- Dumping data for table `tbl_maintenance_record`
--

INSERT INTO `tbl_maintenance_record` (`recordId`, `scheduleId`, `templateId`, `equipmentTypeId`, `equipmentId`, `accountId`, `maintenanceDate`, `checklistJson`, `remarks`, `overallStatus`, `conditionRating`, `preparedBy`, `checkedBy`, `notedBy`) VALUES
(6, 15, NULL, 1, 2, 0, '2026-02-19 13:51:09', '[{\"desc\":\"Dust removal performed\",\"status\":\"Yes\"},{\"desc\":\"Parts are intact\",\"status\":\"No\"},{\"desc\":\"Power Supply is working properly\",\"status\":\"Yes\"}]', '', 'For Replacement', 'Good', 'Current User', 'Template Default', 'Template Default'),
(10, 27, 8, 1, 17, 3, '2026-02-15 10:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"OK\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"Power Supply working\",\"status\":\"OK\"}]}]}', 'All components in excellent condition. Thermal paste refreshed.', 'Operational', 'Excellent', 'Lexter Manuel', 'ICT Head', 'Department Manager'),
(11, 47, 10, 3, 37, 3, '2026-02-15 10:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"},{\"task\":\"No dead pixels\",\"status\":\"OK\"}]}]}', 'Monitor functioning perfectly. No issues found.', 'Operational', 'Excellent', 'Lexter Manuel', 'ICT Head', 'Department Manager'),
(12, 64, 11, 4, 24, 3, '2026-02-15 11:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Exterior clean\",\"status\":\"OK\"},{\"task\":\"Print head aligned\",\"status\":\"OK\"}]}]}', 'Printer in good condition. Ink levels adequate.', 'Operational', 'Good', 'Lexter Manuel', 'ICT Head', 'Department Manager'),
(13, 24, 8, 1, 14, 3, '2026-02-14 09:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"OK\"}]}]}', 'System running smoothly. SSD health at 95%.', 'Operational', 'Excellent', 'Demi Xochitl', 'Legal Head', 'Department Manager'),
(14, 44, 10, 3, 34, 3, '2026-02-14 09:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"}]}]}', 'Monitor in good condition.', 'Operational', 'Good', 'Demi Xochitl', 'Legal Head', 'Department Manager'),
(15, 32, 8, 1, 22, 3, '2026-02-14 14:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"RAM test passed\",\"status\":\"OK\"}]}]}', 'FISA system unit cleaned and tested. All clear.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),
(16, 52, 10, 3, 42, 3, '2026-02-14 14:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"}]}]}', 'Monitor working well. Slight color shift noted but acceptable.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),
(17, 21, 8, 1, 11, 3, '2026-02-12 09:15:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"OK\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"Power Supply working\",\"status\":\"OK\"},{\"task\":\"Fan noise check\",\"status\":\"OK\"}]}]}', 'System unit thoroughly cleaned. Fan replaced due to noise.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),
(18, 41, 10, 3, 31, 3, '2026-02-12 09:45:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"},{\"task\":\"No dead pixels\",\"status\":\"OK\"}]}]}', 'Monitor in excellent condition.', 'Operational', 'Excellent', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),
(19, 61, 11, 4, 21, 3, '2026-02-12 10:15:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Exterior clean\",\"status\":\"OK\"}]},{\"name\":\"Print Quality\",\"items\":[{\"task\":\"Test page printed\",\"status\":\"OK\"}]}]}', 'LaserJet printer functioning well. Toner at 60%.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),
(20, 20, NULL, 1, 10, 3, '2026-02-10 10:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"Minor Issue\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"Power Supply working\",\"status\":\"OK\"}]}]}', 'Minor dust buildup inside. RAM slot 2 slightly loose - reseated.', 'Operational', 'Fair', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager'),
(21, 40, NULL, 3, 30, 3, '2026-02-10 10:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"}]}]}', 'Monitor OK. Slight backlight bleed on lower-left corner.', 'Operational', 'Good', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager'),
(22, 60, NULL, 4, 20, 3, '2026-02-10 11:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Exterior clean\",\"status\":\"OK\"}]},{\"name\":\"Print Quality\",\"items\":[{\"task\":\"Nozzle check\",\"status\":\"Minor Issue\"}]}]}', 'Print head cleaned. Nozzle check showed minor clog - resolved after cleaning cycle.', 'Operational', 'Fair', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager'),
(23, 22, NULL, 1, 12, 3, '2026-02-08 08:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"OK\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"Power Supply working\",\"status\":\"OK\"}]}]}', 'System unit in good condition. Disk health 92%.', 'Operational', 'Good', 'Demi Xochitl', 'Operation Section Head', 'EOD Manager'),
(24, 42, NULL, 3, 32, 3, '2026-02-08 09:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"},{\"task\":\"No dead pixels\",\"status\":\"OK\"}]}]}', 'Monitor excellent. Calibrated for optimal display.', 'Operational', 'Excellent', 'Demi Xochitl', 'Operation Section Head', 'EOD Manager'),
(25, 28, NULL, 1, 18, 3, '2026-02-06 13:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"Power Supply working\",\"status\":\"OK\"}]}]}', 'PR system unit cleaned successfully.', 'Operational', 'Good', 'Lexter Manuel', 'ODM Head', 'Department Manager'),
(26, 48, NULL, 3, 38, 3, '2026-02-06 13:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"}]}]}', 'Monitor in good working condition.', 'Operational', 'Good', 'Lexter Manuel', 'ODM Head', 'Department Manager'),
(27, 65, NULL, 4, 25, 3, '2026-02-06 14:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Exterior clean\",\"status\":\"OK\"}]},{\"name\":\"Print Quality\",\"items\":[{\"task\":\"Test page\",\"status\":\"OK\"}]}]}', 'Brother printer working well. Paper feed smooth.', 'Operational', 'Excellent', 'Lexter Manuel', 'ODM Head', 'Department Manager'),
(28, 26, NULL, 1, 16, 3, '2026-02-03 09:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"OK\"}]}]}', 'Cashiering PC cleaned. Runs well for daily operations.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),
(29, 46, NULL, 3, 36, 3, '2026-02-03 09:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"}]}]}', 'Monitor functioning normally.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),
(30, 23, NULL, 1, 13, 3, '2026-02-01 10:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"Minor Issue\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"HDD health check\",\"status\":\"Warning\"}]}]}', 'HDD showing early signs of degradation (87% health). Recommended SSD upgrade within 6 months.', 'Operational', 'Fair', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),
(31, 43, NULL, 3, 33, 3, '2026-02-01 10:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"}]}]}', 'Monitor working fine.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),
(32, 62, NULL, 4, 22, 3, '2026-02-01 11:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Exterior clean\",\"status\":\"OK\"}]},{\"name\":\"Print Quality\",\"items\":[{\"task\":\"Ink levels checked\",\"status\":\"OK\"}]}]}', 'Canon printer cleaned. Ink levels at 70%.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),
(33, 29, NULL, 1, 19, 3, '2026-01-30 09:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"OK\"}]}]}', 'BAC system unit in excellent shape.', 'Operational', 'Excellent', 'Demi Xochitl', 'ODM Head', 'Department Manager'),
(34, 49, NULL, 3, 39, 3, '2026-01-30 09:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"},{\"task\":\"No dead pixels\",\"status\":\"OK\"}]}]}', 'HP monitor excellent. No issues.', 'Operational', 'Excellent', 'Demi Xochitl', 'ODM Head', 'Department Manager'),
(35, 66, NULL, 4, 26, 3, '2026-01-30 14:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Exterior clean\",\"status\":\"OK\"}]},{\"name\":\"Print Quality\",\"items\":[{\"task\":\"Test page\",\"status\":\"Minor Issue\"}]}]}', 'Epson printer showing slight banding. Cleaned print heads.', 'Operational', 'Fair', 'Lexter Manuel', 'ODM Head', 'Department Manager'),
(36, 25, NULL, 1, 15, 3, '2026-01-28 08:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"OK\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"Power Supply working\",\"status\":\"OK\"},{\"task\":\"SSD health\",\"status\":\"OK\"}]}]}', 'Records PC in excellent condition. SSD health at 98%.', 'Operational', 'Excellent', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),
(37, 45, NULL, 3, 35, 3, '2026-01-28 09:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"}]}]}', 'Older monitor but still performing well. Minor backlight aging.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),
(38, 63, NULL, 4, 23, 3, '2026-01-28 09:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Exterior clean\",\"status\":\"OK\"}]},{\"name\":\"Print Quality\",\"items\":[{\"task\":\"Ink levels\",\"status\":\"OK\"},{\"task\":\"Feed mechanism\",\"status\":\"OK\"}]}]}', 'Epson L5290 in good condition. Ink refilled.', 'Operational', 'Good', 'Lexter Manuel', 'Admin Section Head', 'Department Manager'),
(39, 30, NULL, 1, 20, 3, '2026-01-20 10:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"},{\"task\":\"Parts are intact\",\"status\":\"OK\"}]}]}', 'Equipment Management PC cleaned and tested.', 'Operational', 'Good', 'Demi Xochitl', 'Engineering Section Head', 'EOD Manager'),
(40, 50, NULL, 3, 40, 3, '2026-01-20 10:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"}]}]}', 'Monitor in good shape.', 'Operational', 'Good', 'Demi Xochitl', 'Engineering Section Head', 'EOD Manager'),
(41, 31, NULL, 1, 21, 3, '2026-01-20 13:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal performed\",\"status\":\"OK\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"Power Supply working\",\"status\":\"OK\"}]}]}', 'IDS PC maintained successfully.', 'Operational', 'Excellent', 'Demi Xochitl', 'Engineering Section Head', 'EOD Manager'),
(42, 51, NULL, 3, 41, 3, '2026-01-20 13:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Screen clean\",\"status\":\"OK\"}]}]}', 'Monitor working well.', 'Operational', 'Good', 'Demi Xochitl', 'Engineering Section Head', 'EOD Manager'),
(43, 70, NULL, 2, 2, 3, '2026-01-15 10:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Exterior clean\",\"status\":\"OK\"},{\"task\":\"All-in-one internals\",\"status\":\"OK\"}]}]}', 'All-in-One PC cleaned. Good performance.', 'Operational', 'Good', 'Demi Xochitl', 'ICT Head', 'Department Manager'),
(44, 20, NULL, 1, 10, 3, '2025-12-20 09:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal\",\"status\":\"OK\"}]}]}', 'Quarterly check. System unit OK.', 'Operational', 'Good', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager'),
(45, 22, NULL, 1, 12, 3, '2025-12-20 10:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal\",\"status\":\"OK\"}]}]}', 'Operation Section PC quarterly maintenance done.', 'Operational', 'Excellent', 'Lexter Manuel', 'Operation Section Head', 'EOD Manager'),
(46, 23, NULL, 1, 13, 3, '2025-12-15 14:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal\",\"status\":\"OK\"},{\"task\":\"Parts intact\",\"status\":\"Minor Issue\"}]}]}', 'Property Unit PC - noted HDD starting to slow down.', 'Operational', 'Fair', 'Demi Xochitl', 'Admin Section Head', 'Department Manager'),
(47, 21, NULL, 1, 11, 3, '2025-12-10 09:30:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal\",\"status\":\"OK\"}]}]}', 'Accounting PC quarterly maintenance complete.', 'Operational', 'Good', 'Demi Xochitl', 'Finance Section Head', 'Department Manager'),
(48, 30, NULL, 1, 20, 3, '2025-11-15 10:00:00', '{\"categories\":[{\"name\":\"Physical Inspection\",\"items\":[{\"task\":\"Dust removal\",\"status\":\"OK\"},{\"task\":\"Parts intact\",\"status\":\"FAIL\"}]},{\"name\":\"Hardware Check\",\"items\":[{\"task\":\"Power Supply\",\"status\":\"Warning\"}]}]}', 'PSU showing intermittent issues. Recommended for replacement if budget allows.', 'Operational', 'Poor', 'Lexter Manuel', 'Engineering Section Head', 'EOD Manager'),
(54, 27, 9, 1, 17, 3, '2026-02-20 16:01:20', '[{\"desc\":\"Dust removal performed\",\"itemId\":\"65\",\"categoryId\":\"35\",\"categoryName\":\"I. PHYSICAL INSPECTION, INTERIORS AND CLEANING\",\"seq\":\"1\",\"status\":\"Yes\"},{\"desc\":\"Parts are intact\",\"itemId\":\"66\",\"categoryId\":\"35\",\"categoryName\":\"I. PHYSICAL INSPECTION, INTERIORS AND CLEANING\",\"seq\":\"2\",\"status\":\"No\"},{\"desc\":\"Power Supply is working properly\",\"itemId\":\"67\",\"categoryId\":\"36\",\"categoryName\":\"II. HARDWARE PERFORMANCE CHECK\",\"seq\":\"3\",\"status\":\"N\\/A\"}]', 'TESTING LANG PU', 'For Replacement', 'Good', 'SystemSuperAdmin', 'TEST', '[Select Head of Office]'),
(55, 50, 13, 3, 40, 3, '2026-02-20 16:06:53', '[{\"desc\":\"Dust removal performed\",\"itemId\":\"68\",\"categoryId\":\"39\",\"categoryName\":\"I. PHYSICAL INSPECTION, INTERIORS AND CLEANING\",\"seq\":\"1\",\"status\":\"Yes\"},{\"desc\":\"Parts are intact\",\"itemId\":\"69\",\"categoryId\":\"39\",\"categoryName\":\"I. PHYSICAL INSPECTION, INTERIORS AND CLEANING\",\"seq\":\"2\",\"status\":\"Yes\"},{\"desc\":\"Power Supply is working properly\",\"itemId\":\"70\",\"categoryId\":\"40\",\"categoryName\":\"II. HARDWARE PERFORMANCE CHECK\",\"seq\":\"3\",\"status\":\"Yes\"}]', '', 'Operational', 'Good', 'SystemSuperAdmin', '[Select Supervisor Name]', '[Select Head of Office]'),
(56, 23, 8, 1, 13, 3, '2026-02-23 14:30:23', '[{\"desc\":\"Dust removal performed\",\"itemId\":\"19\",\"categoryId\":\"19\",\"categoryName\":\"I. PHYSICAL INSPECTION, INTERIORS AND CLEANING\",\"seq\":\"1\",\"status\":\"Yes\"},{\"desc\":\"Parts are intact\",\"itemId\":\"20\",\"categoryId\":\"19\",\"categoryName\":\"I. PHYSICAL INSPECTION, INTERIORS AND CLEANING\",\"seq\":\"2\",\"status\":\"No\"},{\"desc\":\"Power Supply is working properly\",\"itemId\":\"21\",\"categoryId\":\"20\",\"categoryName\":\"II. HARDWARE PERFORMANCE CHECK\",\"seq\":\"3\",\"status\":\"No\"}]', '', 'For Replacement', 'Good', 'SystemSuperAdmin', '[Select Supervisor Name]', '[Select Head of Office]'),
(57, 43, 10, 3, 33, 3, '2026-02-23 14:30:39', '[{\"desc\":\"Screen surface cleaned (no smudges\\/dust)\",\"itemId\":\"25\",\"categoryId\":\"25\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"1\",\"status\":\"Yes\"},{\"desc\":\"Monitor casing wiped and free of dust\",\"itemId\":\"26\",\"categoryId\":\"25\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"2\",\"status\":\"Yes\"},{\"desc\":\"Ventilation openings clear of obstruction\",\"itemId\":\"27\",\"categoryId\":\"25\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"3\",\"status\":\"Yes\"},{\"desc\":\"Stand\\/mount secure and stable\",\"itemId\":\"28\",\"categoryId\":\"25\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"4\",\"status\":\"Yes\"},{\"desc\":\"No dead or stuck pixels detected\",\"itemId\":\"29\",\"categoryId\":\"26\",\"categoryName\":\"II. DISPLAY PERFORMANCE CHECK\",\"seq\":\"5\",\"status\":\"No\"},{\"desc\":\"Brightness and contrast levels acceptable\",\"itemId\":\"30\",\"categoryId\":\"26\",\"categoryName\":\"II. DISPLAY PERFORMANCE CHECK\",\"seq\":\"6\",\"status\":\"No\"},{\"desc\":\"Color reproduction is accurate\",\"itemId\":\"31\",\"categoryId\":\"26\",\"categoryName\":\"II. DISPLAY PERFORMANCE CHECK\",\"seq\":\"7\",\"status\":\"Yes\"},{\"desc\":\"No visible backlight bleed or flickering\",\"itemId\":\"32\",\"categoryId\":\"26\",\"categoryName\":\"II. DISPLAY PERFORMANCE CHECK\",\"seq\":\"8\",\"status\":\"Yes\"},{\"desc\":\"Power cable securely connected\",\"itemId\":\"33\",\"categoryId\":\"27\",\"categoryName\":\"III. CONNECTIVITY & CABLES\",\"seq\":\"9\",\"status\":\"N\\/A\"},{\"desc\":\"Video cable (HDMI\\/VGA\\/DP) securely connected\",\"itemId\":\"34\",\"categoryId\":\"27\",\"categoryName\":\"III. CONNECTIVITY & CABLES\",\"seq\":\"10\",\"status\":\"N\\/A\"},{\"desc\":\"No visible cable damage or fraying\",\"itemId\":\"35\",\"categoryId\":\"27\",\"categoryName\":\"III. CONNECTIVITY & CABLES\",\"seq\":\"11\",\"status\":\"N\\/A\"}]', '', 'For Replacement', 'Good', 'SystemSuperAdmin', '[Select Supervisor Name]', '[Select Head of Office]'),
(58, 62, 11, 4, 22, 3, '2026-02-23 14:30:53', '[{\"desc\":\"Exterior casing cleaned and free of dust\",\"itemId\":\"36\",\"categoryId\":\"28\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"1\",\"status\":\"Yes\"},{\"desc\":\"Paper tray clean and properly aligned\",\"itemId\":\"37\",\"categoryId\":\"28\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"2\",\"status\":\"Yes\"},{\"desc\":\"Ventilation openings clear of obstruction\",\"itemId\":\"38\",\"categoryId\":\"28\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"3\",\"status\":\"Yes\"},{\"desc\":\"No paper debris inside printer\",\"itemId\":\"39\",\"categoryId\":\"28\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"4\",\"status\":\"Yes\"},{\"desc\":\"Test page printed successfully\",\"itemId\":\"40\",\"categoryId\":\"29\",\"categoryName\":\"II. PRINT QUALITY CHECK\",\"seq\":\"5\",\"status\":\"Yes\"},{\"desc\":\"Print alignment is correct\",\"itemId\":\"41\",\"categoryId\":\"29\",\"categoryName\":\"II. PRINT QUALITY CHECK\",\"seq\":\"6\",\"status\":\"Yes\"},{\"desc\":\"No streaks, smudges, or banding on output\",\"itemId\":\"42\",\"categoryId\":\"29\",\"categoryName\":\"II. PRINT QUALITY CHECK\",\"seq\":\"7\",\"status\":\"No\"},{\"desc\":\"Color output accurate (if color printer)\",\"itemId\":\"43\",\"categoryId\":\"29\",\"categoryName\":\"II. PRINT QUALITY CHECK\",\"seq\":\"8\",\"status\":\"No\"},{\"desc\":\"Ink\\/toner levels checked and adequate\",\"itemId\":\"44\",\"categoryId\":\"30\",\"categoryName\":\"III. MECHANICAL & CONSUMABLES\",\"seq\":\"9\",\"status\":\"No\"},{\"desc\":\"Paper feed mechanism operates smoothly\",\"itemId\":\"45\",\"categoryId\":\"30\",\"categoryName\":\"III. MECHANICAL & CONSUMABLES\",\"seq\":\"10\",\"status\":\"Yes\"},{\"desc\":\"Print head cleaned (inkjet) or drum inspected (laser)\",\"itemId\":\"46\",\"categoryId\":\"30\",\"categoryName\":\"III. MECHANICAL & CONSUMABLES\",\"seq\":\"11\",\"status\":\"Yes\"},{\"desc\":\"Rollers clean and in good condition\",\"itemId\":\"47\",\"categoryId\":\"30\",\"categoryName\":\"III. MECHANICAL & CONSUMABLES\",\"seq\":\"12\",\"status\":\"Yes\"}]', '', 'For Replacement', 'Good', 'SystemSuperAdmin', '[Select Supervisor Name]', '[Select Head of Office]'),
(59, 25, 9, 1, 15, 3, '2026-02-23 15:54:36', '[{\"desc\":\"Dust removal performed\",\"itemId\":\"65\",\"categoryId\":\"35\",\"categoryName\":\"I. PHYSICAL INSPECTION, INTERIORS AND CLEANING\",\"seq\":\"1\",\"status\":\"Yes\"},{\"desc\":\"Parts are intact\",\"itemId\":\"66\",\"categoryId\":\"35\",\"categoryName\":\"I. PHYSICAL INSPECTION, INTERIORS AND CLEANING\",\"seq\":\"2\",\"status\":\"Yes\"},{\"desc\":\"Power Supply is working properly\",\"itemId\":\"67\",\"categoryId\":\"36\",\"categoryName\":\"II. HARDWARE PERFORMANCE CHECK\",\"seq\":\"3\",\"status\":\"Yes\"}]', '', 'Operational', 'Good', 'SystemSuperAdmin', 'TEST', '[Select Head of Office]'),
(60, 45, 10, 3, 35, 3, '2026-02-24 15:14:30', '[{\"desc\":\"Screen surface cleaned (no smudges\\/dust)\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"1\",\"status\":\"Yes\"},{\"desc\":\"Monitor casing wiped and free of dust\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"2\",\"status\":\"Yes\"},{\"desc\":\"Ventilation openings clear of obstruction\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"3\",\"status\":\"Yes\"},{\"desc\":\"Stand\\/mount secure and stable\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"I. PHYSICAL INSPECTION & CLEANING\",\"seq\":\"4\",\"status\":\"Yes\"},{\"desc\":\"No dead or stuck pixels detected\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"II. DISPLAY PERFORMANCE CHECK\",\"seq\":\"5\",\"status\":\"Yes\"},{\"desc\":\"Brightness and contrast levels acceptable\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"II. DISPLAY PERFORMANCE CHECK\",\"seq\":\"6\",\"status\":\"Yes\"},{\"desc\":\"Color reproduction is accurate\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"II. DISPLAY PERFORMANCE CHECK\",\"seq\":\"7\",\"status\":\"Yes\"},{\"desc\":\"No visible backlight bleed or flickering\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"II. DISPLAY PERFORMANCE CHECK\",\"seq\":\"8\",\"status\":\"Yes\"},{\"desc\":\"Power cable securely connected\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"III. CONNECTIVITY & CABLES\",\"seq\":\"9\",\"status\":\"Yes\"},{\"desc\":\"Video cable (HDMI\\/VGA\\/DP) securely connected\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"III. CONNECTIVITY & CABLES\",\"seq\":\"10\",\"status\":\"Yes\"},{\"desc\":\"No visible cable damage or fraying\",\"itemId\":\"0\",\"categoryId\":\"0\",\"categoryName\":\"III. CONNECTIVITY & CABLES\",\"seq\":\"11\",\"status\":\"Yes\"}]', 'dasfsasafasdas', 'Operational', 'Good', 'Super Admin', '[Select Supervisor Name]', '[Select Head of Office]');

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

--
-- Dumping data for table `tbl_maintenance_response`
--

INSERT INTO `tbl_maintenance_response` (`responseId`, `recordId`, `itemId`, `categoryId`, `categoryName`, `taskDescription`, `response`, `sequenceOrder`) VALUES
(1, 10, 19, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed', 'Yes', 1),
(2, 10, 20, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact', 'Yes', 2),
(3, 10, 21, 20, 'II. HARDWARE PERFORMANCE CHECK', 'Power Supply is working properly', 'Yes', 3),
(4, 11, 25, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Screen surface cleaned (no smudges/dust)', 'Yes', 1),
(5, 11, 26, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Monitor casing wiped and free of dust', 'Yes', 2),
(6, 11, 27, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Ventilation openings clear of obstruction', 'Yes', 3),
(7, 11, 28, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Stand/mount secure and stable', 'Yes', 4),
(8, 11, 29, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No dead or stuck pixels detected', 'Yes', 5),
(9, 11, 30, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Brightness and contrast levels acceptable', 'Yes', 6),
(10, 11, 31, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Color reproduction is accurate', 'Yes', 7),
(11, 11, 32, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No visible backlight bleed or flickering', 'Yes', 8),
(12, 11, 33, 27, 'III. CONNECTIVITY & CABLES', 'Power cable securely connected', 'Yes', 9),
(13, 11, 34, 27, 'III. CONNECTIVITY & CABLES', 'Video cable (HDMI/VGA/DP) securely connected', 'Yes', 10),
(14, 11, 35, 27, 'III. CONNECTIVITY & CABLES', 'No visible cable damage or fraying', 'Yes', 11),
(15, 12, 36, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'Exterior casing cleaned and free of dust', 'Yes', 1),
(16, 12, 37, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'Paper tray clean and properly aligned', 'Yes', 2),
(17, 12, 38, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'Ventilation openings clear of obstruction', 'Yes', 3),
(18, 12, 39, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'No paper debris inside printer', 'Yes', 4),
(19, 12, 40, 29, 'II. PRINT QUALITY CHECK', 'Test page printed successfully', 'Yes', 5),
(20, 12, 41, 29, 'II. PRINT QUALITY CHECK', 'Print alignment is correct', 'Yes', 6),
(21, 12, 42, 29, 'II. PRINT QUALITY CHECK', 'No streaks, smudges, or banding on output', 'Yes', 7),
(22, 12, 43, 29, 'II. PRINT QUALITY CHECK', 'Color output accurate (if color printer)', 'N/A', 8),
(23, 12, 44, 30, 'III. MECHANICAL & CONSUMABLES', 'Ink/toner levels checked and adequate', 'Yes', 9),
(24, 12, 45, 30, 'III. MECHANICAL & CONSUMABLES', 'Paper feed mechanism operates smoothly', 'Yes', 10),
(25, 12, 46, 30, 'III. MECHANICAL & CONSUMABLES', 'Print head cleaned (inkjet) or drum inspected (laser)', 'Yes', 11),
(26, 12, 47, 30, 'III. MECHANICAL & CONSUMABLES', 'Rollers clean and in good condition', 'Yes', 12),
(27, 13, 19, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed', 'Yes', 1),
(28, 13, 20, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact', 'Yes', 2),
(29, 13, 21, 20, 'II. HARDWARE PERFORMANCE CHECK', 'Power Supply is working properly', 'Yes', 3),
(30, 14, 25, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Screen surface cleaned (no smudges/dust)', 'Yes', 1),
(31, 14, 26, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Monitor casing wiped and free of dust', 'Yes', 2),
(32, 14, 27, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Ventilation openings clear of obstruction', 'Yes', 3),
(33, 14, 28, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Stand/mount secure and stable', 'Yes', 4),
(34, 14, 29, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No dead or stuck pixels detected', 'Yes', 5),
(35, 14, 30, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Brightness and contrast levels acceptable', 'Yes', 6),
(36, 14, 31, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Color reproduction is accurate', 'N/A', 7),
(37, 14, 32, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No visible backlight bleed or flickering', 'Yes', 8),
(38, 14, 33, 27, 'III. CONNECTIVITY & CABLES', 'Power cable securely connected', 'Yes', 9),
(39, 14, 34, 27, 'III. CONNECTIVITY & CABLES', 'Video cable (HDMI/VGA/DP) securely connected', 'Yes', 10),
(40, 14, 35, 27, 'III. CONNECTIVITY & CABLES', 'No visible cable damage or fraying', 'Yes', 11),
(41, 15, 19, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed', 'Yes', 1),
(42, 15, 20, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact', 'Yes', 2),
(43, 15, 21, 20, 'II. HARDWARE PERFORMANCE CHECK', 'Power Supply is working properly', 'Yes', 3),
(44, 16, 25, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Screen surface cleaned (no smudges/dust)', 'Yes', 1),
(45, 16, 26, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Monitor casing wiped and free of dust', 'Yes', 2),
(46, 16, 27, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Ventilation openings clear of obstruction', 'Yes', 3),
(47, 16, 28, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Stand/mount secure and stable', 'Yes', 4),
(48, 16, 29, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No dead or stuck pixels detected', 'Yes', 5),
(49, 16, 30, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Brightness and contrast levels acceptable', 'Yes', 6),
(50, 16, 31, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Color reproduction is accurate', 'No', 7),
(51, 16, 32, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No visible backlight bleed or flickering', 'Yes', 8),
(52, 16, 33, 27, 'III. CONNECTIVITY & CABLES', 'Power cable securely connected', 'Yes', 9),
(53, 16, 34, 27, 'III. CONNECTIVITY & CABLES', 'Video cable (HDMI/VGA/DP) securely connected', 'Yes', 10),
(54, 16, 35, 27, 'III. CONNECTIVITY & CABLES', 'No visible cable damage or fraying', 'Yes', 11),
(55, 17, 19, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed', 'Yes', 1),
(56, 17, 20, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact', 'No', 2),
(57, 17, 21, 20, 'II. HARDWARE PERFORMANCE CHECK', 'Power Supply is working properly', 'Yes', 3),
(58, 18, 25, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Screen surface cleaned (no smudges/dust)', 'Yes', 1),
(59, 18, 26, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Monitor casing wiped and free of dust', 'Yes', 2),
(60, 18, 27, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Ventilation openings clear of obstruction', 'Yes', 3),
(61, 18, 28, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Stand/mount secure and stable', 'Yes', 4),
(62, 18, 29, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No dead or stuck pixels detected', 'Yes', 5),
(63, 18, 30, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Brightness and contrast levels acceptable', 'Yes', 6),
(64, 18, 31, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Color reproduction is accurate', 'Yes', 7),
(65, 18, 32, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No visible backlight bleed or flickering', 'Yes', 8),
(66, 18, 33, 27, 'III. CONNECTIVITY & CABLES', 'Power cable securely connected', 'Yes', 9),
(67, 18, 34, 27, 'III. CONNECTIVITY & CABLES', 'Video cable (HDMI/VGA/DP) securely connected', 'Yes', 10),
(68, 18, 35, 27, 'III. CONNECTIVITY & CABLES', 'No visible cable damage or fraying', 'Yes', 11),
(69, 19, 36, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'Exterior casing cleaned and free of dust', 'Yes', 1),
(70, 19, 37, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'Paper tray clean and properly aligned', 'Yes', 2),
(71, 19, 38, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'Ventilation openings clear of obstruction', 'Yes', 3),
(72, 19, 39, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'No paper debris inside printer', 'Yes', 4),
(73, 19, 40, 29, 'II. PRINT QUALITY CHECK', 'Test page printed successfully', 'Yes', 5),
(74, 19, 41, 29, 'II. PRINT QUALITY CHECK', 'Print alignment is correct', 'Yes', 6),
(75, 19, 42, 29, 'II. PRINT QUALITY CHECK', 'No streaks, smudges, or banding on output', 'No', 7),
(76, 19, 43, 29, 'II. PRINT QUALITY CHECK', 'Color output accurate (if color printer)', 'N/A', 8),
(77, 19, 44, 30, 'III. MECHANICAL & CONSUMABLES', 'Ink/toner levels checked and adequate', 'Yes', 9),
(78, 19, 45, 30, 'III. MECHANICAL & CONSUMABLES', 'Paper feed mechanism operates smoothly', 'Yes', 10),
(79, 19, 46, 30, 'III. MECHANICAL & CONSUMABLES', 'Print head cleaned (inkjet) or drum inspected (laser)', 'Yes', 11),
(80, 19, 47, 30, 'III. MECHANICAL & CONSUMABLES', 'Rollers clean and in good condition', 'Yes', 12),
(81, 54, 65, 35, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed', 'Yes', 1),
(82, 54, 66, 35, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact', 'No', 2),
(83, 54, 67, 36, 'II. HARDWARE PERFORMANCE CHECK', 'Power Supply is working properly', 'N/A', 3),
(84, 55, 68, 39, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed', 'Yes', 1),
(85, 55, 69, 39, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact', 'Yes', 2),
(86, 55, 70, 40, 'II. HARDWARE PERFORMANCE CHECK', 'Power Supply is working properly', 'Yes', 3),
(87, 56, 19, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed', 'Yes', 1),
(88, 56, 20, 19, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact', 'No', 2),
(89, 56, 21, 20, 'II. HARDWARE PERFORMANCE CHECK', 'Power Supply is working properly', 'No', 3),
(90, 57, 25, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Screen surface cleaned (no smudges/dust)', 'Yes', 1),
(91, 57, 26, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Monitor casing wiped and free of dust', 'Yes', 2),
(92, 57, 27, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Ventilation openings clear of obstruction', 'Yes', 3),
(93, 57, 28, 25, 'I. PHYSICAL INSPECTION & CLEANING', 'Stand/mount secure and stable', 'Yes', 4),
(94, 57, 29, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No dead or stuck pixels detected', 'No', 5),
(95, 57, 30, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Brightness and contrast levels acceptable', 'No', 6),
(96, 57, 31, 26, 'II. DISPLAY PERFORMANCE CHECK', 'Color reproduction is accurate', 'Yes', 7),
(97, 57, 32, 26, 'II. DISPLAY PERFORMANCE CHECK', 'No visible backlight bleed or flickering', 'Yes', 8),
(98, 57, 33, 27, 'III. CONNECTIVITY & CABLES', 'Power cable securely connected', 'N/A', 9),
(99, 57, 34, 27, 'III. CONNECTIVITY & CABLES', 'Video cable (HDMI/VGA/DP) securely connected', 'N/A', 10),
(100, 57, 35, 27, 'III. CONNECTIVITY & CABLES', 'No visible cable damage or fraying', 'N/A', 11),
(101, 58, 36, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'Exterior casing cleaned and free of dust', 'Yes', 1),
(102, 58, 37, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'Paper tray clean and properly aligned', 'Yes', 2),
(103, 58, 38, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'Ventilation openings clear of obstruction', 'Yes', 3),
(104, 58, 39, 28, 'I. PHYSICAL INSPECTION & CLEANING', 'No paper debris inside printer', 'Yes', 4),
(105, 58, 40, 29, 'II. PRINT QUALITY CHECK', 'Test page printed successfully', 'Yes', 5),
(106, 58, 41, 29, 'II. PRINT QUALITY CHECK', 'Print alignment is correct', 'Yes', 6),
(107, 58, 42, 29, 'II. PRINT QUALITY CHECK', 'No streaks, smudges, or banding on output', 'No', 7),
(108, 58, 43, 29, 'II. PRINT QUALITY CHECK', 'Color output accurate (if color printer)', 'No', 8),
(109, 58, 44, 30, 'III. MECHANICAL & CONSUMABLES', 'Ink/toner levels checked and adequate', 'No', 9),
(110, 58, 45, 30, 'III. MECHANICAL & CONSUMABLES', 'Paper feed mechanism operates smoothly', 'Yes', 10),
(111, 58, 46, 30, 'III. MECHANICAL & CONSUMABLES', 'Print head cleaned (inkjet) or drum inspected (laser)', 'Yes', 11),
(112, 58, 47, 30, 'III. MECHANICAL & CONSUMABLES', 'Rollers clean and in good condition', 'Yes', 12),
(113, 59, 65, 35, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Dust removal performed', 'Yes', 1),
(114, 59, 66, 35, 'I. PHYSICAL INSPECTION, INTERIORS AND CLEANING', 'Parts are intact', 'Yes', 2),
(115, 59, 67, 36, 'II. HARDWARE PERFORMANCE CHECK', 'Power Supply is working properly', 'Yes', 3),
(116, 60, NULL, NULL, 'I. PHYSICAL INSPECTION & CLEANING', 'Screen surface cleaned (no smudges/dust)', 'Yes', 1),
(117, 60, NULL, NULL, 'I. PHYSICAL INSPECTION & CLEANING', 'Monitor casing wiped and free of dust', 'Yes', 2),
(118, 60, NULL, NULL, 'I. PHYSICAL INSPECTION & CLEANING', 'Ventilation openings clear of obstruction', 'Yes', 3),
(119, 60, NULL, NULL, 'I. PHYSICAL INSPECTION & CLEANING', 'Stand/mount secure and stable', 'Yes', 4),
(120, 60, NULL, NULL, 'II. DISPLAY PERFORMANCE CHECK', 'No dead or stuck pixels detected', 'Yes', 5),
(121, 60, NULL, NULL, 'II. DISPLAY PERFORMANCE CHECK', 'Brightness and contrast levels acceptable', 'Yes', 6),
(122, 60, NULL, NULL, 'II. DISPLAY PERFORMANCE CHECK', 'Color reproduction is accurate', 'Yes', 7),
(123, 60, NULL, NULL, 'II. DISPLAY PERFORMANCE CHECK', 'No visible backlight bleed or flickering', 'Yes', 8),
(124, 60, NULL, NULL, 'III. CONNECTIVITY & CABLES', 'Power cable securely connected', 'Yes', 9),
(125, 60, NULL, NULL, 'III. CONNECTIVITY & CABLES', 'Video cable (HDMI/VGA/DP) securely connected', 'Yes', 10),
(126, 60, NULL, NULL, 'III. CONNECTIVITY & CABLES', 'No visible cable damage or fraying', 'Yes', 11);

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

--
-- Dumping data for table `tbl_maintenance_schedule`
--

INSERT INTO `tbl_maintenance_schedule` (`scheduleId`, `equipmentType`, `equipmentId`, `maintenanceFrequency`, `lastMaintenanceDate`, `nextDueDate`, `isActive`, `createdAt`, `updatedAt`) VALUES
(2, '3', 15, '', NULL, '2026-08-15', 0, '2026-02-16 03:51:54', '2026-02-19 07:23:15'),
(3, '3', 16, '', NULL, '2026-08-15', 0, '2026-02-16 03:52:10', '2026-02-19 07:23:15'),
(4, '3', 17, '', NULL, '2026-08-15', 0, '2026-02-16 03:54:21', '2026-02-19 07:23:15'),
(5, '3', 18, '', NULL, '2026-08-15', 0, '2026-02-16 03:55:19', '2026-02-19 07:23:15'),
(6, '3', 19, '', NULL, '2026-08-15', 0, '2026-02-16 03:58:17', '2026-02-19 07:23:15'),
(7, '4', 4, '', NULL, '2026-08-15', 0, '2026-02-16 04:31:34', '2026-02-19 07:23:15'),
(8, '4', 5, '', NULL, '2026-08-15', 0, '2026-02-16 04:31:52', '2026-02-19 07:23:15'),
(9, '4', 6, '', NULL, '2026-08-15', 1, '2026-02-16 04:33:15', '2026-02-16 04:33:15'),
(10, '4', 7, '', '2025-08-15', '2026-08-17', 0, '2026-02-18 03:07:57', '2026-02-19 07:23:15'),
(11, '2', 3, 'Semi-Annual', NULL, '2026-08-17', 0, '2026-02-18 07:08:25', '2026-02-19 07:23:15'),
(13, '1', 1, '', NULL, '2026-08-18', 0, '2026-02-19 02:59:08', '2026-02-19 07:23:15'),
(14, '3', 20, '', NULL, '2026-08-18', 0, '2026-02-19 03:08:55', '2026-02-19 07:23:15'),
(15, '1', 2, '', '2026-02-19', '2026-08-18', 0, '2026-02-19 03:39:44', '2026-02-19 07:23:15'),
(16, '4', 8, '', NULL, '2026-08-18', 0, '2026-02-19 03:40:12', '2026-02-19 07:23:15'),
(20, '1', 10, 'Semi-Annual', '2025-08-22', '2026-02-22', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(21, '1', 11, 'Semi-Annual', '2025-10-05', '2026-04-05', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(22, '1', 12, 'Semi-Annual', '2025-08-24', '2026-02-24', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(23, '1', 13, 'Semi-Annual', '2026-02-23', '2026-08-22', 1, '2026-02-19 06:06:48', '2026-02-23 06:30:23'),
(24, '1', 14, 'Annual', '2025-08-06', '2026-08-06', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(25, '1', 15, 'Semi-Annual', '2026-02-23', '2026-08-22', 1, '2026-02-19 06:06:48', '2026-02-23 07:54:36'),
(26, '1', 16, 'Semi-Annual', '2025-09-10', '2026-03-10', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(27, '1', 17, 'Semi-Annual', '2026-02-20', '2026-08-19', 1, '2026-02-19 06:06:48', '2026-02-20 08:01:20'),
(28, '1', 18, 'Semi-Annual', '2025-10-01', '2026-04-01', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(29, '1', 19, 'Semi-Annual', '2026-02-10', '2026-08-10', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(30, '1', 20, 'Semi-Annual', '2026-02-19', '2026-08-18', 1, '2026-02-19 06:06:48', '2026-02-19 08:12:27'),
(31, '1', 21, 'Semi-Annual', '2025-10-20', '2026-04-20', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(32, '1', 22, 'Semi-Annual', '2025-10-10', '2026-04-10', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(40, '3', 30, 'Semi-Annual', '2025-08-22', '2026-02-22', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(41, '3', 31, 'Semi-Annual', '2025-10-05', '2026-04-05', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(42, '3', 32, 'Semi-Annual', '2025-08-24', '2026-02-24', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(43, '3', 33, 'Semi-Annual', '2026-02-23', '2026-08-22', 1, '2026-02-19 06:06:48', '2026-02-23 06:30:39'),
(44, '3', 34, 'Annual', '2025-08-06', '2026-08-06', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(45, '3', 35, 'Semi-Annual', '2026-02-24', '2026-08-23', 1, '2026-02-19 06:06:48', '2026-02-24 07:14:30'),
(46, '3', 36, 'Semi-Annual', '2025-09-10', '2026-03-10', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(47, '3', 37, 'Semi-Annual', '2025-10-15', '2026-04-15', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(48, '3', 38, 'Semi-Annual', '2025-10-01', '2026-04-01', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(49, '3', 39, 'Semi-Annual', '2025-09-15', '2026-03-15', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(50, '3', 40, 'Semi-Annual', '2026-02-20', '2026-08-19', 1, '2026-02-19 06:06:48', '2026-02-20 08:06:53'),
(51, '3', 41, 'Semi-Annual', '2025-10-20', '2026-04-20', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(52, '3', 42, 'Semi-Annual', '2025-10-10', '2026-04-10', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(60, '4', 20, 'Semi-Annual', '2025-08-22', '2026-02-22', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(61, '4', 21, 'Semi-Annual', '2025-10-05', '2026-04-05', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(62, '4', 22, 'Semi-Annual', '2026-02-23', '2026-08-22', 1, '2026-02-19 06:06:48', '2026-02-23 06:30:53'),
(63, '4', 23, 'Semi-Annual', '2025-08-10', '2026-02-10', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(64, '4', 24, 'Semi-Annual', '2025-10-15', '2026-04-15', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(65, '4', 25, 'Semi-Annual', '2025-10-01', '2026-04-01', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(66, '4', 26, 'Semi-Annual', '2025-09-15', '2026-03-15', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15'),
(70, '2', 2, 'Semi-Annual', '2025-10-15', '2026-04-15', 1, '2026-02-19 06:06:48', '2026-02-19 07:23:15');

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
(8, 'ICT PREVENTIVE MAINTENANCE', '1', 'Semi-Annual', NULL, '{\"verifiedByName\":\"[Select Supervisor Name]\",\"verifiedByTitle\":\"DIVISION \\/ SECTION HEAD\",\"notedByName\":\"[Select Head of Office]\",\"notedByTitle\":\"HEAD OF OFFICE\"}', 1, '2026-02-18 06:02:38'),
(9, 'SYSTEM UNIT PREVENTIVE MAINTENANCE', '1', 'Semi-Annual', NULL, '{\"verifiedByName\":\"TEST\",\"verifiedByTitle\":\"DIVISION \\/ SECTION HEAD\",\"notedByName\":\"[Select Head of Office]\",\"notedByTitle\":\"HEAD OF OFFICE\"}', 1, '2026-02-18 06:02:55'),
(10, 'MONITOR PREVENTIVE MAINTENANCE', '3', 'Semi-Annual', NULL, '{\"verifiedByName\":\"[Select Supervisor Name]\",\"verifiedByTitle\":\"DIVISION / SECTION HEAD\",\"notedByName\":\"[Select Head of Office]\",\"notedByTitle\":\"HEAD OF OFFICE\"}', 1, '2026-02-19 06:28:43'),
(11, 'PRINTER PREVENTIVE MAINTENANCE', '4', 'Quarterly', NULL, '{\"verifiedByName\":\"[Select Supervisor Name]\",\"verifiedByTitle\":\"DIVISION / SECTION HEAD\",\"notedByName\":\"[Select Head of Office]\",\"notedByTitle\":\"HEAD OF OFFICE\"}', 1, '2026-02-19 06:28:43'),
(12, 'ALL-IN-ONE PC PREVENTIVE MAINTENANCE', '2', 'Semi-Annual', NULL, '{\"verifiedByName\":\"[Select Supervisor Name]\",\"verifiedByTitle\":\"DIVISION / SECTION HEAD\",\"notedByName\":\"[Select Head of Office]\",\"notedByTitle\":\"HEAD OF OFFICE\"}', 1, '2026-02-19 06:28:43'),
(13, 'MULTI-TEMPLATE TEST', '2,5,3,1', 'Semi-Annual', NULL, '{\"verifiedByName\":\"[Select Supervisor Name]\",\"verifiedByTitle\":\"DIVISION \\/ SECTION HEAD\",\"notedByName\":\"[Select Head of Office]\",\"notedByTitle\":\"HEAD OF OFFICE\"}', 1, '2026-02-19 06:57:14');

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
(15, 'faf', '24', 'afadf', '2000', NULL),
(16, 'faf', '24', '654656345', '2000', NULL),
(17, '5141', '24', '214141234134', '2022', NULL),
(18, '11111111', '11111111111111', '11111111111', '2000', NULL),
(19, '555555555', '555555555', '5555555555', '2000', NULL),
(20, 'test', '24 inches', 'test', '2025', 1),
(30, 'Dell P2422H', '24 inches', 'MO-2023-030', '2023', 100),
(31, 'LG 24MK430H', '24 inches', 'MO-2023-031', '2023', 101),
(32, 'Samsung S24C450', '24 inches', 'MO-2024-032', '2024', 102),
(33, 'HP V24e G5', '24 inches', 'MO-2023-033', '2023', 103),
(34, 'Dell E2420H', '24 inches', 'MO-2024-034', '2024', 104),
(35, 'LG 22MK430H', '22 inches', 'MO-2022-035', '2022', 105),
(36, 'Acer V246HQL', '24 inches', 'MO-2024-036', '2024', 106),
(37, 'Dell P2723QE', '27 inches', 'MO-2025-037', '2025', 1),
(38, 'Samsung LS24C360', '24 inches', 'MO-2025-038', '2025', 107),
(39, 'HP M24fw', '24 inches', 'MO-2024-039', '2024', 108),
(40, 'LG 24MP400', '24 inches', 'MO-2025-040', '2025', 109),
(41, 'Dell SE2422H', '24 inches', 'MO-2023-041', '2023', 110),
(42, 'Acer KA242Y', '24 inches', 'MO-2023-042', '2023', 111);

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
(4, '66666', '666666', '6666666', '2000', NULL),
(5, '666667657', '666666657 56', '66666667657', '2000', NULL),
(7, 'HP DESKJET210', '2012413', 'SN_DK1142', '2003', 645987),
(8, 'test', 'test', 'test', '2025', 645987),
(20, 'Epson L3150', 'EcoTank L3150', 'PR-2023-020', '2023', 100),
(21, 'HP LaserJet Pro M404n', 'M404n', 'PR-2024-021', '2024', 101),
(22, 'Canon PIXMA G3010', 'G3010', 'PR-2023-022', '2023', 103),
(23, 'Epson L5290', 'EcoTank L5290', 'PR-2024-023', '2024', 105),
(24, 'HP LaserJet M110we', 'M110we', 'PR-2025-024', '2025', 1),
(25, 'Brother DCP-T520W', 'DCP-T520W', 'PR-2024-025', '2024', 107),
(26, 'Epson L3110', 'EcoTank L3110', 'PR-2023-026', '2023', 108);

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
(12, 'Adobe Photoshop', 'NONE', 'Perpetual', '2024-11-11 00:00:00', 'nia.lextermanuel@gmail.com', 'asdasdsad', 1),
(13, 'rasr', 'rqwerr', 'Subscription', '2026-11-11 00:00:00', '432', NULL, NULL);

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
(1, 'Pre-Built', 'test', 'test', 'tets', 'test', 'test', 'test', '2025', 645987),
(2, 'Custom Built', 'test2', 'test2', 'test2', 'test2', 'test2', 'test2', '2025', 645987),
(10, 'Pre-Built', 'Dell OptiPlex 5070', 'Intel Core i5-9500', '8GB DDR4', 'Intel UHD 630', '256GB SSD', 'SU-2023-010', '2023', 100),
(11, 'Pre-Built', 'HP ProDesk 400 G6', 'Intel Core i5-9500T', '16GB DDR4', 'Intel UHD 630', '512GB SSD', 'SU-2023-011', '2023', 101),
(12, 'Pre-Built', 'Lenovo ThinkCentre M70', 'Intel Core i3-10100', '8GB DDR4', 'Intel UHD 630', '256GB SSD', 'SU-2024-012', '2024', 102),
(13, 'Pre-Built', 'Acer Veriton M200', 'Intel Core i5-10400', '8GB DDR4', 'Intel UHD 630', '500GB HDD', 'SU-2023-013', '2023', 103),
(14, 'Pre-Built', 'Dell OptiPlex 3080', 'Intel Core i5-10500', '16GB DDR4', 'Intel UHD 630', '512GB SSD', 'SU-2024-014', '2024', 104),
(15, 'Pre-Built', 'HP EliteDesk 800 G5', 'Intel Core i7-9700', '16GB DDR4', 'Intel UHD 630', '512GB SSD', 'SU-2022-015', '2022', 105),
(16, 'Pre-Built', 'Lenovo V530', 'Intel Core i3-9100', '8GB DDR4', 'Intel UHD Graphics', '256GB SSD', 'SU-2024-016', '2024', 106),
(17, 'Custom Built', 'Custom Build ICT', 'AMD Ryzen 7 5700X', '32GB DDR4', 'NVIDIA RTX 3060', '1TB NVMe', 'SU-2025-017', '2025', 1),
(18, 'Pre-Built', 'Dell Vostro 3710', 'Intel Core i5-12400', '8GB DDR4', 'Intel UHD 730', '512GB SSD', 'SU-2025-018', '2025', 107),
(19, 'Pre-Built', 'HP ProDesk 405 G8', 'AMD Ryzen 5 5600G', '16GB DDR4', 'AMD Radeon Graphics', '512GB SSD', 'SU-2024-019', '2024', 108),
(20, 'Pre-Built', 'Acer Veriton S2690', 'Intel Core i5-12400', '8GB DDR4', 'Intel UHD 730', '256GB SSD', 'SU-2025-020', '2025', 109),
(21, 'Pre-Built', 'Lenovo ThinkCentre M80', 'Intel Core i5-10500', '16GB DDR4', 'Intel UHD 630', '512GB SSD', 'SU-2023-021', '2025', 110),
(22, 'Pre-Built', 'Dell OptiPlex 7090', 'Intel Core i7-10700', '16GB DDR4', 'Intel UHD 630', '1TB SSD', 'SU-2023-022', '2023', 111);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=705;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_activity_logs`
--
ALTER TABLE `tbl_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `tbl_allinone`
--
ALTER TABLE `tbl_allinone`
  MODIFY `allinoneId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_checklist_category`
--
ALTER TABLE `tbl_checklist_category`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `tbl_checklist_item`
--
ALTER TABLE `tbl_checklist_item`
  MODIFY `itemId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

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
  MODIFY `recordId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `tbl_maintenance_response`
--
ALTER TABLE `tbl_maintenance_response`
  MODIFY `responseId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `tbl_maintenance_schedule`
--
ALTER TABLE `tbl_maintenance_schedule`
  MODIFY `scheduleId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `tbl_maintenance_template`
--
ALTER TABLE `tbl_maintenance_template`
  MODIFY `templateId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_monitor`
--
ALTER TABLE `tbl_monitor`
  MODIFY `monitorId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `tbl_otherequipment`
--
ALTER TABLE `tbl_otherequipment`
  MODIFY `otherEquipmentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_printer`
--
ALTER TABLE `tbl_printer`
  MODIFY `printerId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `tbl_software`
--
ALTER TABLE `tbl_software`
  MODIFY `softwareId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_systemunit`
--
ALTER TABLE `tbl_systemunit`
  MODIFY `systemunitId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
