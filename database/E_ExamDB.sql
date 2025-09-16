-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: localhost    Database: e_exams
-- ------------------------------------------------------
-- Server version	9.1.0

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
-- Table structure for table `exam_answers`
--

DROP TABLE IF EXISTS `exam_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_answers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `attempt_id` int NOT NULL,
  `question_id` int NOT NULL,
  `option_id` int DEFAULT NULL,
  `answer_text` text,
  `is_correct` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `question_id` (`question_id`),
  KEY `option_id` (`option_id`),
  CONSTRAINT `exam_answers_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts` (`id`),
  CONSTRAINT `exam_answers_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`),
  CONSTRAINT `exam_answers_ibfk_3` FOREIGN KEY (`option_id`) REFERENCES `question_options` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_answers`
--

LOCK TABLES `exam_answers` WRITE;
/*!40000 ALTER TABLE `exam_answers` DISABLE KEYS */;
INSERT INTO `exam_answers` VALUES (7,1,1,6,NULL,1),(8,1,2,10,NULL,1),(9,2,1,5,NULL,0),(10,2,2,10,NULL,1),(11,3,3,NULL,'The cat is sleeping.',1),(12,3,4,15,NULL,1),(13,4,17,36,NULL,0),(14,4,18,43,NULL,0),(15,5,17,38,NULL,1),(16,5,18,45,NULL,1);
/*!40000 ALTER TABLE `exam_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_attempts`
--

DROP TABLE IF EXISTS `exam_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exam_id` int NOT NULL,
  `user_id` int NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `status` enum('in_progress','submitted','graded') DEFAULT 'in_progress',
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `exam_attempts_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`),
  CONSTRAINT `exam_attempts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_attempts`
--

LOCK TABLES `exam_attempts` WRITE;
/*!40000 ALTER TABLE `exam_attempts` DISABLE KEYS */;
INSERT INTO `exam_attempts` VALUES (1,1,3,'2025-09-01 08:05:00','2025-09-01 08:35:00',10.00,'submitted'),(2,1,2,'2025-09-01 08:10:00','2025-09-01 08:40:00',8.00,'submitted'),(3,2,1,'2025-09-02 14:05:00','2025-09-02 14:50:00',NULL,'graded'),(4,8,3,'2025-09-07 09:01:00','2025-09-07 09:05:00',0.00,'submitted'),(5,8,1,'2025-09-09 09:17:04','2025-09-09 09:17:14',10.00,'submitted');
/*!40000 ALTER TABLE `exam_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_questions`
--

DROP TABLE IF EXISTS `exam_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exam_questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `exam_id` int NOT NULL,
  `question_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `exam_questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`),
  CONSTRAINT `exam_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_questions`
--

LOCK TABLES `exam_questions` WRITE;
/*!40000 ALTER TABLE `exam_questions` DISABLE KEYS */;
INSERT INTO `exam_questions` VALUES (3,1,1),(4,1,2),(5,2,3),(6,2,4),(7,3,5),(8,3,6),(9,7,1),(10,7,2),(11,7,17),(16,8,18),(17,8,17);
/*!40000 ALTER TABLE `exam_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject_id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `duration_minutes` int NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  `type` varchar(45) NOT NULL,
  `maxScore` int NOT NULL,
  `passingScore` int NOT NULL,
  `totalQuestions` int NOT NULL,
  `difficulty` varchar(45) NOT NULL,
  `questionSource` enum('bank','manual') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `exams_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exams`
--

LOCK TABLES `exams` WRITE;
/*!40000 ALTER TABLE `exams` DISABLE KEYS */;
INSERT INTO `exams` VALUES (1,1,'Bài kiểm tra Toán số 1','Kiểm tra kiến thức Toán cơ bản','2025-09-01 08:00:00','2025-09-01 10:00:00',30,2,'2025-08-28 14:17:28',0,'active','multiple_choice',10,5,2,'easy','bank'),(2,2,'Bài kiểm tra Tiếng Anh số 1','Kiểm tra ngữ pháp cơ bản','2025-09-02 14:00:00','2025-09-02 16:00:00',45,1,'2025-08-28 14:17:28',0,'active','multiple_choice',10,5,2,'medium','bank'),(3,3,'Bài kiểm tra Tin học số 1','Kiểm tra kiến thức cơ bản về máy tính','2025-09-03 09:00:00','2025-09-03 11:00:00',40,2,'2025-08-28 14:17:28',0,'active','multiple_choice',10,5,2,'hard','bank'),(7,1,'Bài kiểm tra Toán số 1','Kiểm tra kiến thức toán cơ bản ','2025-09-04 16:18:00','2025-09-02 16:18:00',60,1,'2025-09-01 10:40:30',0,'active','multiple_choice',10,5,3,'easy','bank'),(8,1,'Kiểm tra lần 3','Bài kiểm tra toán lần 3','2025-09-05 09:01:00','2025-09-13 09:01:00',60,1,'2025-09-02 02:02:01',0,'active','multiple_choice',10,7,2,'easy','bank');
/*!40000 ALTER TABLE `exams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_options`
--

DROP TABLE IF EXISTS `question_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` text NOT NULL,
  `is_correct` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_options`
--

LOCK TABLES `question_options` WRITE;
/*!40000 ALTER TABLE `question_options` DISABLE KEYS */;
INSERT INTO `question_options` VALUES (5,1,'3',0),(6,1,'4',1),(7,1,'5',0),(8,1,'6',0),(9,2,'2',0),(10,2,'4',1),(11,2,'8',0),(12,2,'16',0),(13,4,'Beautiful',0),(14,4,'Run',1),(15,4,'Book',0),(16,4,'Quick',0),(17,5,'Apple',0),(18,5,'Microsoft',1),(19,5,'Google',0),(20,5,'IBM',0),(21,6,'HyperText Markup Language',1),(22,6,'HighText Machine Language',0),(23,6,'Hyper Tool Multi Language',0),(24,6,'Home Tool Markup Language',0),(25,13,'0',1),(26,14,'0',1),(27,15,'0',1),(28,15,'1',0),(29,15,'2',0),(30,15,'3',0),(31,16,'0',1),(32,16,'1',0),(33,16,'2',0),(34,16,'3',0),(35,17,'0',0),(36,17,'1',0),(37,17,'2',0),(38,17,'3',1),(43,18,'10',0),(44,18,'20',0),(45,18,'30',1),(46,18,'40',0);
/*!40000 ALTER TABLE `question_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject_id` int NOT NULL,
  `content` text NOT NULL,
  `type` enum('multiple_choice','true_false','fill_blank','essay') NOT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,1,'5 - 3','multiple_choice','easy',1,'2025-08-28 14:14:58',0,'active'),(2,1,'Căn bậc hai của 16 là?','multiple_choice','easy',2,'2025-08-28 14:14:58',0,'active'),(3,2,'Dịch sang tiếng Anh: \"Con mèo đang ngủ.\"','essay','medium',2,'2025-08-28 14:14:58',0,'active'),(4,2,'Từ nào là động từ?','multiple_choice','medium',1,'2025-08-28 14:14:58',0,'active'),(5,3,'Hệ điều hành Windows do công ty nào phát triển?','multiple_choice','easy',1,'2025-08-28 14:14:58',0,'active'),(6,3,'HTML là viết tắt của cụm từ gì?','multiple_choice','medium',1,'2025-08-28 14:14:58',0,'active'),(13,1,'10 - 10','multiple_choice','easy',1,'2025-08-30 14:27:06',1,'active'),(14,1,'10 - 10','multiple_choice','easy',1,'2025-08-30 14:27:07',1,'active'),(15,1,'10 - 10','multiple_choice','easy',1,'2025-08-30 14:27:46',1,'active'),(16,1,'10 - 10','multiple_choice','easy',1,'2025-08-30 14:27:46',1,'active'),(17,1,'5 - 2','multiple_choice','easy',1,'2025-08-30 14:28:49',0,'active'),(18,1,'50 - 20','multiple_choice','easy',1,'2025-08-31 01:20:49',0,'active');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `code` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role`
--

LOCK TABLES `role` WRITE;
/*!40000 ALTER TABLE `role` DISABLE KEYS */;
INSERT INTO `role` VALUES (1,'Quản trị viên',0,'admin'),(2,'Giáo viên',0,'teacher'),(3,'Học sinh',0,'student');
/*!40000 ALTER TABLE `role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `created_by` int NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,'Toán học','MATH101','Môn Toán cơ bản',0,1,'active','2025-09-02 21:55:23'),(2,'Tiếng Anh','ENG101','Tiếng Anh căn bản',0,1,'active','2025-09-02 21:55:23'),(3,'Tin học','IT101','Tin học đại cương',0,1,'active','2025-09-02 21:55:23'),(4,'Vật Lý 11','VL11','Vật Lý lớp 11',1,1,'active','2025-09-03 08:58:22');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(1) DEFAULT '0',
  `role_id` int NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin01','123456','Quản trị viên','admin@example.com','2025-08-28 14:12:55',0,1,'active'),(2,'teacher01','123456','Nguyễn Văn A','teacher01@example.com','2025-08-28 14:12:55',0,1,'active'),(3,'levana','123','Lê Văn A ','levana@gmail.com','2025-08-28 14:19:30',0,2,'active'),(4,'levanc','14e1b600b1fd579f47433b88e8d85291','Le Van C','levanc@gmail.com','2025-09-03 14:33:03',0,3,'active'),(5,'hoangvana','e807f1fcf82d132f9bb018ca6738a19f','Hoang Van A','hoangvana@gmail.com','2025-09-03 14:51:51',0,1,'active'),(6,'hiepld','eedebef5001e55f9d3b9aefaacbc077f','La Đức Hiệp','laduchiep2003@gmail.com','2025-09-04 10:41:14',0,1,'active');
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

-- Dump completed on 2025-09-16 23:24:52
