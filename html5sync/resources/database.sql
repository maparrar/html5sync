CREATE DATABASE  IF NOT EXISTS `musical` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `musical`;
-- MySQL dump 10.13  Distrib 5.5.35, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: musical
-- ------------------------------------------------------
-- Server version	5.5.35-0ubuntu0.13.10.2

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
-- Table structure for table `Album`
--

DROP TABLE IF EXISTS `Album`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `artist` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Album_Artist_idx` (`artist`),
  CONSTRAINT `fk_Album_Artist` FOREIGN KEY (`artist`) REFERENCES `Artist` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Album`
--

LOCK TABLES `Album` WRITE;
/*!40000 ALTER TABLE `Album` DISABLE KEYS */;
INSERT INTO `Album` VALUES (1,'Pulse',1),(2,'Come taste the band',2),(3,'Blind Melon',3),(4,'L.A. Woman',4),(5,'Born Again',5);
/*!40000 ALTER TABLE `Album` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Artist`
--

DROP TABLE IF EXISTS `Artist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Artist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Artist`
--

LOCK TABLES `Artist` WRITE;
/*!40000 ALTER TABLE `Artist` DISABLE KEYS */;
INSERT INTO `Artist` VALUES (1,'Pink Floyd'),(2,'Deep Purple'),(3,'Blind Melon'),(4,'The Doors'),(5,'Black Sabbath');
/*!40000 ALTER TABLE `Artist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `Song`
--

DROP TABLE IF EXISTS `Song`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Song` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `album` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Song_Album1_idx` (`album`),
  CONSTRAINT `fk_Song_Album1` FOREIGN KEY (`album`) REFERENCES `Album` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Song`
--

LOCK TABLES `Song` WRITE;
/*!40000 ALTER TABLE `Song` DISABLE KEYS */;
INSERT INTO `Song` VALUES (1,'Shine on you crazy diamond',1),(5,'You keep on moving',2),(6,'No rain',3),(7,'Riders on the storm',4),(8,'Born again',5);
/*!40000 ALTER TABLE `Song` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-03-03 18:18:36
