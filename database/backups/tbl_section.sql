-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 06:41 AM
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
-- Table structure for table `tbl_section`
--

CREATE TABLE `tbl_section` (
  `sectionId` int(11) NOT NULL,
  `sectionCode` varchar(50) NOT NULL,
  `sectionName` varchar(255) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `updatedAt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `divisionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_section`
--

INSERT INTO `tbl_section` (`sectionId`, `sectionCode`, `sectionName`, `createdAt`, `updatedAt`, `divisionId`) VALUES
(1, 'ICT', 'Information and Communication Services', '2026-01-29 08:31:17', '2026-01-30 04:50:18', 1),
(2, 'ODM', 'Office of the Department Manager', '2026-01-30 00:17:19', '2026-01-30 04:50:18', 1),
(3, 'PR', 'PR Unit', '2026-01-30 00:17:39', '2026-01-30 04:50:18', 1),
(4, 'LSU', 'Legal Services Unit', '2026-01-30 00:18:05', '2026-01-30 04:50:18', 1),
(5, 'BAC', 'BAC Unit', '2026-01-30 00:18:13', '2026-01-30 04:50:18', 1),
(6, 'OEM', 'Office of the EOD Manager', '2026-01-30 00:18:51', '2026-01-30 04:50:18', 2),
(7, 'ENGU', 'Engineering Section', '2026-01-30 00:19:58', '2026-01-30 04:50:18', 2),
(8, 'EMS', 'Equipment Management Section', '2026-01-30 00:20:36', '2026-01-30 04:50:18', 2),
(9, 'IDS', 'Institutional Development Section', '2026-01-30 00:21:46', '2026-01-30 04:50:18', 2),
(10, 'OAM', 'Office of the ADFIN Manager', '2026-01-30 00:22:12', '2026-01-30 04:50:18', 3),
(11, 'AS', 'Administrative Section', '2026-01-30 00:22:33', '2026-01-30 04:50:18', 3),
(12, 'FS', 'Finance Section', '2026-01-30 00:25:12', '2026-01-30 04:50:18', 3),
(13, 'PU', 'Property Unit', '2026-01-30 00:25:56', '2026-01-30 04:50:18', 3),
(14, 'MSU', 'Medical Service Unit', '2026-01-30 00:26:10', '2026-01-30 04:50:18', 3),
(15, 'GSU', 'General Services Unit', '2026-01-30 00:26:27', '2026-01-30 04:50:18', 3),
(16, 'SSU', 'Security Services Unit', '2026-01-30 00:26:42', '2026-01-30 04:50:18', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_section`
--
ALTER TABLE `tbl_section`
  ADD PRIMARY KEY (`sectionId`),
  ADD UNIQUE KEY `sectionCode` (`sectionCode`),
  ADD KEY `divisionId` (`divisionId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_section`
--
ALTER TABLE `tbl_section`
  MODIFY `sectionId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_section`
--
ALTER TABLE `tbl_section`
  ADD CONSTRAINT `tbl_section_ibfk_1` FOREIGN KEY (`divisionId`) REFERENCES `tbl_division` (`divisionId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
