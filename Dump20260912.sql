-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: parlour_pos
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
-- Table structure for table `appointment_services`
--

DROP TABLE IF EXISTS `appointment_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` bigint unsigned NOT NULL,
  `service_id` bigint unsigned NOT NULL,
  `staff_id` bigint unsigned DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `duration_minutes` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `service_id` (`service_id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `appointment_services_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointment_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `appointment_services_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_services`
--

LOCK TABLES `appointment_services` WRITE;
/*!40000 ALTER TABLE `appointment_services` DISABLE KEYS */;
INSERT INTO `appointment_services` VALUES (1,1,1,1,650.00,45),(2,2,2,2,1450.00,60),(3,3,3,3,3200.00,120),(4,4,4,4,1850.00,75),(5,5,5,5,850.00,45),(6,6,6,1,750.00,45),(7,7,7,2,1200.00,60),(8,8,8,3,3500.00,75),(9,9,9,4,1000.00,45),(10,10,10,5,500.00,30),(11,11,11,1,120.00,15),(12,12,12,2,650.00,40),(13,13,6,6,750.00,45);
/*!40000 ALTER TABLE `appointment_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `appointment_code` varchar(30) NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `staff_id` bigint unsigned DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `status` enum('pending','confirmed','checked_in','in_progress','completed','cancelled','no_show') NOT NULL DEFAULT 'pending',
  `notes` text,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appointment_code` (`appointment_code`),
  KEY `staff_id` (`staff_id`,`start_at`,`end_at`,`status`),
  KEY `customer_id` (`customer_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,'APT-DEMO-01',5,1,'2026-09-15 10:00:00','2026-09-15 10:45:00','completed','Demo appointment',2,'2026-09-10 08:02:34'),(2,'APT-DEMO-02',6,2,'2026-09-14 11:00:00','2026-09-14 12:00:00','confirmed','Demo appointment',2,'2026-09-10 08:02:34'),(3,'APT-DEMO-03',7,3,'2026-09-13 12:00:00','2026-09-13 14:00:00','checked_in','Demo appointment',2,'2026-09-10 08:02:34'),(4,'APT-DEMO-04',8,4,'2026-09-12 13:00:00','2026-09-12 14:15:00','in_progress','Demo appointment',2,'2026-09-10 08:02:34'),(5,'APT-DEMO-05',9,5,'2026-09-11 14:00:00','2026-09-11 14:45:00','completed','Demo appointment',2,'2026-09-10 08:02:34'),(6,'APT-DEMO-06',10,1,'2026-09-10 10:00:00','2026-09-10 10:45:00','cancelled','Demo appointment',2,'2026-09-10 08:02:34'),(7,'APT-DEMO-07',11,2,'2026-09-09 11:00:00','2026-09-09 12:00:00','no_show','Demo appointment',2,'2026-09-10 08:02:34'),(8,'APT-DEMO-08',12,3,'2026-09-08 12:00:00','2026-09-08 13:15:00','pending','Demo appointment',2,'2026-09-10 08:02:34'),(9,'APT-DEMO-09',13,4,'2026-09-07 13:00:00','2026-09-07 13:45:00','confirmed','Demo appointment',2,'2026-09-10 08:02:34'),(10,'APT-DEMO-10',14,5,'2026-09-06 14:00:00','2026-09-06 14:30:00','checked_in','Demo appointment',2,'2026-09-10 08:02:34'),(11,'APT-DEMO-11',15,1,'2026-09-05 10:00:00','2026-09-05 10:15:00','in_progress','Demo appointment',2,'2026-09-10 08:02:34'),(12,'APT-DEMO-12',16,2,'2026-09-04 11:00:00','2026-09-04 11:40:00','completed','Demo appointment',2,'2026-09-10 08:02:34'),(13,'APT-20260911-3E30',20,6,'2026-09-11 16:03:00','2026-09-11 16:48:00','pending','',1,'2026-09-11 10:34:04');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module_name` varchar(80) NOT NULL,
  `record_id` bigint unsigned DEFAULT NULL,
  `context_json` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `module_name` (`module_name`,`record_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'login','authentication',1,NULL,'2026-09-10 19:33:32',NULL),(2,1,'login','authentication',1,NULL,'2026-09-10 19:37:19','::1'),(3,1,'logout','authentication',1,NULL,'2026-09-10 19:37:20','::1'),(4,1,'login','authentication',1,NULL,'2026-09-10 19:37:20','::1'),(5,1,'logout','authentication',1,NULL,'2026-09-10 19:37:20','::1'),(6,1,'logout','authentication',1,NULL,'2026-09-10 19:41:19','::1'),(7,1,'login','authentication',1,NULL,'2026-09-10 19:41:47','::1'),(8,1,'logout','authentication',1,NULL,'2026-09-10 19:42:19','::1'),(9,NULL,'failed_login','authentication',2,'{\"email\": \"admin@veloraparlour.demo\", \"reason\": \"invalid_password\"}','2026-09-10 20:11:26','::1'),(10,2,'login','authentication',2,'{\"ip\": \"::1\"}','2026-09-10 20:11:27','::1'),(11,2,'created','users',6,'{\"name\": \"Automated Test User\", \"email\": \"automated_test_user@veloraparlour.demo\", \"role_id\": 4, \"staff_id\": null, \"is_active\": 1}','2026-09-10 20:11:27','::1'),(12,2,'updated','users',6,'{\"email\": \"automated_test_user@veloraparlour.demo\", \"role_id\": 4, \"staff_id\": null, \"is_active\": 1, \"password_reset\": false}','2026-09-10 20:11:27','::1'),(13,2,'status_changed','users',6,'{\"is_active\": 0}','2026-09-10 20:11:27','::1'),(14,2,'status_changed','users',6,'{\"is_active\": 1}','2026-09-10 20:11:27','::1'),(15,2,'logout','authentication',2,NULL,'2026-09-10 20:11:28','::1'),(16,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-10 20:11:28','::1'),(17,3,'logout','authentication',3,NULL,'2026-09-10 20:11:28','::1'),(18,4,'login','authentication',4,'{\"ip\": \"::1\"}','2026-09-10 20:11:29','::1'),(19,4,'logout','authentication',4,NULL,'2026-09-10 20:11:29','::1'),(20,5,'login','authentication',5,'{\"ip\": \"::1\"}','2026-09-10 20:11:29','::1'),(21,5,'logout','authentication',5,NULL,'2026-09-10 20:11:30','::1'),(22,3,'password_reset_requested','authentication',3,'{\"email\": \"manager@veloraparlour.demo\"}','2026-09-10 20:11:30','::1'),(23,3,'password_reset_completed','authentication',3,NULL,'2026-09-10 20:11:30','::1'),(24,NULL,'failed_login','authentication',3,'{\"email\": \"manager@veloraparlour.demo\", \"reason\": \"invalid_password\"}','2026-09-10 20:11:31','::1'),(25,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-10 20:11:31','::1'),(26,3,'password_change','authentication',3,NULL,'2026-09-10 20:11:32','::1'),(27,3,'logout','authentication',3,NULL,'2026-09-10 20:11:32','::1'),(28,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-10 20:11:49','::1'),(29,NULL,'failed_login','authentication',2,'{\"email\": \"admin@veloraparlour.demo\", \"reason\": \"invalid_password\"}','2026-09-10 20:14:07','::1'),(30,2,'login','authentication',2,'{\"ip\": \"::1\"}','2026-09-10 20:14:07','::1'),(31,2,'updated','users',6,'{\"email\": \"automated_test_user@veloraparlour.demo\", \"role_id\": 4, \"staff_id\": null, \"is_active\": 1, \"password_reset\": false}','2026-09-10 20:14:07','::1'),(32,2,'status_changed','users',6,'{\"is_active\": 0}','2026-09-10 20:14:07','::1'),(33,2,'status_changed','users',6,'{\"is_active\": 1}','2026-09-10 20:14:07','::1'),(34,2,'logout','authentication',2,NULL,'2026-09-10 20:14:08','::1'),(35,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-10 20:14:08','::1'),(36,3,'logout','authentication',3,NULL,'2026-09-10 20:14:08','::1'),(37,4,'login','authentication',4,'{\"ip\": \"::1\"}','2026-09-10 20:14:09','::1'),(38,4,'logout','authentication',4,NULL,'2026-09-10 20:14:09','::1'),(39,5,'login','authentication',5,'{\"ip\": \"::1\"}','2026-09-10 20:14:09','::1'),(40,5,'logout','authentication',5,NULL,'2026-09-10 20:14:10','::1'),(41,3,'password_reset_requested','authentication',3,'{\"email\": \"manager@veloraparlour.demo\"}','2026-09-10 20:14:10','::1'),(42,3,'password_reset_completed','authentication',3,NULL,'2026-09-10 20:14:10','::1'),(43,NULL,'failed_login','authentication',3,'{\"email\": \"manager@veloraparlour.demo\", \"reason\": \"invalid_password\"}','2026-09-10 20:14:11','::1'),(44,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-10 20:14:11','::1'),(45,3,'password_change','authentication',3,NULL,'2026-09-10 20:14:12','::1'),(46,3,'logout','authentication',3,NULL,'2026-09-10 20:14:12','::1'),(47,NULL,'failed_login','authentication',2,'{\"email\": \"admin@veloraparlour.demo\", \"reason\": \"invalid_password\"}','2026-09-10 20:22:11','::1'),(48,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-10 20:22:27','::1'),(49,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-10 20:29:02','::1'),(50,1,'logout','authentication',1,NULL,'2026-09-10 20:29:02','::1'),(51,NULL,'failed_login','authentication',2,'{\"email\": \"admin@veloraparlour.demo\", \"reason\": \"invalid_password\"}','2026-09-10 20:29:11','::1'),(52,2,'login','authentication',2,'{\"ip\": \"::1\"}','2026-09-10 20:29:11','::1'),(53,2,'created','users',7,'{\"name\": \"Automated Test User\", \"email\": \"automated_test_user@veloraparlour.demo\", \"role_id\": 4, \"staff_id\": null, \"is_active\": 1}','2026-09-10 20:29:12','::1'),(54,2,'updated','users',7,'{\"email\": \"automated_test_user@veloraparlour.demo\", \"role_id\": 4, \"staff_id\": null, \"is_active\": 1, \"password_reset\": false}','2026-09-10 20:29:12','::1'),(55,2,'status_changed','users',7,'{\"is_active\": 0}','2026-09-10 20:29:12','::1'),(56,2,'status_changed','users',7,'{\"is_active\": 1}','2026-09-10 20:29:12','::1'),(57,2,'logout','authentication',2,NULL,'2026-09-10 20:29:12','::1'),(58,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-10 20:29:12','::1'),(59,3,'logout','authentication',3,NULL,'2026-09-10 20:29:13','::1'),(60,4,'login','authentication',4,'{\"ip\": \"::1\"}','2026-09-10 20:29:13','::1'),(61,4,'logout','authentication',4,NULL,'2026-09-10 20:29:14','::1'),(62,5,'login','authentication',5,'{\"ip\": \"::1\"}','2026-09-10 20:29:14','::1'),(63,5,'logout','authentication',5,NULL,'2026-09-10 20:29:14','::1'),(64,3,'password_reset_requested','authentication',3,'{\"email\": \"manager@veloraparlour.demo\"}','2026-09-10 20:29:14','::1'),(65,3,'password_reset_completed','authentication',3,NULL,'2026-09-10 20:29:15','::1'),(66,NULL,'failed_login','authentication',3,'{\"email\": \"manager@veloraparlour.demo\", \"reason\": \"invalid_password\"}','2026-09-10 20:29:15','::1'),(67,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-10 20:29:16','::1'),(68,3,'password_change','authentication',3,NULL,'2026-09-10 20:29:16','::1'),(69,3,'logout','authentication',3,NULL,'2026-09-10 20:29:17','::1'),(70,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-10 20:42:45','::1'),(71,1,'logout','authentication',1,NULL,'2026-09-10 20:42:45','::1'),(72,2,'login','authentication',2,'{\"ip\": \"::1\"}','2026-09-10 20:42:45','::1'),(73,2,'logout','authentication',2,NULL,'2026-09-10 20:42:45','::1'),(74,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-10 20:42:46','::1'),(75,3,'logout','authentication',3,NULL,'2026-09-10 20:42:46','::1'),(76,4,'login','authentication',4,'{\"ip\": \"::1\"}','2026-09-10 20:42:46','::1'),(77,4,'logout','authentication',4,NULL,'2026-09-10 20:42:47','::1'),(78,5,'login','authentication',5,'{\"ip\": \"::1\"}','2026-09-10 20:42:47','::1'),(79,5,'logout','authentication',5,NULL,'2026-09-10 20:42:47','::1'),(80,1,'logout','authentication',1,NULL,'2026-09-10 20:44:06','::1'),(81,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-10 20:44:22','::1'),(82,1,'logout','authentication',1,NULL,'2026-09-10 20:44:30','::1'),(83,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-10 20:45:18','::1'),(84,3,'logout','authentication',3,NULL,'2026-09-10 20:46:13','::1'),(85,4,'login','authentication',4,'{\"ip\": \"::1\"}','2026-09-10 20:47:02','::1'),(86,4,'logout','authentication',4,NULL,'2026-09-10 20:47:21','::1'),(87,5,'login','authentication',5,'{\"ip\": \"::1\"}','2026-09-10 20:47:47','::1'),(88,5,'logout','authentication',5,NULL,'2026-09-10 20:47:59','::1'),(89,NULL,'failed_login','authentication',1,'{\"email\": \"kkundu249@gmail.com\", \"reason\": \"invalid_password\"}','2026-09-10 20:57:01','::1'),(90,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-10 20:58:19','::1'),(91,1,'logout','authentication',1,NULL,'2026-09-10 20:58:48','::1'),(92,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-11 08:38:41','::1'),(93,1,'logout','authentication',1,NULL,'2026-09-11 08:40:14','::1'),(94,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-11 08:41:16','::1'),(95,3,'logout','authentication',3,NULL,'2026-09-11 08:41:41','::1'),(96,5,'login','authentication',5,'{\"ip\": \"::1\"}','2026-09-11 08:41:52','::1'),(97,5,'logout','authentication',5,NULL,'2026-09-11 08:42:10','::1'),(98,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-11 09:52:58','::1'),(99,1,'status_updated','appointments',1,'{\"new_status\": \"confirmed\", \"old_status\": \"pending\"}','2026-09-11 09:56:49','::1'),(100,1,'status_updated','appointments',1,'{\"new_status\": \"pending\", \"old_status\": \"confirmed\"}','2026-09-11 09:58:49','::1'),(101,1,'adjusted','inventory',9,'{\"after\": 38, \"before\": 20, \"change\": 18, \"reason\": \"Physical stock count correction\"}','2026-09-11 10:05:09','::1'),(102,1,'adjusted','inventory',9,'{\"after\": 28, \"before\": 38, \"change\": -10, \"reason\": \"Physical stock count correction\"}','2026-09-11 10:06:11','::1'),(103,1,'created','users',8,'{\"name\": \"Debarpan Dutta Banik\", \"email\": \"deba@example.com\", \"role_id\": 4, \"staff_id\": 3, \"is_active\": 1}','2026-09-11 10:15:19','::1'),(104,1,'created','staff',6,'{\"name\": \"Debarpan Dutta Banik\", \"mobile\": \"9876543210\", \"staff_code\": \"STF-20260911-8986\", \"designation\": \"hair washer\", \"commission_type\": \"percentage\", \"commission_value\": \"5\"}','2026-09-11 10:19:43','::1'),(105,1,'updated','users',8,'{\"email\": \"deba@example.com\", \"role_id\": 4, \"staff_id\": 6, \"is_active\": 1, \"password_reset\": false}','2026-09-11 10:19:53','::1'),(106,1,'logout','authentication',1,NULL,'2026-09-11 10:20:22','::1'),(107,8,'login','authentication',8,'{\"ip\": \"::1\"}','2026-09-11 10:20:33','::1'),(108,8,'logout','authentication',8,NULL,'2026-09-11 10:20:47','::1'),(109,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-11 10:21:17','::1'),(110,3,'status_updated','appointments',1,'{\"new_status\": \"completed\", \"old_status\": \"pending\"}','2026-09-11 10:24:25','::1'),(111,3,'created','invoice',13,'{\"grand_total\": 126, \"invoice_number\": \"INV-20260911-2BB008\"}','2026-09-11 10:25:23','::1'),(112,3,'finalized','invoices',13,'{\"customer_id\": 5, \"paid_amount\": 100}','2026-09-11 10:25:23','::1'),(113,3,'created','purchases',11,'{\"grand_total\": 504, \"items_count\": 1, \"supplier_id\": 2}','2026-09-11 10:26:44','::1'),(114,3,'created','purchases',12,'{\"grand_total\": 157.5, \"items_count\": 1, \"supplier_id\": 3}','2026-09-11 10:27:21','::1'),(115,3,'logout','authentication',3,NULL,'2026-09-11 10:29:34','::1'),(116,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-11 10:32:28','::1'),(117,1,'created','customers',20,'{\"name\": \"Susmita Das\", \"email\": \"susmita@example.com\", \"notes\": \"\", \"gender\": \"Female\", \"mobile\": \"9764581323\", \"customer_code\": \"CUS-20260911-268B\"}','2026-09-11 10:33:30','::1'),(118,1,'created','appointments',13,'{\"code\": \"APT-20260911-3E30\", \"staff_id\": 6, \"start_at\": \"2026-09-11 16:03\", \"customer_id\": 20}','2026-09-11 10:34:04','::1'),(119,1,'created','invoice',14,'{\"grand_total\": 787.5, \"invoice_number\": \"INV-20260911-84F712\"}','2026-09-11 10:35:11','::1'),(120,1,'finalized','invoices',14,'{\"customer_id\": 20, \"paid_amount\": 750}','2026-09-11 10:35:11','::1'),(121,1,'status_updated','appointments',13,'{\"new_status\": \"confirmed\", \"old_status\": \"pending\"}','2026-09-11 10:35:23','::1'),(122,1,'logout','authentication',1,NULL,'2026-09-11 10:38:22','::1'),(123,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-11 10:38:42','::1'),(124,3,'logout','authentication',3,NULL,'2026-09-11 10:38:58','::1'),(125,8,'login','authentication',8,'{\"ip\": \"::1\"}','2026-09-11 10:39:09','::1'),(126,8,'logout','authentication',8,NULL,'2026-09-11 10:39:32','::1'),(127,8,'login','authentication',8,'{\"ip\": \"::1\"}','2026-09-11 10:39:48','::1'),(128,8,'status_updated','appointments',13,'{\"new_status\": \"pending\", \"old_status\": \"confirmed\"}','2026-09-11 10:40:00','::1'),(129,8,'logout','authentication',8,NULL,'2026-09-12 14:38:16','::1'),(130,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 14:38:25','::1'),(131,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:08:39','::1'),(132,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:09:57','::1'),(133,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:14:14','::1'),(134,1,'uploaded','staff_document',1,'{\"title\": \"Joining Agreement\", \"filename\": \"joining_agreement.pdf\", \"staff_id\": 7}','2026-09-12 15:14:14','::1'),(135,1,'created','staff',7,'{\"name\": \"Test Stylist Aarti\", \"staff_code\": \"STF-20260912-04BE\", \"designation\": \"Colour Specialist\"}','2026-09-12 15:14:14','::1'),(136,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:15:30','::1'),(137,1,'uploaded','staff_document',2,'{\"title\": \"Joining Agreement\", \"filename\": \"joining_agreement.pdf\", \"staff_id\": 8}','2026-09-12 15:15:30','::1'),(138,1,'created','staff',8,'{\"name\": \"Test Stylist Aarti\", \"staff_code\": \"STF-20260912-3B07\", \"designation\": \"Colour Specialist\"}','2026-09-12 15:15:30','::1'),(139,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:17:00','::1'),(140,1,'uploaded','staff_document',3,'{\"title\": \"Joining Agreement\", \"filename\": \"joining_agreement.pdf\", \"staff_id\": 9}','2026-09-12 15:17:00','::1'),(141,1,'created','staff',9,'{\"name\": \"Test Stylist Aarti\", \"staff_code\": \"STF-20260912-EEA0\", \"designation\": \"Colour Specialist\"}','2026-09-12 15:17:00','::1'),(142,1,'updated','staff',1,'{\"name\": \"Aarti Sen (Updated)\", \"is_active\": 1, \"designation\": \"Lead Colour Specialist\"}','2026-09-12 15:17:00','::1'),(143,1,'uploaded','staff_document',4,'{\"title\": \"Cosmetology Diploma\", \"filename\": \"cosmetology_diploma.pdf\", \"staff_id\": 1}','2026-09-12 15:17:00','::1'),(144,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:19:23','::1'),(145,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:24:03','::1'),(146,1,'uploaded','staff_document',5,'{\"title\": \"Joining Agreement\", \"filename\": \"joining_agreement.pdf\", \"staff_id\": 10}','2026-09-12 15:24:04','::1'),(147,1,'created','staff',10,'{\"name\": \"Test Stylist Aarti\", \"staff_code\": \"STF-20260912-6511\", \"designation\": \"Colour Specialist\"}','2026-09-12 15:24:04','::1'),(148,1,'updated','staff',10,'{\"name\": \"Aarti Sen (Updated)\", \"is_active\": 1, \"designation\": \"Lead Colour Specialist\"}','2026-09-12 15:24:04','::1'),(149,1,'uploaded','staff_document',6,'{\"title\": \"Cosmetology Diploma\", \"filename\": \"cosmetology_diploma.pdf\", \"staff_id\": 10}','2026-09-12 15:24:04','::1'),(150,1,'deleted','staff_document',4,'{\"title\": \"Cosmetology Diploma\", \"staff_id\": 1}','2026-09-12 15:24:04','::1'),(151,1,'created','invoice',15,'{\"grand_total\": 682.5, \"invoice_number\": \"INV-20260912-5B686A\"}','2026-09-12 15:24:04','::1'),(152,1,'finalized','invoices',15,'{\"customer_id\": 1, \"paid_amount\": 682.5}','2026-09-12 15:24:04','::1'),(153,1,'logout','authentication',1,NULL,'2026-09-12 15:24:04','::1'),(154,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:24:24','::1'),(155,1,'logout','authentication',1,NULL,'2026-09-12 15:24:24','::1'),(156,2,'login','authentication',2,'{\"ip\": \"::1\"}','2026-09-12 15:24:24','::1'),(157,2,'logout','authentication',2,NULL,'2026-09-12 15:24:24','::1'),(158,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-12 15:24:24','::1'),(159,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:25:13','::1'),(160,1,'logout','authentication',1,NULL,'2026-09-12 15:25:13','::1'),(161,2,'login','authentication',2,'{\"ip\": \"::1\"}','2026-09-12 15:25:13','::1'),(162,2,'logout','authentication',2,NULL,'2026-09-12 15:25:13','::1'),(163,3,'login','authentication',3,'{\"ip\": \"::1\"}','2026-09-12 15:25:13','::1'),(164,3,'logout','authentication',3,NULL,'2026-09-12 15:25:13','::1'),(165,4,'login','authentication',4,'{\"ip\": \"::1\"}','2026-09-12 15:25:13','::1'),(166,4,'logout','authentication',4,NULL,'2026-09-12 15:25:14','::1'),(167,5,'login','authentication',5,'{\"ip\": \"::1\"}','2026-09-12 15:25:14','::1'),(168,5,'logout','authentication',5,NULL,'2026-09-12 15:25:14','::1'),(169,1,'login','authentication',1,'{\"ip\": \"::1\"}','2026-09-12 15:25:44','::1'),(170,1,'uploaded','staff_document',7,'{\"title\": \"Joining Agreement\", \"filename\": \"joining_agreement.pdf\", \"staff_id\": 11}','2026-09-12 15:25:44','::1'),(171,1,'created','staff',11,'{\"name\": \"Test Stylist Aarti\", \"staff_code\": \"STF-20260912-73AF\", \"designation\": \"Colour Specialist\"}','2026-09-12 15:25:44','::1'),(172,1,'updated','staff',11,'{\"name\": \"Aarti Sen (Updated)\", \"is_active\": 1, \"designation\": \"Lead Colour Specialist\"}','2026-09-12 15:25:44','::1'),(173,1,'uploaded','staff_document',8,'{\"title\": \"Cosmetology Diploma\", \"filename\": \"cosmetology_diploma.pdf\", \"staff_id\": 11}','2026-09-12 15:25:44','::1'),(174,1,'deleted','staff_document',8,'{\"title\": \"Cosmetology Diploma\", \"staff_id\": 11}','2026-09-12 15:25:44','::1'),(175,1,'created','invoice',16,'{\"grand_total\": 682.5, \"invoice_number\": \"INV-20260912-5FF16A\"}','2026-09-12 15:25:45','::1'),(176,1,'finalized','invoices',16,'{\"customer_id\": 1, \"paid_amount\": 682.5}','2026-09-12 15:25:45','::1'),(177,1,'logout','authentication',1,NULL,'2026-09-12 15:25:45','::1'),(178,1,'logout','authentication',1,NULL,'2026-09-12 15:42:39','::1');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commissions`
--

DROP TABLE IF EXISTS `commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_item_id` bigint unsigned NOT NULL,
  `staff_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `commission_type` enum('percentage','fixed') NOT NULL,
  `commission_rate` decimal(12,2) NOT NULL,
  `commission_amount` decimal(12,2) NOT NULL,
  `status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_item_id` (`invoice_item_id`),
  KEY `staff_id` (`staff_id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `commissions_ibfk_1` FOREIGN KEY (`invoice_item_id`) REFERENCES `invoice_items` (`id`),
  CONSTRAINT `commissions_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`),
  CONSTRAINT `commissions_ibfk_3` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commissions`
--

LOCK TABLES `commissions` WRITE;
/*!40000 ALTER TABLE `commissions` DISABLE KEYS */;
INSERT INTO `commissions` VALUES (1,1,1,1,'percentage',12.00,78.00,'unpaid',NULL,'2026-09-10 08:02:34'),(2,4,2,2,'percentage',10.00,145.00,'unpaid',NULL,'2026-09-10 08:02:34'),(3,5,3,3,'percentage',10.00,320.00,'unpaid',NULL,'2026-09-10 08:02:34'),(4,7,4,4,'percentage',15.00,277.50,'unpaid',NULL,'2026-09-10 08:02:34'),(5,8,5,5,'percentage',8.00,68.00,'unpaid',NULL,'2026-09-10 08:02:34'),(6,10,1,6,'percentage',12.00,90.00,'unpaid',NULL,'2026-09-10 08:02:34'),(7,12,2,7,'percentage',10.00,120.00,'unpaid',NULL,'2026-09-10 08:02:34'),(8,14,3,8,'percentage',10.00,350.00,'unpaid',NULL,'2026-09-10 08:02:34'),(9,15,4,9,'percentage',15.00,150.00,'unpaid',NULL,'2026-09-10 08:02:34'),(10,17,5,10,'percentage',8.00,40.00,'unpaid',NULL,'2026-09-10 08:02:34'),(11,18,1,11,'percentage',12.00,14.40,'unpaid',NULL,'2026-09-10 08:02:34'),(12,21,2,12,'percentage',10.00,65.00,'unpaid',NULL,'2026-09-10 08:02:34'),(13,22,4,13,'percentage',15.00,18.00,'unpaid',NULL,'2026-09-11 10:25:23'),(14,23,6,14,'percentage',5.00,37.50,'unpaid',NULL,'2026-09-11 10:35:11'),(15,24,1,15,'percentage',12.00,78.00,'unpaid',NULL,'2026-09-12 15:24:04'),(16,25,1,16,'percentage',12.00,78.00,'unpaid',NULL,'2026-09-12 15:25:45');
/*!40000 ALTER TABLE `commissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_notes`
--

DROP TABLE IF EXISTS `customer_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `note` text NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `customer_notes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_notes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_notes`
--

LOCK TABLES `customer_notes` WRITE;
/*!40000 ALTER TABLE `customer_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `notes` text,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dob` date DEFAULT NULL,
  `address` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_code` (`customer_code`),
  KEY `mobile` (`mobile`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'CUS-20260910-76A4','Debottam Talukdar','9876543210','debottam@example.com','Male','Hello!!',1,'2026-09-10 07:42:33','2026-09-10 07:42:33',NULL,NULL),(2,'CUS-DEMO-01','Aarohi Gupta','9810010001','customer1@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1990-01-01','Kolkata, West Bengal'),(3,'CUS-DEMO-02','Ishita Singh','9810010002','customer2@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1991-02-02','Kolkata, West Bengal'),(4,'CUS-DEMO-03','Nandini Rao','9810010003','customer3@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1992-03-03','Kolkata, West Bengal'),(5,'CUS-DEMO-04','Sana Khan','9810010004','customer4@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1993-04-04','Kolkata, West Bengal'),(6,'CUS-DEMO-05','Pooja Bansal','9810010005','customer5@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1994-05-05','Kolkata, West Bengal'),(7,'CUS-DEMO-06','Kritika Jain','9810010006','customer6@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1995-06-06','Kolkata, West Bengal'),(8,'CUS-DEMO-07','Anjali Menon','9810010007','customer7@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1996-07-07','Kolkata, West Bengal'),(9,'CUS-DEMO-08','Simran Kaur','9810010008','customer8@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1997-08-08','Kolkata, West Bengal'),(10,'CUS-DEMO-09','Tanvi Desai','9810010009','customer9@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1990-09-09','Kolkata, West Bengal'),(11,'CUS-DEMO-10','Rhea Chatterjee','9810010010','customer10@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1991-10-10','Kolkata, West Bengal'),(12,'CUS-DEMO-11','Sneha Kulkarni','9810010011','customer11@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1992-11-11','Kolkata, West Bengal'),(13,'CUS-DEMO-12','Divya Patel','9810010012','customer12@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1993-12-12','Kolkata, West Bengal'),(14,'CUS-DEMO-13','Mitali Sinha','9810010013','customer13@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1994-01-13','Kolkata, West Bengal'),(15,'CUS-DEMO-14','Shreya Bose','9810010014','customer14@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1995-02-14','Kolkata, West Bengal'),(16,'CUS-DEMO-15','Aditi Joshi','9810010015','customer15@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1996-03-15','Kolkata, West Bengal'),(17,'CUS-DEMO-16','Manisha Reddy','9810010016','customer16@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1997-04-16','Kolkata, West Bengal'),(18,'CUS-DEMO-17','Nisha Agarwal','9810010017','customer17@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1990-05-17','Kolkata, West Bengal'),(19,'CUS-DEMO-18','Bhavna Shah','9810010018','customer18@veloraparlour.demo','female','Velora demo customer',1,'2026-09-10 08:02:34','2026-09-10 08:02:34','1991-06-18','Kolkata, West Bengal'),(20,'CUS-20260911-268B','Susmita Das','9764581323','susmita@example.com','Female','',1,'2026-09-11 10:33:30','2026-09-11 10:33:30',NULL,NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discounts`
--

DROP TABLE IF EXISTS `discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `code` varchar(40) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(12,2) NOT NULL,
  `starts_on` date DEFAULT NULL,
  `ends_on` date DEFAULT NULL,
  `minimum_bill` decimal(12,2) NOT NULL DEFAULT '0.00',
  `maximum_discount` decimal(12,2) DEFAULT NULL,
  `usage_limit` int unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discounts`
--

LOCK TABLES `discounts` WRITE;
/*!40000 ALTER TABLE `discounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `discounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_categories`
--

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
INSERT INTO `expense_categories` VALUES (1,'Rent',1),(2,'Utilities',1),(3,'Salary',1),(4,'Maintenance',1),(5,'Marketing',1),(6,'Cleaning',1),(7,'Miscellaneous',1);
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `expense_code` varchar(30) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `expense_date` date NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `category_id` bigint unsigned DEFAULT NULL,
  `payment_method_id` bigint unsigned DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expense_code` (`expense_code`),
  KEY `created_by` (`created_by`),
  KEY `fk_expense_category` (`category_id`),
  KEY `fk_expense_payment_method` (`payment_method_id`),
  CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_expense_category` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_expense_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,'EXP-DEMO-01','Studio rent',28000.00,'2026-08-21',2,'2026-09-10 08:02:34',1,1,'EXP-DEMO-01',NULL),(2,'EXP-DEMO-02','Electricity bill',4200.00,'2026-08-29',2,'2026-09-10 08:02:34',2,1,'EXP-DEMO-02',NULL),(3,'EXP-DEMO-03','Deep cleaning supplies',1650.00,'2026-09-03',2,'2026-09-10 08:02:34',6,1,'EXP-DEMO-03',NULL),(4,'EXP-DEMO-04','Instagram campaign',3200.00,'2026-09-07',2,'2026-09-10 08:02:34',5,1,'EXP-DEMO-04',NULL),(5,'EXP-DEMO-05','Chair maintenance',1800.00,'2026-09-09',2,'2026-09-10 08:02:34',4,1,'EXP-DEMO-05',NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `line_no` int unsigned NOT NULL,
  `item_type` enum('service','product') NOT NULL,
  `service_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `staff_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_id` (`invoice_id`,`line_no`),
  KEY `service_id` (`service_id`),
  KEY `product_id` (`product_id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `invoice_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `invoice_items_ibfk_4` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (1,1,1,'service',1,NULL,1,'Signature Haircut',1.000,650.00,5.00,32.50,682.50),(2,1,2,'product',NULL,1,NULL,'Velora Nourish Shampoo',1.000,480.00,4.00,19.20,499.20),(3,1,3,'product',NULL,4,NULL,'Repair Hair Mask',2.000,760.00,5.00,76.00,1596.00),(4,2,1,'service',2,NULL,2,'Hair Spa Ritual',1.000,1450.00,5.00,72.50,1522.50),(5,3,1,'service',3,NULL,3,'Global Hair Colour',1.000,3200.00,5.00,160.00,3360.00),(6,3,2,'product',NULL,3,NULL,'Argan Hair Serum',1.000,690.00,5.00,34.50,724.50),(7,4,1,'service',4,NULL,4,'Hydra Glow Facial',1.000,1850.00,5.00,92.50,1942.50),(8,5,1,'service',5,NULL,5,'Fruit Cleanup',1.000,850.00,5.00,42.50,892.50),(9,5,2,'product',NULL,5,NULL,'Professional Hair Colour',1.000,410.00,5.00,20.50,430.50),(10,6,1,'service',6,NULL,1,'Classic Manicure',1.000,750.00,5.00,37.50,787.50),(11,6,2,'product',NULL,2,NULL,'Velora Silk Conditioner',2.000,520.00,4.00,41.60,1081.60),(12,7,1,'service',7,NULL,2,'Gel Pedicure',1.000,1200.00,5.00,60.00,1260.00),(13,7,2,'product',NULL,7,NULL,'Radiance Facial Cream',1.000,650.00,5.00,32.50,682.50),(14,8,1,'service',8,NULL,3,'Party Makeup',1.000,3500.00,5.00,175.00,3675.00),(15,9,1,'service',9,NULL,4,'Bridal Makeup Consultation',1.000,1000.00,5.00,50.00,1050.00),(16,9,2,'product',NULL,2,NULL,'Velora Silk Conditioner',1.000,520.00,4.00,20.80,540.80),(17,10,1,'service',10,NULL,5,'Head Massage',1.000,500.00,5.00,25.00,525.00),(18,11,1,'service',11,NULL,1,'Eyebrow Threading',1.000,120.00,5.00,6.00,126.00),(19,11,2,'product',NULL,4,NULL,'Repair Hair Mask',1.000,760.00,5.00,38.00,798.00),(20,11,3,'product',NULL,7,NULL,'Radiance Facial Cream',2.000,650.00,5.00,65.00,1365.00),(21,12,1,'service',12,NULL,2,'Full Arms Waxing',1.000,650.00,5.00,32.50,682.50),(22,13,1,'service',11,NULL,4,'Eyebrow Threading',1.000,120.00,5.00,6.00,126.00),(23,14,1,'service',6,NULL,6,'Classic Manicure',1.000,750.00,5.00,37.50,787.50),(24,15,1,'service',1,NULL,1,'Signature Haircut',1.000,650.00,5.00,32.50,682.50),(25,16,1,'service',1,NULL,1,'Signature Haircut',1.000,650.00,5.00,32.50,682.50);
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(40) NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `cashier_id` bigint unsigned DEFAULT NULL,
  `appointment_id` bigint unsigned DEFAULT NULL,
  `status` enum('finalized','cancelled') NOT NULL DEFAULT 'finalized',
  `subtotal` decimal(12,2) NOT NULL,
  `discount_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `rounding` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL,
  `paid_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `balance_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  UNIQUE KEY `appointment_id` (`appointment_id`),
  KEY `customer_id` (`customer_id`),
  KEY `cashier_id` (`cashier_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,'INV-20260910-062655',2,2,NULL,'finalized',2650.00,0.00,127.70,0.00,2777.70,2777.70,0.00,'2026-08-30 05:00:00'),(2,'INV-20260910-EB14C1',3,2,NULL,'finalized',1450.00,0.00,72.50,0.00,1522.50,1522.50,0.00,'2026-08-31 06:00:00'),(3,'INV-20260910-43E4DA',4,2,NULL,'finalized',3890.00,0.00,194.50,0.00,4084.50,4084.50,0.00,'2026-09-01 07:00:00'),(4,'INV-20260910-2BF413',5,2,NULL,'finalized',1850.00,0.00,92.50,0.00,1942.50,1942.50,0.00,'2026-09-02 08:00:00'),(5,'INV-20260910-E16161',6,2,NULL,'finalized',1260.00,0.00,63.00,0.00,1323.00,1323.00,0.00,'2026-09-03 09:00:00'),(6,'INV-20260910-2F78E5',7,2,NULL,'finalized',1790.00,0.00,79.10,0.00,1869.10,1869.10,0.00,'2026-09-04 10:00:00'),(7,'INV-20260910-C5F42D',8,2,NULL,'finalized',1850.00,0.00,92.50,0.00,1942.50,1942.50,0.00,'2026-09-05 11:00:00'),(8,'INV-20260910-6869FD',9,2,NULL,'finalized',3500.00,0.00,175.00,0.00,3675.00,3675.00,0.00,'2026-09-06 05:00:00'),(9,'INV-20260910-24E1D1',10,2,NULL,'finalized',1520.00,0.00,70.80,0.00,1590.80,1590.80,0.00,'2026-09-07 06:00:00'),(10,'INV-20260910-A77D17',11,2,NULL,'finalized',500.00,0.00,25.00,0.00,525.00,525.00,0.00,'2026-09-08 07:00:00'),(11,'INV-20260910-50605F',12,2,NULL,'finalized',2180.00,0.00,109.00,0.00,2289.00,2289.00,0.00,'2026-09-09 08:00:00'),(12,'INV-20260910-5E0F12',13,2,NULL,'finalized',650.00,0.00,32.50,0.00,682.50,682.50,0.00,'2026-09-10 09:00:00'),(13,'INV-20260911-2BB008',5,3,NULL,'finalized',120.00,0.00,6.00,0.00,126.00,100.00,26.00,'2026-09-11 10:25:23'),(14,'INV-20260911-84F712',20,1,NULL,'finalized',750.00,0.00,37.50,0.00,787.50,750.00,37.50,'2026-09-11 10:35:11'),(15,'INV-20260912-5B686A',1,1,NULL,'finalized',650.00,0.00,32.50,0.00,682.50,682.50,0.00,'2026-09-12 15:24:04'),(16,'INV-20260912-5FF16A',1,1,NULL,'finalized',650.00,0.00,32.50,0.00,682.50,682.50,0.00,'2026-09-12 15:25:45');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration` (`migration`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'20260831000100_create_system_metadata_table','2026-09-10 02:09:28'),(2,'20260907000100_create_pos_schema','2026-09-10 02:09:29'),(3,'20260909000200_complete_operations_modules','2026-09-10 02:09:29'),(4,'20260911000100_pdf_compliance_extensions','2026-09-10 14:03:08'),(5,'20260911000200_auth_rbac_demo_accounts','2026-09-10 14:35:57'),(6,'20260912000100_create_staff_documents_and_photos','2026-09-12 09:31:03');
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`expires_at`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES (1,3,'eb45615ab13be077bf13fb8a30cef39bf7493d8b7396b2f50b8333efcdc59bcc','2026-09-11 02:41:30','2026-09-11 01:41:30','2026-09-10 20:11:30'),(2,3,'7b8ff9b7bbc04d01ee341b9e780a0392e24932d01ed34174a621cd98b1b6e5bb','2026-09-11 02:44:10','2026-09-11 01:44:10','2026-09-10 20:14:10'),(3,3,'ed593c8e15bb63742660450a96e2aabbc1bcd0edd9562dee5be0854e01637b92','2026-09-11 02:59:14','2026-09-11 01:59:15','2026-09-10 20:29:14');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(80) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (1,'cash','Cash',1),(2,'upi','UPI',1),(3,'card','Card',1),(4,'bank','Bank Transfer',1);
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_code` varchar(40) NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `payment_method_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `received_by` bigint unsigned DEFAULT NULL,
  `paid_at` datetime NOT NULL,
  `status` enum('captured','void') NOT NULL DEFAULT 'captured',
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_code` (`payment_code`),
  KEY `invoice_id` (`invoice_id`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `received_by` (`received_by`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'PAY-DEMO-01',1,1,2777.70,NULL,2,'2026-08-30 10:30:00','captured'),(2,'PAY-DEMO-02',2,2,1522.50,NULL,2,'2026-08-31 11:30:00','captured'),(3,'PAY-DEMO-03',3,3,4084.50,NULL,2,'2026-09-01 12:30:00','captured'),(4,'PAY-DEMO-04',4,1,1942.50,NULL,2,'2026-09-02 13:30:00','captured'),(5,'PAY-DEMO-05',5,2,1323.00,NULL,2,'2026-09-03 14:30:00','captured'),(6,'PAY-DEMO-06',6,3,1869.10,NULL,2,'2026-09-04 15:30:00','captured'),(7,'PAY-DEMO-07',7,1,1942.50,NULL,2,'2026-09-05 16:30:00','captured'),(8,'PAY-DEMO-08',8,2,3675.00,NULL,2,'2026-09-06 10:30:00','captured'),(9,'PAY-DEMO-09',9,3,1590.80,NULL,2,'2026-09-07 11:30:00','captured'),(10,'PAY-DEMO-10',10,1,525.00,NULL,2,'2026-09-08 12:30:00','captured'),(11,'PAY-DEMO-11',11,2,2289.00,NULL,2,'2026-09-09 13:30:00','captured'),(12,'PAY-DEMO-12',12,3,682.50,NULL,2,'2026-09-10 14:30:00','captured'),(13,'PAY-20260911-CC544E',13,1,100.00,NULL,3,'2026-09-11 15:55:23','captured'),(14,'PAY-20260911-D78D89',14,1,750.00,NULL,1,'2026-09-11 16:05:11','captured'),(15,'PAY-20260912-A32059',15,1,682.50,NULL,1,'2026-09-12 20:54:04','captured'),(16,'PAY-20260912-F6B94A',16,1,682.50,NULL,1,'2026-09-12 20:55:45','captured');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (3,'appointments.manage'),(14,'audit.view'),(6,'catalog.manage'),(5,'catalog.view'),(15,'commissions.view'),(2,'customers.manage'),(1,'dashboard.view'),(10,'expenses.manage'),(8,'inventory.manage'),(4,'pos.use'),(9,'purchases.manage'),(16,'refunds.manage'),(11,'reports.view'),(12,'settings.manage'),(7,'staff.manage'),(13,'users.manage');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,'Hair Care',1),(2,'Skin Care',1),(3,'Colour & Styling',1),(4,'Nail Care',1);
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_code` varchar(30) NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `sku` varchar(80) DEFAULT NULL,
  `selling_price` decimal(12,2) NOT NULL,
  `purchase_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `current_stock` decimal(12,3) NOT NULL DEFAULT '0.000',
  `minimum_stock` decimal(12,3) NOT NULL DEFAULT '0.000',
  `unit` varchar(20) NOT NULL DEFAULT 'pcs',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `barcode` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  UNIQUE KEY `sku` (`sku`),
  UNIQUE KEY `barcode` (`barcode`),
  KEY `category_id` (`category_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'PRD-DEMO-01',1,1,'Velora Nourish Shampoo','VLR-SHP-250',480.00,325.00,4.00,7.000,8.000,'pcs',1,'890ad056065d3','Velora Pro'),(2,'PRD-DEMO-02',1,1,'Velora Silk Conditioner','VLR-CON-250',520.00,340.00,4.00,9.000,8.000,'pcs',1,'8902c94206580','Velora Pro'),(3,'PRD-DEMO-03',1,2,'Argan Hair Serum','VLR-SER-50',690.00,420.00,5.00,15.000,5.000,'pcs',1,'890b1be49e73a','Velora Pro'),(4,'PRD-DEMO-04',1,2,'Repair Hair Mask','VLR-MSK-200',760.00,480.00,5.00,18.000,4.000,'pcs',1,'890070daafb65','Velora Pro'),(5,'PRD-DEMO-05',3,3,'Professional Hair Colour','VLR-COL-100',410.00,250.00,5.00,23.000,12.000,'pcs',1,'89067d5be2a35','Velora Pro'),(6,'PRD-DEMO-06',2,1,'Gentle Face Wash','VLR-FW-100',320.00,190.00,5.00,8.000,7.000,'pcs',1,'890524c71ef32','Velora Pro'),(7,'PRD-DEMO-07',2,2,'Radiance Facial Cream','VLR-FC-50',650.00,380.00,5.00,9.000,5.000,'pcs',1,'890f05d8b157d','Velora Pro'),(8,'PRD-DEMO-08',2,2,'Daily Defence Sunscreen','VLR-SS-50',540.00,310.00,5.00,16.000,3.000,'pcs',1,'89003ce21ad4f','Velora Pro'),(9,'PRD-DEMO-09',2,3,'Aloe Vera Wax','VLR-WAX-500',450.00,280.00,5.00,28.000,2.000,'pcs',1,'8901f3fbf9ff6','Velora Pro'),(10,'PRD-DEMO-10',4,3,'Cuticle Oil','VLR-CO-15',290.00,150.00,5.00,25.000,3.000,'pcs',1,'890864fae539a','Velora Pro');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_items`
--

DROP TABLE IF EXISTS `purchase_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_items`
--

LOCK TABLES `purchase_items` WRITE;
/*!40000 ALTER TABLE `purchase_items` DISABLE KEYS */;
INSERT INTO `purchase_items` VALUES (1,1,1,8.000,325.00,4.00,2704.00),(2,2,2,12.000,340.00,4.00,4243.20),(3,3,3,16.000,420.00,5.00,7056.00),(4,4,4,20.000,480.00,5.00,10080.00),(5,5,5,24.000,250.00,5.00,6300.00),(6,6,6,8.000,190.00,5.00,1596.00),(7,7,7,12.000,380.00,5.00,4788.00),(8,8,8,16.000,310.00,5.00,5208.00),(9,9,9,20.000,280.00,5.00,5880.00),(10,10,10,24.000,150.00,5.00,3780.00),(11,11,4,1.000,480.00,5.00,504.00),(12,12,10,1.000,150.00,5.00,157.50);
/*!40000 ALTER TABLE `purchase_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_number` varchar(40) NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `supplier_invoice_no` varchar(100) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_number` (`purchase_number`),
  UNIQUE KEY `supplier_id` (`supplier_id`,`supplier_invoice_no`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (1,'PUR-DEMO-01',1,'DEMO-0','2026-08-13',2600.00,104.00,2704.00,'paid',2,'2026-09-10 08:02:34'),(2,'PUR-DEMO-02',2,'DEMO-1','2026-08-14',4080.00,163.20,4243.20,'paid',2,'2026-09-10 08:02:34'),(3,'PUR-DEMO-03',3,'DEMO-2','2026-08-15',6720.00,336.00,7056.00,'paid',2,'2026-09-10 08:02:34'),(4,'PUR-DEMO-04',1,'DEMO-3','2026-08-16',9600.00,480.00,10080.00,'paid',2,'2026-09-10 08:02:34'),(5,'PUR-DEMO-05',2,'DEMO-4','2026-08-17',6000.00,300.00,6300.00,'paid',2,'2026-09-10 08:02:34'),(6,'PUR-DEMO-06',3,'DEMO-5','2026-08-18',1520.00,76.00,1596.00,'paid',2,'2026-09-10 08:02:34'),(7,'PUR-DEMO-07',1,'DEMO-6','2026-08-19',4560.00,228.00,4788.00,'paid',2,'2026-09-10 08:02:34'),(8,'PUR-DEMO-08',2,'DEMO-7','2026-08-20',4960.00,248.00,5208.00,'paid',2,'2026-09-10 08:02:34'),(9,'PUR-DEMO-09',3,'DEMO-8','2026-08-21',5600.00,280.00,5880.00,'paid',2,'2026-09-10 08:02:34'),(10,'PUR-DEMO-10',1,'DEMO-9','2026-08-22',3600.00,180.00,3780.00,'paid',2,'2026-09-10 08:02:34'),(11,'PUR-20260911-77FE7C',2,'10','2026-09-11',480.00,24.00,504.00,'paid',3,'2026-09-11 10:26:44'),(12,'PUR-20260911-1CB6AE',3,'15','2026-09-11',150.00,7.50,157.50,'partial',3,'2026-09-11 10:27:21');
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refund_items`
--

DROP TABLE IF EXISTS `refund_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refund_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `refund_id` bigint unsigned NOT NULL,
  `invoice_item_id` bigint unsigned NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `return_to_stock` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `refund_id` (`refund_id`),
  KEY `invoice_item_id` (`invoice_item_id`),
  CONSTRAINT `refund_items_ibfk_1` FOREIGN KEY (`refund_id`) REFERENCES `refunds` (`id`) ON DELETE CASCADE,
  CONSTRAINT `refund_items_ibfk_2` FOREIGN KEY (`invoice_item_id`) REFERENCES `invoice_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refund_items`
--

LOCK TABLES `refund_items` WRITE;
/*!40000 ALTER TABLE `refund_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `refund_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refunds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `refund_number` varchar(40) NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `refund_method_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `status` enum('completed','void') NOT NULL DEFAULT 'completed',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `refund_number` (`refund_number`),
  KEY `invoice_id` (`invoice_id`),
  KEY `refund_method_id` (`refund_method_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  CONSTRAINT `refunds_ibfk_2` FOREIGN KEY (`refund_method_id`) REFERENCES `payment_methods` (`id`),
  CONSTRAINT `refunds_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(1,2),(2,2),(3,2),(5,2),(1,3),(2,3),(3,3),(4,3),(5,3),(1,4),(2,4),(3,4),(5,4),(1,5),(2,5),(3,5),(5,5),(1,6),(2,6),(1,7),(2,7),(1,8),(2,8),(1,9),(2,9),(1,10),(2,10),(1,11),(2,11),(1,12),(1,13),(1,14),(1,15),(2,15),(4,15),(1,16),(2,16);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Super Admin','2026-09-10 07:39:28'),(2,'Manager','2026-09-10 07:39:28'),(3,'Cashier','2026-09-10 07:39:28'),(4,'Staff','2026-09-10 07:39:28'),(5,'Receptionist / Cashier','2026-09-10 19:33:08');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_categories`
--

DROP TABLE IF EXISTS `service_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_categories`
--

LOCK TABLES `service_categories` WRITE;
/*!40000 ALTER TABLE `service_categories` DISABLE KEYS */;
INSERT INTO `service_categories` VALUES (1,'Hair Studio',1),(2,'Skin & Facial',1),(3,'Nails',1),(4,'Makeup',1),(5,'Wellness',1);
/*!40000 ALTER TABLE `service_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service_code` varchar(30) NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `duration_minutes` int unsigned NOT NULL DEFAULT '30',
  `price` decimal(12,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `commission_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `commission_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_code` (`service_code`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'SVC-DEMO-01',1,'Signature Haircut',45,650.00,5.00,'percentage',10.00,1,'Professional Signature Haircut service'),(2,'SVC-DEMO-02',1,'Hair Spa Ritual',60,1450.00,5.00,'percentage',10.00,1,'Professional Hair Spa Ritual service'),(3,'SVC-DEMO-03',1,'Global Hair Colour',120,3200.00,5.00,'percentage',10.00,1,'Professional Global Hair Colour service'),(4,'SVC-DEMO-04',2,'Hydra Glow Facial',75,1850.00,5.00,'percentage',10.00,1,'Professional Hydra Glow Facial service'),(5,'SVC-DEMO-05',2,'Fruit Cleanup',45,850.00,5.00,'percentage',10.00,1,'Professional Fruit Cleanup service'),(6,'SVC-DEMO-06',3,'Classic Manicure',45,750.00,5.00,'percentage',10.00,1,'Professional Classic Manicure service'),(7,'SVC-DEMO-07',3,'Gel Pedicure',60,1200.00,5.00,'percentage',10.00,1,'Professional Gel Pedicure service'),(8,'SVC-DEMO-08',4,'Party Makeup',75,3500.00,5.00,'percentage',10.00,1,'Professional Party Makeup service'),(9,'SVC-DEMO-09',4,'Bridal Makeup Consultation',45,1000.00,5.00,'percentage',10.00,1,'Professional Bridal Makeup Consultation service'),(10,'SVC-DEMO-10',5,'Head Massage',30,500.00,5.00,'percentage',10.00,1,'Professional Head Massage service'),(11,'SVC-DEMO-11',2,'Eyebrow Threading',15,120.00,5.00,'percentage',10.00,1,'Professional Eyebrow Threading service'),(12,'SVC-DEMO-12',2,'Full Arms Waxing',40,650.00,5.00,'percentage',10.00,1,'Professional Full Arms Waxing service');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'business_name','Parlour POS','2026-09-10 07:39:29'),(2,'allow_negative_stock','0','2026-09-10 07:39:29');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `commission_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `commission_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(190) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` text,
  `joining_date` date DEFAULT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `profile_photo_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_code` (`staff_code`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,'STF-DEMO-01','Priya Sharma','9876543210','Senior Hair Stylist','percentage',12.00,1,'2026-09-10 08:02:34',NULL,NULL,NULL,NULL,NULL,NULL,'/uploads/staff/photos/staff_priya_sharma.jpg'),(2,'STF-DEMO-02','Neha Kapoor','9876543211','Skin Therapist','percentage',10.00,1,'2026-09-10 08:02:34',NULL,NULL,NULL,NULL,NULL,NULL,'/uploads/staff/photos/staff_neha_kapoor.jpg'),(3,'STF-DEMO-03','Riya Verma','9876543212','Nail Artist','percentage',10.00,1,'2026-09-10 08:02:34',NULL,NULL,NULL,NULL,NULL,NULL,'/uploads/staff/photos/staff_riya_verma.jpg'),(4,'STF-DEMO-04','Kavya Iyer','9876543213','Makeup Artist','percentage',15.00,1,'2026-09-10 08:02:34',NULL,NULL,NULL,NULL,NULL,NULL,'/uploads/staff/photos/staff_kavya_iyer.jpg'),(5,'STF-DEMO-05','Meera Nair','9876543214','Salon Associate','percentage',8.00,1,'2026-09-10 08:02:34',NULL,NULL,NULL,NULL,NULL,NULL,'/uploads/staff/photos/staff_meera_nair.jpg'),(6,'STF-20260911-8986','Debarpan Dutta Banik','9876543210','hair washer','percentage',5.00,1,'2026-09-11 10:19:43',NULL,NULL,NULL,NULL,NULL,NULL,'/uploads/staff/photos/staff_debarpan_dutta.jpg');
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_documents`
--

DROP TABLE IF EXISTS `staff_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint unsigned NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int unsigned NOT NULL DEFAULT '0',
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'application/octet-stream',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_staff_docs_staff_id` (`staff_id`),
  KEY `fk_staff_docs_user` (`created_by`),
  CONSTRAINT `fk_staff_docs_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_staff_docs_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_documents`
--

LOCK TABLES `staff_documents` WRITE;
/*!40000 ALTER TABLE `staff_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `movement_type` enum('purchase','sale','adjustment','damage','return_in','return_out') NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `before_quantity` decimal(12,3) NOT NULL,
  `after_quantity` decimal(12,3) NOT NULL,
  `reference_type` varchar(40) DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`,`created_at`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,1,'purchase',8.000,0.000,8.000,'purchase',1,'Opening demo purchase',2,'2026-08-13 04:30:00'),(2,2,'purchase',12.000,0.000,12.000,'purchase',2,'Opening demo purchase',2,'2026-08-14 04:30:00'),(3,3,'purchase',16.000,0.000,16.000,'purchase',3,'Opening demo purchase',2,'2026-08-15 04:30:00'),(4,4,'purchase',20.000,0.000,20.000,'purchase',4,'Opening demo purchase',2,'2026-08-16 04:30:00'),(5,5,'purchase',24.000,0.000,24.000,'purchase',5,'Opening demo purchase',2,'2026-08-17 04:30:00'),(6,6,'purchase',8.000,0.000,8.000,'purchase',6,'Opening demo purchase',2,'2026-08-18 04:30:00'),(7,7,'purchase',12.000,0.000,12.000,'purchase',7,'Opening demo purchase',2,'2026-08-19 04:30:00'),(8,8,'purchase',16.000,0.000,16.000,'purchase',8,'Opening demo purchase',2,'2026-08-20 04:30:00'),(9,9,'purchase',20.000,0.000,20.000,'purchase',9,'Opening demo purchase',2,'2026-08-21 04:30:00'),(10,10,'purchase',24.000,0.000,24.000,'purchase',10,'Opening demo purchase',2,'2026-08-22 04:30:00'),(11,1,'sale',-1.000,8.000,7.000,'invoice',1,NULL,2,'2026-09-10 08:02:34'),(12,4,'sale',-2.000,20.000,18.000,'invoice',1,NULL,2,'2026-09-10 08:02:34'),(13,3,'sale',-1.000,16.000,15.000,'invoice',3,NULL,2,'2026-09-10 08:02:34'),(14,5,'sale',-1.000,24.000,23.000,'invoice',5,NULL,2,'2026-09-10 08:02:34'),(15,2,'sale',-2.000,12.000,10.000,'invoice',6,NULL,2,'2026-09-10 08:02:34'),(16,7,'sale',-1.000,12.000,11.000,'invoice',7,NULL,2,'2026-09-10 08:02:34'),(17,2,'sale',-1.000,10.000,9.000,'invoice',9,NULL,2,'2026-09-10 08:02:34'),(18,4,'sale',-1.000,18.000,17.000,'invoice',11,NULL,2,'2026-09-10 08:02:34'),(19,7,'sale',-2.000,11.000,9.000,'invoice',11,NULL,2,'2026-09-10 08:02:34'),(20,9,'adjustment',18.000,20.000,38.000,NULL,NULL,'Physical stock count correction',1,'2026-09-11 10:05:09'),(21,9,'adjustment',-10.000,38.000,28.000,NULL,NULL,'Physical stock count correction',1,'2026-09-11 10:06:11'),(22,4,'purchase',1.000,17.000,18.000,'purchase',11,'Purchase receipt',3,'2026-09-11 10:26:44'),(23,10,'purchase',1.000,24.000,25.000,'purchase',12,'Purchase receipt',3,'2026-09-11 10:27:21');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `mobile` varchar(30) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email` varchar(190) DEFAULT NULL,
  `address` text,
  `gstin` varchar(40) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_code` (`supplier_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'SUP-DEMO-01','Glow Essentials Distributors','9898981001','Glow Essentials',1,NULL,NULL,NULL,NULL),(2,'SUP-DEMO-02','Luxe Beauty Supply','9898981002','Luxe Beauty Supply',1,NULL,NULL,NULL,NULL),(3,'SUP-DEMO-03','Professional Salon Mart','9898981003','Salon Mart India',1,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_metadata`
--

DROP TABLE IF EXISTS `system_metadata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_metadata` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `metadata_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_system_metadata_key` (`metadata_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_metadata`
--

LOCK TABLES `system_metadata` WRITE;
/*!40000 ALTER TABLE `system_metadata` DISABLE KEYS */;
INSERT INTO `system_metadata` VALUES (1,'velora_demo_seed_v1','2026-09-10T13:32:34+05:30','2026-09-10 08:02:34','2026-09-10 08:02:34');
/*!40000 ALTER TABLE `system_metadata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `staff_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `fk_users_staff` (`staff_id`),
  CONSTRAINT `fk_users_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Kiran Kundu','kkundu249@gmail.com','$2y$12$jNReXDkXtLYdrYlJGFD9w.kK6JnRdEh6ptzg5INxvEEELPExvuv7G',1,1,'2026-09-12 20:55:44','2026-09-10 07:40:25',NULL),(2,'Ananya Mehta','admin@veloraparlour.demo','$2y$12$ZmC.Yy7RZlGmvJt/IpN/Xe2cpGpRGT7qh/5jv8I.jgfe4DF7gzXxW',1,1,'2026-09-12 20:55:13','2026-09-10 08:02:34',NULL),(3,'Kavita Sen','manager@veloraparlour.demo','$2y$12$HsZU8y3iPKnxnbO0WmxkyOHfG9t492oOl8BnxEzlvcKsz/jwHzMe.',2,1,'2026-09-12 20:55:13','2026-09-10 20:05:57',NULL),(4,'Rohit Verma','receptionist@veloraparlour.demo','$2y$12$xPmmGDDHB1dek5vbcwATn.ef0uRWaKZpPa2yd2znRs4Wx5YkJUGXC',5,1,'2026-09-12 20:55:13','2026-09-10 20:05:57',NULL),(5,'Priya Sharma','staff@veloraparlour.demo','$2y$12$tBSjOm1AZ.Kns8ZmeYG2UOWnk/kp8JYwNZlg2OYQEXOS7H43WABWS',4,1,'2026-09-12 20:55:14','2026-09-10 20:05:57',1),(8,'Debarpan Dutta Banik','deba@example.com','$2y$12$/k1ZiYy1IciPbMgbb9FYl.Hk.aJMvyuboyezWsstyaMT5U8rN3Sze',4,1,'2026-09-11 16:09:48','2026-09-11 10:15:19',6);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-12 21:22:20
