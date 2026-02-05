-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: support_system
-- ------------------------------------------------------
-- Server version	8.0.43

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `location` (
  `location_id` int NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `location_type_id` int NOT NULL,
  `parent_location_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_id`),
  KEY `parent_location_id` (`parent_location_id`),
  KEY `location_type_id_idx` (`location_type_id`),
  CONSTRAINT `location_type_id` FOREIGN KEY (`location_type_id`) REFERENCES `location_type` (`id`),
  CONSTRAINT `parent_location_id` FOREIGN KEY (`parent_location_id`) REFERENCES `location` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `location`
--

LOCK TABLES `location` WRITE;
/*!40000 ALTER TABLE `location` DISABLE KEYS */;
INSERT INTO `location` VALUES (3,'Office of the Department Manager',1,NULL,'2025-02-16 08:02:03','0'),(4,'ICT Unit',4,7,'2025-02-16 08:02:03','0'),(5,'Public Relation Office Unit',4,3,'2025-02-16 08:02:03','0'),(6,'Legal Services',4,8,'2025-02-16 08:02:03','0'),(7,'Office of the EOD Manager',2,NULL,'2025-02-16 08:02:03','0'),(8,'Office of the ADFIN Manager',2,NULL,'2025-02-16 08:02:03','0'),(9,'Administrative Section',3,24,'2025-02-16 08:02:03','0'),(10,'Finance Section',3,24,'2025-02-16 08:02:03','0'),(11,'Property Unit',4,9,'2025-02-16 08:02:03','0'),(12,'General Services Security Unit ',4,9,'2025-02-16 08:02:03','0'),(13,'Pantabangan Lake Resort and Hotel',4,9,'2025-02-16 08:02:03','0'),(14,'Medical Services Unit',4,9,'2025-02-16 08:02:03','0'),(15,'Cashiering Unit',4,10,'2025-02-16 08:02:03','0'),(16,'FISA Unit',4,9,'2025-02-16 08:02:03','0'),(18,'Engineering Section',3,23,'2025-02-16 08:02:03','0'),(19,'Operation Section',3,23,'2025-02-16 08:02:03','0'),(20,'Equipment Management Section',3,23,'2025-02-16 08:02:03','0'),(21,'Institutional Development Section',3,23,'2025-02-16 08:02:03','0'),(22,'BAC Unit',4,3,'2025-02-18 07:24:43','0'),(23,'Engineering and Operation Division(EOD)',2,NULL,'2025-03-03 06:38:42','0'),(24,'Administrative and Finance Division(ADFIN)',2,NULL,'2025-03-03 06:44:12','0'),(25,'Personnel and Records Unit',4,9,'2025-03-03 08:28:04','0'),(26,'DM Secretary',4,3,'2025-03-03 08:30:12','0'),(27,'DM Secretary',4,7,'2025-03-03 08:30:12','0'),(28,'DM Secretary',4,8,'2025-03-03 08:30:12','0'),(29,'Accounting Unit',4,10,'2025-04-02 07:46:43','0');
/*!40000 ALTER TABLE `location` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-05 13:09:06
