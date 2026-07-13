-- MySQL dump 10.13  Distrib 5.7.29, for Linux (x86_64)
--
-- Host: localhost    Database: accounting
-- ------------------------------------------------------
-- Server version	5.7.29-0ubuntu0.18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `access_tokens`
--

DROP TABLE IF EXISTS `access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `access_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiry_time` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_tokens`
--

LOCK TABLES `access_tokens` WRITE;
/*!40000 ALTER TABLE `access_tokens` DISABLE KEYS */;
INSERT INTO `access_tokens` VALUES (1,1,'dxTGaFAzNYV8zlTi1581511937','1613047937','2020-02-07 09:43:16','2020-02-12 07:52:17');
/*!40000 ALTER TABLE `access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint(20) unsigned DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_log_log_name_index` (`log_name`),
  KEY `subject` (`subject_id`,`subject_type`),
  KEY `causer` (`causer_id`,`causer_type`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,'Sale added','Uzair Khan added new Sale ( sdsdsds )',1,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-11 09:34:19','2020-02-11 09:34:19'),(2,'Sale updated','Uzair Khan updated Sale ( sdsdsds )',1,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-11 09:36:58','2020-02-11 09:36:58'),(3,'Sale deleted','Uzair Khan deleted Sale ( sdsdsds )',1,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-11 09:37:36','2020-02-11 09:37:36'),(4,'Sale added','Uzair Khan added new Sale ( sdsdsds )',2,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-11 09:38:24','2020-02-11 09:38:24'),(5,'Expense added','Uzair Khan added new Expense ( Expense )',4,'App\\Models\\Expense',1,'App\\Models\\AppUser','expenses','[]','2020-02-11 09:39:10','2020-02-11 09:39:10'),(6,'Expense updated','Uzair Khan updated Expense ( Expense )',3,'App\\Models\\Expense',1,'App\\Models\\AppUser','expenses','[]','2020-02-11 09:39:44','2020-02-11 09:39:44'),(7,'Expense updated','Uzair Khan updated Expense ( Expense )',2,'App\\Models\\Expense',1,'App\\Models\\AppUser','expenses','[]','2020-02-11 09:41:29','2020-02-11 09:41:29'),(8,'Expense deleted','Uzair Khan deleted Expense ( Expense )',4,'App\\Models\\Expense',1,'App\\Models\\AppUser','expenses','[]','2020-02-11 09:43:02','2020-02-11 09:43:02'),(9,'Purchase added','Uzair Khan added new Purchase ( Purchase )',3,'App\\Models\\Purchase',1,'App\\Models\\AppUser','purchases','[]','2020-02-11 09:44:19','2020-02-11 09:44:19'),(10,'Purchase updated','Uzair Khan updated Purchase ( Purchase )',2,'App\\Models\\Purchase',1,'App\\Models\\AppUser','purchases','[]','2020-02-11 09:44:35','2020-02-11 09:44:35'),(11,'Purchase deleted','Uzair Khan deleted Purchase ( Purchase )',2,'App\\Models\\Purchase',1,'App\\Models\\AppUser','purchases','[]','2020-02-11 09:44:55','2020-02-11 09:44:55'),(12,'Inventory added','Uzair Khan added new Inventory ( asdasdas )',5,'App\\Models\\Inventory',1,'App\\Models\\AppUser','inventories','[]','2020-02-11 09:46:27','2020-02-11 09:46:27'),(13,'Inventory updated','Uzair Khan updated Inventory ( Ineventory )',5,'App\\Models\\Inventory',1,'App\\Models\\AppUser','inventories','[]','2020-02-11 09:46:58','2020-02-11 09:46:58'),(14,'Inventory deleted','Uzair Khan deleted Inventory ( Ineventory )',5,'App\\Models\\Inventory',1,'App\\Models\\AppUser','inventories','[]','2020-02-11 09:49:21','2020-02-11 09:49:21'),(15,'Owner Account added','Uzair Khan added new Owner Account ( Cash In )',1,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-11 10:00:20','2020-02-11 10:00:20'),(16,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',1,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-11 10:00:44','2020-02-11 10:00:44'),(17,'Inventory updated','Uzair Khan updated Inventory ( Ineventory )',3,'App\\Models\\Inventory',1,'App\\Models\\AppUser','inventories','[]','2020-02-11 10:01:30','2020-02-11 10:01:30'),(18,'Owner Account added','Uzair Khan added new Owner Account ( Cash In )',2,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-11 10:02:01','2020-02-11 10:02:01'),(19,'Owner Account deleted','Uzair Khan deleted Owner Account ( Cash In )',2,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-11 10:02:19','2020-02-11 10:02:19'),(20,'Password Changed','Uzair Khan changed password',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-11 10:20:46','2020-02-11 10:20:46'),(21,'Password Changed','Uzair Khan changed password',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-11 10:21:41','2020-02-11 10:21:41'),(23,'Language Changed','Uzair Khan changed language to App User',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-11 10:24:49','2020-02-11 10:24:49'),(24,'Language Changed','Uzair Khan changed App User language to en',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-11 10:29:33','2020-02-11 10:29:33'),(25,'Profile Updated','Uzair Khan update profile',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-11 10:33:57','2020-02-11 10:33:57'),(28,'Login','Uzair Khan logged in on 2020-02-07 14:43:16',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-11 10:45:02','2020-02-11 10:45:02'),(29,'Password Changed','Uzair Khan changed password',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-11 10:45:28','2020-02-11 10:45:28'),(30,'Language Changed','Uzair Khan changed App User language to en',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-11 11:02:49','2020-02-11 11:02:49'),(31,'Password Changed','Uzair Khan changed password',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-11 11:02:53','2020-02-11 11:02:53'),(32,'Expense updated','Uzair Khan updated Expense ( Expense )',3,'App\\Models\\Expense',1,'App\\Models\\AppUser','expenses','[]','2020-02-12 05:50:39','2020-02-12 05:50:39'),(33,'Sale updated','Uzair Khan updated Sale ( sdsdsds )',2,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-12 05:52:59','2020-02-12 05:52:59'),(34,'Sale updated','Uzair Khan updated Sale ( sdsdsds )',2,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-12 06:07:34','2020-02-12 06:07:34'),(35,'Sale updated','Uzair Khan updated Sale ( sdsdsds )',2,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-12 06:07:56','2020-02-12 06:07:56'),(36,'Sale updated','Uzair Khan updated Sale ( sdsdsds )',2,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-12 06:08:44','2020-02-12 06:08:44'),(37,'Sale updated','Uzair Khan updated Sale ( sdsdsds )',2,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-12 06:08:50','2020-02-12 06:08:50'),(38,'Sale updated','Uzair Khan updated Sale ( sdsdsds )',2,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-12 06:11:54','2020-02-12 06:11:54'),(39,'Sale updated','Uzair Khan updated Sale ( sdsdsds )',2,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-12 06:12:45','2020-02-12 06:12:45'),(40,'Sale updated','Uzair Khan updated Sale ( sdsdsds )',2,'App\\Models\\Sale',1,'App\\Models\\AppUser','sales','[]','2020-02-12 06:13:01','2020-02-12 06:13:01'),(41,'Expense updated','Uzair Khan updated Expense ( Expense )',3,'App\\Models\\Expense',1,'App\\Models\\AppUser','expenses','[]','2020-02-12 07:10:53','2020-02-12 07:10:53'),(42,'Expense updated','Uzair Khan updated Expense ( Expense )',3,'App\\Models\\Expense',1,'App\\Models\\AppUser','expenses','[]','2020-02-12 07:11:19','2020-02-12 07:11:19'),(43,'Owner Account added','Uzair Khan added new Owner Account ( Cash In )',3,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:12:05','2020-02-12 07:12:05'),(44,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',3,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:12:28','2020-02-12 07:12:28'),(45,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',3,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:12:44','2020-02-12 07:12:44'),(46,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',3,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:13:00','2020-02-12 07:13:00'),(47,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',3,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:13:08','2020-02-12 07:13:08'),(48,'Owner Account added','Uzair Khan added new Owner Account ( Cash In )',4,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:18:44','2020-02-12 07:18:44'),(49,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',3,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:19:02','2020-02-12 07:19:02'),(50,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',4,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:19:08','2020-02-12 07:19:08'),(51,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',4,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:19:20','2020-02-12 07:19:20'),(52,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',3,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:19:42','2020-02-12 07:19:42'),(53,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',3,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:19:50','2020-02-12 07:19:50'),(54,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',4,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:19:52','2020-02-12 07:19:52'),(55,'Owner Account updated','Uzair Khan updated Owner Account ( Cash Out )',4,'App\\Models\\OwnerAccount',1,'App\\Models\\AppUser','owner_accounts','[]','2020-02-12 07:52:03','2020-02-12 07:52:03'),(56,'Login','Uzair Khan logged in on 2020-02-07 14:43:16',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-12 07:52:17','2020-02-12 07:52:17'),(57,'Language Changed','Uzair Khan changed App User language to en',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-12 08:52:04','2020-02-12 08:52:04'),(58,'Language Changed','Uzair Khan changed App User language to ar',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-12 08:52:17','2020-02-12 08:52:17'),(59,'Language Changed','Uzair Khan changed App User language to ar',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-12 08:58:14','2020-02-12 08:58:14'),(60,'Language Changed','Uzair Khan changed App User language to en',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-12 08:59:30','2020-02-12 08:59:30'),(61,'Inventory updated','Uzair Khan updated Inventory ( Ineventory )',3,'App\\Models\\Inventory',1,'App\\Models\\AppUser','inventories','[]','2020-02-13 04:44:32','2020-02-13 04:44:32'),(62,'Inventory deleted','Uzair Khan deleted Inventory ( sds )',5,'App\\Models\\Inventory',1,'App\\Models\\AppUser','inventories','[]','2020-02-13 04:45:08','2020-02-13 04:45:08'),(63,'Language Changed','Uzair Khan changed App User language to ar',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-13 08:39:53','2020-02-13 08:39:53'),(64,'Language Changed','Uzair Khan changed App User language to en',1,'App\\Models\\AppUser',1,'App\\Models\\AppUser','app_users','[]','2020-02-13 08:40:06','2020-02-13 08:40:06');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_log_tags`
--

DROP TABLE IF EXISTS `activity_log_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `wildcards` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log_tags`
--

LOCK TABLES `activity_log_tags` WRITE;
/*!40000 ALTER TABLE `activity_log_tags` DISABLE KEYS */;
INSERT INTO `activity_log_tags` VALUES (1,'added','[USER] added new [module] ( [data] )','[module] added','[USER],[module],[data]',NULL,NULL),(2,'updated','[USER] updated [module] ( [data] )','[module] updated','[USER],[module],[data]',NULL,NULL),(3,'deleted','[USER] deleted [module] ( [data] )','[module] deleted','[USER],[module],[data]',NULL,NULL),(4,'password_changed','[USER] changed password','Password Changed','[USER]',NULL,NULL),(5,'language_changed','[USER] changed [module] language to [language]','Language Changed','[USER],[module],[language]',NULL,NULL),(6,'profile_updated','[USER] update profile','Profile Updated','[USER]',NULL,NULL),(7,'login','[USER] logged in on [DATE]','Login','[USER],[DATE]',NULL,NULL);
/*!40000 ALTER TABLE `activity_log_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_users`
--

DROP TABLE IF EXISTS `app_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `business_name` varchar(255) DEFAULT NULL,
  `industry_type` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `accounting_method` varchar(255) DEFAULT NULL,
  `company_year_end_date` date DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `is_verified` int(11) DEFAULT '0',
  `device_token` varchar(255) DEFAULT NULL,
  `contact` varchar(45) DEFAULT NULL,
  `language` varchar(45) DEFAULT 'en',
  `is_subscribed` tinyint(2) DEFAULT '0' COMMENT '0 OR 1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_users`
--

LOCK TABLES `app_users` WRITE;
/*!40000 ALTER TABLE `app_users` DISABLE KEYS */;
INSERT INTO `app_users` VALUES (1,'Uzair Khan','uzair_khan','uzair.khan@cubixlabs.com','$2y$10$/x4DQVQesYZn8DAy/Ubi7e1By6BFVEG4ROZ3Ucc9IJUX2PoM8zG8e','Cubix','ABC Industry','DHA Phase V','75500','Karachi','Pakistan','Cash Only','2020-02-07',1,NULL,1,'no_token','+92-3343362255','en',1,'2020-02-07 09:43:16','2020-02-13 08:40:06'),(4,'Ali Mohtashim','ali_mohtashim','ali.mohtashim@cubixlabs.com','$2y$10$wby/lfBV8egd6QwOLqenkOCeQ7JTC.rQsFBdkN.eI0hQaEL63.YsO','CubixLabs','ABC Industry','DHA','75500','Karachi','Pakistan','Cash Only','2020-02-07',2,'5784',0,'12345678902',NULL,'en',0,'2020-02-11 08:43:24','2020-02-14 04:25:50');
/*!40000 ALTER TABLE `app_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bank_reconciles`
--

DROP TABLE IF EXISTS `bank_reconciles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bank_reconciles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `date_from` date DEFAULT NULL,
  `date_to` date DEFAULT NULL,
  `opening_balance` varchar(45) DEFAULT NULL,
  `actual_balance` varchar(45) DEFAULT NULL,
  `cash_in` varchar(45) DEFAULT NULL,
  `cash_out` varchar(45) DEFAULT NULL,
  `ending_balance` varchar(45) DEFAULT NULL,
  `variance` varchar(45) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bank_reconciles`
--

LOCK TABLES `bank_reconciles` WRITE;
/*!40000 ALTER TABLE `bank_reconciles` DISABLE KEYS */;
INSERT INTO `bank_reconciles` VALUES (2,1,'2020-02-12','2020-02-16','120','120','120','110','10','0.2','2020-02-13 05:00:28','2020-02-13 04:38:53');
/*!40000 ALTER TABLE `bank_reconciles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  `type_id` int(11) DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (2,'Expense','sdadadsa',7,100,7,1,'2020-02-10 12:03:47','2020-02-11 09:41:29'),(3,'Expense','sdadadsa',7,100,7,1,'2020-02-11 05:15:13','2020-02-12 05:49:50'),(4,'Expense Title','dsadssadsdasd',NULL,100,11,1,'2020-02-13 04:07:48','2020-02-13 04:07:48'),(5,'Title Expense','sdasda',NULL,500,11,1,'2020-02-13 04:07:48','2020-02-13 04:07:48');
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventories`
--

DROP TABLE IF EXISTS `inventories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` double(8,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventories`
--

LOCK TABLES `inventories` WRITE;
/*!40000 ALTER TABLE `inventories` DISABLE KEYS */;
INSERT INTO `inventories` VALUES (1,'Inventory','1dsaasd',100.00,2,1,5,'2020-02-10 10:52:33','2020-02-10 10:57:09'),(2,'asdasdas','sadasdasdasdasdasdasdsadasdasdasd',23.00,1,1,5,'2020-02-11 06:53:50','2020-02-11 06:53:50'),(3,'Ineventory','1dsaasd',100.00,2,1,12,'2020-02-11 07:27:58','2020-02-11 10:01:30'),(4,'asdasdas','123456789',23.00,1,1,5,'2020-02-11 07:32:14','2020-02-11 07:32:14'),(6,'Title 2','sdasda',121.00,2,1,5,'2020-02-13 03:33:45','2020-02-13 03:33:45');
/*!40000 ALTER TABLE `inventories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2020_01_17_155910_modules',1),(5,'2020_01_17_161651_statuses',1),(6,'2020_01_17_162036_types',1),(7,'2020_01_17_162425_widgets',1),(8,'2020_01_17_162732_widgets_roles',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `permissions_enabled` int(11) DEFAULT NULL,
  `permissions_table` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'dashboard','Dashboard','home','fa fa-columns',0,NULL,NULL,1,NULL,NULL),(3,'users','User Management','app_users.show','fa fa-users',0,NULL,NULL,2,NULL,NULL);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `owner_accounts`
--

DROP TABLE IF EXISTS `owner_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `owner_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `owner_accounts`
--

LOCK TABLES `owner_accounts` WRITE;
/*!40000 ALTER TABLE `owner_accounts` DISABLE KEYS */;
INSERT INTO `owner_accounts` VALUES (3,4,200,'2020-02-11',1,'2020-02-12 07:12:05','2020-02-12 07:12:28'),(4,4,200,'2020-02-11',1,'2020-02-12 07:18:44','2020-02-12 07:19:08'),(5,NULL,100,'2020-11-12',1,'2020-02-13 04:21:53','2020-02-13 04:21:53'),(6,NULL,500,'2020-11-12',1,'2020-02-13 04:21:53','2020-02-13 04:21:53');
/*!40000 ALTER TABLE `owner_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pendings`
--

DROP TABLE IF EXISTS `pendings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pendings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `draftable_id` int(11) DEFAULT NULL,
  `draftable_type` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pendings`
--

LOCK TABLES `pendings` WRITE;
/*!40000 ALTER TABLE `pendings` DISABLE KEYS */;
INSERT INTO `pendings` VALUES (1,1,2,'App\\Models\\Sale','2020-02-12 06:13:01','2020-02-12 06:13:01'),(3,1,3,'App\\Models\\Expense','2020-02-12 07:11:19','2020-02-12 07:11:19'),(8,1,3,'App\\Models\\OwnerAccount','2020-02-12 07:19:50','2020-02-12 07:19:50');
/*!40000 ALTER TABLE `pendings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) DEFAULT NULL,
  `description` text,
  `quantity` int(11) DEFAULT '1',
  `amount` double DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (3,'Purchase','adasdadsdsa',1,1221,8,1,'2020-02-11 09:44:19','2020-02-11 09:44:19'),(4,'Purchase item','dsadssadsdasd',1,100,8,1,'2020-02-13 04:11:42','2020-02-13 04:11:42'),(5,'sdds','sdasda',2,500,8,1,'2020-02-13 04:11:42','2020-02-13 04:11:42');
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `description` text,
  `amount` double DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (2,'2020-02-11','sdsdsds',200,6,1,'2020-02-11 09:38:24','2020-02-12 05:52:59'),(3,'2020-11-11','dsadssadsdasd',100,10,1,'2020-02-13 03:40:17','2020-02-13 03:40:17'),(4,'2020-12-12','sdasda',500,10,1,'2020-02-13 03:40:17','2020-02-13 03:40:17');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statuses`
--

DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statuses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statuses`
--

LOCK TABLES `statuses` WRITE;
/*!40000 ALTER TABLE `statuses` DISABLE KEYS */;
INSERT INTO `statuses` VALUES (1,'Active','active','app_users'),(2,'Block','block','app_users'),(4,'Pending','pending','accounts'),(5,'In Stock','in_stock','inventories'),(6,'Received','received','sales'),(7,'Paid','Paid','expenses'),(8,'Un Paid','un_paid','purchases'),(9,'Paid','paid','purchases'),(10,'Not Received','not_received','sales'),(11,'Un Paid','un_paid','expenses'),(12,'Out of Stock','out_of_stock','inventories');
/*!40000 ALTER TABLE `statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscription` varchar(45) DEFAULT NULL,
  `slug` varchar(45) DEFAULT NULL,
  `expire_in` varchar(45) DEFAULT NULL,
  `amount` float DEFAULT NULL,
  `update_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,'Basic','basic','3 months',0,'2020-02-11 13:34:37','2020-02-11 13:34:44'),(2,'Expert','expert','1 month',33,'2020-02-11 13:34:40','2020-02-11 13:34:46'),(3,'Pro','pro','1 month',330,'2020-02-11 13:34:42','2020-02-11 13:34:48');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `types`
--

DROP TABLE IF EXISTS `types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `types`
--

LOCK TABLES `types` WRITE;
/*!40000 ALTER TABLE `types` DISABLE KEYS */;
INSERT INTO `types` VALUES (1,'Sales','sales','accounts'),(2,'Expense','expense','accounts'),(3,'Cash In','cash_in','owner_accounts'),(4,'Cash Out','cash_out','owner_accounts'),(6,'Cost of Goods','cost_of_goods','expenses'),(7,'Fixed Cost','fixed_cost','expenses'),(8,'Variable Cost','variable_cost','expenses');
/*!40000 ALTER TABLE `types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uploads`
--

DROP TABLE IF EXISTS `uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uploads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `model_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_ref_id` bigint(20) DEFAULT NULL,
  `ref_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filesize` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uploads`
--

LOCK TABLES `uploads` WRITE;
/*!40000 ALTER TABLE `uploads` DISABLE KEYS */;
INSERT INTO `uploads` VALUES (1,'product',1,NULL,'/files/1581089422abc.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(2,'product',1,NULL,'/files/1581089422gravatar.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(3,'product',1,NULL,'/files/1581089422gravatar.jpg',NULL,NULL,NULL,NULL,NULL,NULL),(4,'product',2,NULL,'/files/1581089606abc.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(5,'product',2,NULL,'/files/1581089606gravatar.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(6,'product',2,NULL,'/files/1581089606gravatar.jpg',NULL,NULL,NULL,NULL,NULL,NULL),(7,'product',3,NULL,'/files/1581089736abc.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(8,'product',3,NULL,'/files/1581089736gravatar.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(9,'product',3,NULL,'/files/1581089736gravatar.jpg',NULL,NULL,NULL,NULL,NULL,NULL),(10,'product',4,NULL,'/files/1581090052abc.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(11,'product',4,NULL,'/files/1581090052gravatar.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(12,'product',4,NULL,'/files/1581090052gravatar.jpg',NULL,NULL,NULL,NULL,NULL,NULL),(13,'product',1,NULL,'/files/1581327082abc.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(14,'product',1,NULL,'/files/1581327082gravatar.jpeg',NULL,NULL,NULL,NULL,NULL,NULL),(15,'product',1,NULL,'/files/1581327082gravatar.jpg',NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_subscriptions`
--

DROP TABLE IF EXISTS `user_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_subscriptions`
--

LOCK TABLES `user_subscriptions` WRITE;
/*!40000 ALTER TABLE `user_subscriptions` DISABLE KEYS */;
INSERT INTO `user_subscriptions` VALUES (1,1,2,'2020-03-11 04:29:30','2020-02-11 11:29:30','2020-02-11 11:28:32');
/*!40000 ALTER TABLE `user_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@sigil.com',NULL,'$2y$10$eMgUi1v3s.a5xXJDYyv7muDG505.xSwFRL/kDL/u0xpcJVo7o9M5q',NULL,NULL,NULL,NULL,'2020-02-13 06:44:28','2020-02-13 06:44:28');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `widgets`
--

DROP TABLE IF EXISTS `widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widgets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `query` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sorting` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `column` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `widgets`
--

LOCK TABLES `widgets` WRITE;
/*!40000 ALTER TABLE `widgets` DISABLE KEYS */;
INSERT INTO `widgets` VALUES (1,'Active Users','fa fa-users','select count(*) as value from `app_users` where `status_id` = 1','active','1',NULL,NULL,NULL,'counter','col-4',NULL,NULL),(2,'Basic package Subscriptions',NULL,'SELECT COUNT(*) AS value FROM `user_subscriptions` WHERE EXISTS( SELECT * FROM `subscriptions` WHERE `user_subscriptions`.`subscription_id` = `subscriptions`.`id` AND `slug` = \'basic\')','active','2',NULL,NULL,NULL,'counter','col-4',NULL,NULL),(3,'Upgraded package Subscriptions',NULL,'SELECT COUNT(*) AS value FROM `user_subscriptions` WHERE EXISTS( SELECT * FROM `subscriptions` WHERE `user_subscriptions`.`subscription_id` = `subscriptions`.`id` AND `slug` != \'basic\')','active','3',NULL,NULL,NULL,'counter','col-4',NULL,NULL);
/*!40000 ALTER TABLE `widgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `widgets_roles`
--

DROP TABLE IF EXISTS `widgets_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `widgets_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `widget_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `widgets_roles`
--

LOCK TABLES `widgets_roles` WRITE;
/*!40000 ALTER TABLE `widgets_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `widgets_roles` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-02-14 14:34:47
