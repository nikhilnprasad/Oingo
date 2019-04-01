CREATE DATABASE  IF NOT EXISTS `oingo` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `oingo`;
-- MySQL dump 10.13  Distrib 5.7.20, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: oingo
-- ------------------------------------------------------
-- Server version	5.7.20-log

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
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `nid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `replyToCid` int(11) DEFAULT NULL,
  `cText` tinytext NOT NULL,
  `ctimestamp` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  PRIMARY KEY (`cid`),
  KEY `nidComment_idx` (`nid`),
  KEY `uidComment_idx` (`uid`),
  CONSTRAINT `nidComment` FOREIGN KEY (`nid`) REFERENCES `note` (`nid`),
  CONSTRAINT `uidComment` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,1,2,NULL,'Haha yeah it is! I did my project there too!','2018-10-12 15:27:23.057'),(2,1,1,1,'I can see why. It\'s a quiet place with the books that I need!','2018-10-12 16:03:22.931'),(3,4,3,NULL,'Nice! I\'ll definitely visit this place!','2018-07-10 20:16:41.438');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filter`
--

DROP TABLE IF EXISTS `filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filter` (
  `uid` int(11) NOT NULL,
  `stateid` int(11) NOT NULL,
  `tagID` int(11) NOT NULL,
  `fstarttimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fendtimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rid` int(11) NOT NULL,
  `flatitude` decimal(10,8) NOT NULL,
  `flongitude` decimal(11,8) NOT NULL,
  `fradius` decimal(10,6) NOT NULL,
  `vid` int(11) NOT NULL,
  PRIMARY KEY (`uid`,`stateid`,`tagID`,`fstarttimestamp`,`fendtimestamp`,`flatitude`,`flongitude`,`fradius`,`vid`,`rid`),
  KEY `tagIDFilter_idx` (`tagID`),
  KEY `vidFilter_idx` (`vid`),
  KEY `ridFilter_idx` (`rid`),
  CONSTRAINT `ridFilter` FOREIGN KEY (`rid`) REFERENCES `repeatnote` (`rid`),
  CONSTRAINT `tagIDFilter` FOREIGN KEY (`tagID`) REFERENCES `tag` (`tagid`),
  CONSTRAINT `uidFilter, stateIDFilter` FOREIGN KEY (`uid`, `stateid`) REFERENCES `userstate` (`uid`, `stateid`),
  CONSTRAINT `vidFilter` FOREIGN KEY (`vid`) REFERENCES `visibility` (`vid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filter`
--

LOCK TABLES `filter` WRITE;
/*!40000 ALTER TABLE `filter` DISABLE KEYS */;
INSERT INTO `filter` VALUES (1,1,-1,'0000-00-00 00:00:00','0000-00-00 00:00:00',0,40.78000000,-73.69600000,5.000000,2),(2,0,-1,'0000-00-00 00:00:00','0000-00-00 00:00:00',0,-99.00000000,-999.00000000,0.000000,1),(3,0,-1,'0000-00-00 00:00:00','0000-00-00 00:00:00',0,40.72900000,-73.99600000,0.200000,2),(3,2,-1,'2018-11-30 00:00:00','2018-11-30 03:00:00',1,40.72300000,-73.99900000,0.500000,2),(2,2,7,'2018-11-29 23:35:08','2018-11-30 03:00:00',1,-99.00000000,-999.00000000,0.000000,2),(2,2,9,'2018-11-30 02:00:00','2018-11-30 04:00:00',1,-99.00000000,-999.00000000,0.000000,2),(1,0,12,'2018-10-12 16:00:00','2018-10-12 20:00:00',1,40.78400000,-73.96500000,0.500000,2);
/*!40000 ALTER TABLE `filter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friendship`
--

DROP TABLE IF EXISTS `friendship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friendship` (
  `inviteID` int(11) NOT NULL AUTO_INCREMENT,
  `uid1` int(11) NOT NULL,
  `uid2` int(11) NOT NULL,
  `isAccepted` tinyint(4) NOT NULL,
  PRIMARY KEY (`inviteID`),
  KEY `uid1_idx` (`uid1`),
  KEY `uid2_idx` (`uid2`),
  CONSTRAINT `uid1` FOREIGN KEY (`uid1`) REFERENCES `user` (`uid`),
  CONSTRAINT `uid2` FOREIGN KEY (`uid2`) REFERENCES `user` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friendship`
--

LOCK TABLES `friendship` WRITE;
/*!40000 ALTER TABLE `friendship` DISABLE KEYS */;
INSERT INTO `friendship` VALUES (1,1,2,1);
/*!40000 ALTER TABLE `friendship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `note`
--

DROP TABLE IF EXISTS `note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note` (
  `nID` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `note` text NOT NULL,
  `ntimestamp` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `nlatitude` decimal(10,8) NOT NULL,
  `nlongitude` decimal(11,8) NOT NULL,
  `nradius` int(11) NOT NULL,
  `nstarttimestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nendtimestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rid` int(11) NOT NULL,
  `vid` int(11) NOT NULL,
  `allowcomments` tinyint(4) NOT NULL,
  PRIMARY KEY (`nID`),
  KEY `uid_idx` (`uid`),
  KEY `rid_idx` (`rid`),
  KEY `vid_idx` (`vid`),
  FULLTEXT KEY `note` (`note`),
  CONSTRAINT `rid` FOREIGN KEY (`rid`) REFERENCES `repeatnote` (`rid`),
  CONSTRAINT `uid` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`),
  CONSTRAINT `vid` FOREIGN KEY (`vid`) REFERENCES `visibility` (`vid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `note`
--

LOCK TABLES `note` WRITE;
/*!40000 ALTER TABLE `note` DISABLE KEYS */;
INSERT INTO `note` VALUES (1,1,'The library is a calm place to finish DB project.','2018-10-12 10:06:34.063',40.72951100,-73.99646000,300,'2018-10-13 00:00:00','2018-10-13 03:00:00',1,1,0),(2,2,'The food here is amazing!','2018-01-05 16:07:42.531',40.70239300,-73.98733000,300,'2018-01-06 08:30:00','2018-01-06 20:30:00',1,2,1),(3,3,'This bank has horrible customer service','2018-07-10 10:23:36.694',40.60309400,-73.99386900,300,'2018-07-10 11:39:41','2018-07-10 18:30:00',2,2,1),(4,2,'This restaurant is so posh!','2018-11-29 23:22:02.831',40.72394400,-73.99969400,300,'2018-11-30 01:45:00','2018-11-30 04:30:00',1,2,1),(5,3,'The park is so beautiful at this time of the year!','2018-10-10 09:56:42.751',40.78461900,-73.96531500,300,'2018-10-14 15:00:00','2018-10-14 21:00:00',5,2,1),(6,1,'The water is so calm this evening. It\'s the best!','2018-11-30 00:31:44.643',40.72950000,-73.99640000,3000,'2018-11-30 00:32:45','2018-11-30 04:00:00',1,0,1),(7,3,'This street has the best places to eat!','2018-11-29 20:12:34.845',40.72951000,-73.99643000,3000,'2018-11-29 21:00:00','2018-11-30 04:55:00',1,2,1);
/*!40000 ALTER TABLE `note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `repeatnote`
--

DROP TABLE IF EXISTS `repeatnote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `repeatnote` (
  `rid` int(11) NOT NULL,
  `rdesc` varchar(45) NOT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `repeatnote`
--

LOCK TABLES `repeatnote` WRITE;
/*!40000 ALTER TABLE `repeatnote` DISABLE KEYS */;
INSERT INTO `repeatnote` VALUES (0,'None'),(1,'Daily'),(2,'Weekly'),(3,'Biweekly'),(4,'Monthly'),(5,'Yearly');
/*!40000 ALTER TABLE `repeatnote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `state`
--

DROP TABLE IF EXISTS `state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state` (
  `stateID` int(11) NOT NULL,
  `statename` varchar(45) NOT NULL,
  PRIMARY KEY (`stateID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `state`
--

LOCK TABLES `state` WRITE;
/*!40000 ALTER TABLE `state` DISABLE KEYS */;
INSERT INTO `state` VALUES (0,'Default'),(1,'focused'),(2,'chilling'),(3,'at work');
/*!40000 ALTER TABLE `state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tag`
--

DROP TABLE IF EXISTS `tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tag` (
  `tagid` int(11) NOT NULL AUTO_INCREMENT,
  `tagname` varchar(45) NOT NULL,
  PRIMARY KEY (`tagid`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tag`
--

LOCK TABLES `tag` WRITE;
/*!40000 ALTER TABLE `tag` DISABLE KEYS */;
INSERT INTO `tag` VALUES (-1,'no tag'),(1,'#studying'),(2,'#gradschool'),(3,'#nyu'),(4,'#library'),(5,'#bar'),(6,'#pub'),(7,'#goodfood'),(8,'#food'),(9,'#ambiance'),(10,'#park'),(11,'#scenery'),(12,'#snow'),(13,'#beautiful'),(14,'#bank'),(15,'#customerservice'),(16,'#restaurant'),(17,'#posh');
/*!40000 ALTER TABLE `tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tagnotes`
--

DROP TABLE IF EXISTS `tagnotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tagnotes` (
  `nid` int(11) NOT NULL,
  `tagid` int(11) NOT NULL,
  PRIMARY KEY (`nid`,`tagid`),
  KEY `tagid_idx` (`tagid`),
  CONSTRAINT `nid` FOREIGN KEY (`nid`) REFERENCES `note` (`nid`),
  CONSTRAINT `tagid` FOREIGN KEY (`tagid`) REFERENCES `tag` (`tagid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tagnotes`
--

LOCK TABLES `tagnotes` WRITE;
/*!40000 ALTER TABLE `tagnotes` DISABLE KEYS */;
INSERT INTO `tagnotes` VALUES (1,1),(1,2),(1,3),(1,4),(2,5),(2,6),(2,7),(4,7),(2,8),(4,8),(7,8),(4,9),(6,9),(5,10),(6,10),(5,11),(6,11),(5,12),(4,13),(5,13),(3,14),(3,15),(4,16),(4,17);
/*!40000 ALTER TABLE `tagnotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(45) NOT NULL,
  `uemail` varchar(45) NOT NULL,
  `utimestamp` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `upassword` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'Adam','abc@abc.com','2018-11-30 02:16:53.630','$2y$10$HBqDoq4poTTD8bf2MDTtk.JpAOG8M9SLsUlGK0a6HwtlguHMJQo5u'),(2,'Derek','def@def.com','2018-11-30 02:16:53.630','$2y$10$hGq8bk6MXaLYwil.SGbU/OI.GfVBPd.KfhuNC6tZVpYsBw5jmwFky'),(3,'Guan','ghi@ghi.com','2018-11-30 02:16:53.630','$2y$10$1/UjFOxbQoJ/uiZu9k00ruyq7VTCFZQGcw3Y012Z5XnepyM6V52qm');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 trigger after_user_register
	after insert on user
    for each row begin
    insert into userstate (uid, stateID, isCurrent) VALUES (NEW.uid, 0, 1);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `userlocation`
--

DROP TABLE IF EXISTS `userlocation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userlocation` (
  `uid` int(11) NOT NULL,
  `ulatitude` decimal(10,8) NOT NULL,
  `ulongitude` decimal(11,8) NOT NULL,
  `utimestamp` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
  `activity` varchar(45) NOT NULL,
  PRIMARY KEY (`uid`,`ulatitude`,`ulongitude`,`utimestamp`),
  CONSTRAINT `uidLocation` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userlocation`
--

LOCK TABLES `userlocation` WRITE;
/*!40000 ALTER TABLE `userlocation` DISABLE KEYS */;
INSERT INTO `userlocation` VALUES (1,40.62437430,-74.02458390,'2018-12-17 11:45:04.021','user logged in'),(1,40.62439350,-74.02459410,'2018-12-17 11:40:18.580','user logged in'),(1,40.65937600,-74.00460200,'2018-10-11 13:59:47.131','became friends with uid 2'),(1,40.72950000,-73.99640000,'2018-11-30 00:31:44.643','nid 6 created'),(1,40.72951100,-73.99646000,'2018-10-11 08:21:26.508','friend request sent to uid 2'),(1,40.72951100,-73.99646000,'2018-10-12 11:06:34.063','nid 1 created'),(1,40.72953000,-73.99654000,'2018-11-30 02:30:00.000','searched for notes'),(1,40.74881700,-73.98542800,'2018-10-12 12:03:22.931','cid 2 created'),(1,40.76204400,-73.97609400,'2018-10-09 22:40:10.919','uid 1 created'),(2,40.62433890,-74.02458680,'2018-12-17 11:42:13.538','user logged in'),(2,40.70239300,-73.98733000,'2018-01-05 16:07:42.531','nid 2 created'),(2,40.71297400,-74.01339700,'2018-01-02 08:05:03.767','uid 2 created'),(2,40.72390000,-73.99960000,'2018-11-30 02:45:30.290','searched for notes'),(2,40.72394400,-73.99969400,'2018-07-10 09:39:42.663','nid 4 created'),(2,40.75874000,-73.97867400,'2018-10-12 11:27:23.057','cid 1 created'),(3,40.60309400,-73.99386900,'2018-07-10 10:23:36.694','nid 3 created'),(3,40.62436660,-74.02458320,'2018-12-17 11:42:24.717','user logged in'),(3,40.62437480,-74.02460470,'2018-12-17 11:49:52.573','user logged in'),(3,40.66553500,-73.96974900,'2018-07-10 16:16:41.438','cid 3 created'),(3,40.68940800,-74.04446800,'2018-07-06 17:36:53.213','uid 3 created'),(3,40.72951000,-73.99643000,'2018-11-29 20:12:34.845','nid 7 created'),(3,40.78461900,-73.96531500,'2018-10-10 09:56:42.751','nid 5 created');
/*!40000 ALTER TABLE `userlocation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userstate`
--

DROP TABLE IF EXISTS `userstate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userstate` (
  `uid` int(11) NOT NULL,
  `stateID` int(11) NOT NULL,
  `isCurrent` tinyint(4) NOT NULL,
  PRIMARY KEY (`uid`,`stateID`),
  KEY `stateid_idx` (`stateID`),
  CONSTRAINT `stateid` FOREIGN KEY (`stateID`) REFERENCES `state` (`stateID`),
  CONSTRAINT `uidfromuser` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userstate`
--

LOCK TABLES `userstate` WRITE;
/*!40000 ALTER TABLE `userstate` DISABLE KEYS */;
INSERT INTO `userstate` VALUES (1,0,0),(1,1,1),(2,0,0),(2,2,1),(3,0,1),(3,2,0),(3,3,0);
/*!40000 ALTER TABLE `userstate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visibility`
--

DROP TABLE IF EXISTS `visibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visibility` (
  `vid` int(11) NOT NULL,
  `visibleRelation` varchar(45) NOT NULL,
  PRIMARY KEY (`vid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visibility`
--

LOCK TABLES `visibility` WRITE;
/*!40000 ALTER TABLE `visibility` DISABLE KEYS */;
INSERT INTO `visibility` VALUES (0,'Private'),(1,'Friends'),(2,'All');
/*!40000 ALTER TABLE `visibility` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'oingo'
--

--
-- Dumping routines for database 'oingo'
--
/*!50003 DROP FUNCTION IF EXISTS `haversine` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `haversine`(

        lat1 FLOAT, lon1 FLOAT,
        lat2 FLOAT, lon2 FLOAT
     ) RETURNS float
    NO SQL
    DETERMINISTIC
    COMMENT 'Returns the distance in degrees on the Earth between two known points of latitude and longitude. To get miles, multiply by 3961, and km by 6373'
BEGIN

    RETURN 6371000 * (ACOS(
              COS(RADIANS(lat1)) *
              COS(RADIANS(lat2)) *
              COS(RADIANS(lon2) - RADIANS(lon1)) +
              SIN(RADIANS(lat1)) * SIN(RADIANS(lat2))
            ));

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `intime` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `intime`(utime timestamp, starttime timestamp, endtime timestamp, rid int) RETURNS int(11)
BEGIN
	DECLARE RET INT DEFAULT 0;
    Select CASE
		WHEN (starttime != '' and endtime != '')
			THEN 
				CASE rid
					WHEN 0 
						Then
							CASE
								WHEN(utime >= starttime and utime <= endtime)
									Then 1
								ELSE 0
							END
					WHEN 1 
						Then 
							CASE
								WHEN((time(utime) Between time(starttime) and time(endtime)) and utime >= starttime)
									THEN 1
								ELSE 0
							END
					WHEN 2 
						Then 
							CASE
								WHEN((time(utime) Between time(starttime) and time(endtime)) and DAYOFWEEK(utime) = DAYOFWEEK(starttime))
									THEN 1
								ELSE 0
							END
					WHEN 3 
						Then
							CASE 
								WHEN((time(utime) Between time(starttime) and time(endtime)) and DAYOFWEEK(utime) = DAYOFWEEK(starttime) 
											and (((WEEK(utime) - WEEK(starttime)) % 2) = 0) and utime >= starttime)
									THEN 1
								ELSE 0
							END
					WHEN 4 
						THEN 
							CASE
								WHEN((time(utime) Between time(starttime) and time(endtime)) and DAY(utime) = DAY(starttime)
										and MONTH(utime) >= MONTH(starttime)) 
									THEN 1
								ELSE 0
							END                                        
					WHEN 5 
						Then 
							CASE
								WHEN((time(utime) Between time(starttime) and time(endtime))
										and MONTH(utime) = MONTH(starttime)	and DAY(utime) = DAY(starttime)
                                        and YEAR(utime) >= YEAR(starttime))
									Then 1
								ELSE 0
							END
				END
		WHEN (starttime != '' and endtime = '')
			THEN 
				CASE rid
					WHEN 0 
						Then
							CASE
								WHEN(utime = starttime)
									Then 1
								ELSE 0
							END
					WHEN 1 
						Then 
							CASE
								WHEN((time(utime) = time(starttime)) and utime >= starttime)
									THEN 1
								ELSE 0
							END
					WHEN 2 
						Then 
							CASE
								WHEN((time(utime) = time(starttime)) and DAYOFWEEK(utime) = DAYOFWEEK(starttime))
									THEN 1
								ELSE 0
							END
					WHEN 3 
						Then
							CASE 
								WHEN((time(utime) = time(starttime)) and DAYOFWEEK(utime) = DAYOFWEEK(starttime) 
											and (((WEEK(utime) - WEEK(starttime)) % 2) = 0) and utime >= starttime)
									THEN 1
								ELSE 0
							END
					WHEN 4 
						THEN 
							CASE
								WHEN((time(utime) = time(starttime)) and DAY(utime) = DAY(starttime)
										and MONTH(utime) >= MONTH(starttime)) 
									THEN 1
								ELSE 0
							END                                        
					WHEN 5 
						Then 
							CASE
								WHEN((time(utime) = time(starttime))
										and MONTH(utime) = MONTH(starttime)	and DAY(utime) = DAY(starttime)
                                        and YEAR(utime) >= YEAR(starttime))
									Then 1
								ELSE 0
							END
				END
		ELSE 0
	END as ifexists into ret;
    Return ret;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GETNOTES` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `GETNOTES`(IN inputuserid INT)
BEGIN
	select nid, note
from (select note.uid, nid, note, tagid, nlatitude, nlongitude, nradius, nstarttimestamp, nendtimestamp, rid, user.uid as visibletouser
		from note natural join tagnotes, user
		where CASE vid
	WHEN 0
    Then user.uid = note.uid
    WHEN 1
    THEN user.uid in (select uid2 as Friends
						from friendship
						where uid1 = note.uid
						union
						select uid1 as Friends
						from friendship
						where uid2 = note.uid
						union
						select uid from user
						where uid = user.uid)
	ELSE user.uid in (select uid from user)
    END) notetag, (select filter.uid, tagid, fstarttimestamp, fendtimestamp, flatitude, flongitude, ulatitude, ulongitude, uloc.utimestamp, rid, user.uid as visiblefromuser
					from filter, user, (select uid, ulatitude, ulongitude, utimestamp
					from userlocation u1
					where utimestamp = (select max(utimestamp)
													from userlocation u2
													where u1.uid = u2.uid)) uloc
					where stateid = (select stateid from userstate where uid = filter.uid and iscurrent = 1) and uloc.uid = filter.uid and 
					CASE vid
					WHEN 0
					Then user.uid = filter.uid
					WHEN 1
					Then user.uid in (select uid2 as Friends
								from friendship
								where uid1 = filter.uid
								union
								select uid1 as Friends
								from friendship
								where uid2 = filter.uid
								union
								select uid from user
								where uid = user.uid)
					ELSE user.uid in (select uid from user)
				END) filtertag
where filtertag.uid = inputuserid and CASE filtertag.tagid
		WHEN '-1' THEN notetag.tagid != ''
        ELSE notetag.tagid = filtertag.tagid
	END
and CASE 
		WHEN (filtertag.fstarttimestamp != '0000-00-00 00:00:00' and filtertag.fendtimestamp != '0000-00-00 00:00:00')
			THEN
				intime(filtertag.utimestamp, filtertag.fstarttimestamp, filtertag.fendtimestamp, filtertag.rid) = 1
		WHEN (filtertag.fstarttimestamp != '0000-00-00 00:00:00' and filtertag.fendtimestamp = '0000-00-00 00:00:00')
			THEN
				intime(filtertag.utimestamp, filtertag.fstarttimestamp, '', filtertag.rid) = 1
		ELSE filtertag.utimestamp != ''
	END
and intime(filtertag.utimestamp, notetag.nstarttimestamp, notetag.nendtimestamp, notetag.rid) = 1
and notetag.visibletouser = filtertag.uid and filtertag.visiblefromuser = notetag.uid
and haversine(filtertag.ulatitude, filtertag.ulongitude, notetag.nlatitude, notetag.nlongitude) <= notetag.nradius
and CASE 
		WHEN (filtertag.flatitude != -99.00000000 and filtertag.flongitude != -999.00000000)
        Then (haversine(filtertag.ulatitude, filtertag.ulongitude, filtertag.flatitude, filtertag.flongitude)/1000) <= notetag.nradius
        ELSE
        filtertag.ulatitude != ''
	END
group by nid, note;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GETNOTESKEYWORDS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `GETNOTESKEYWORDS`(IN inputuserid INT, IN keywords VARCHAR(45))
BEGIN
	select nid, note
from (select note.uid, nid, note, tagid, nlatitude, nlongitude, nradius, nstarttimestamp, nendtimestamp, rid, user.uid as visibletouser
		from note natural join tagnotes, user
		where CASE vid
	WHEN 0
    Then user.uid = note.uid
    WHEN 1
    THEN user.uid in (select uid2 as Friends
						from friendship
						where uid1 = note.uid
						union
						select uid1 as Friends
						from friendship
						where uid2 = note.uid
						union
						select uid from user
						where uid = user.uid)
	ELSE user.uid in (select uid from user)
    END) notetag, (select filter.uid, tagid, fstarttimestamp, fendtimestamp, flatitude, flongitude, ulatitude, ulongitude, uloc.utimestamp, rid, user.uid as visiblefromuser
					from filter, user, (select uid, ulatitude, ulongitude, utimestamp
					from userlocation u1
					where utimestamp = (select max(utimestamp)
													from userlocation u2
													where u1.uid = u2.uid)) uloc
					where stateid = (select stateid from userstate where uid = filter.uid and iscurrent = 1) and uloc.uid = filter.uid and 
					CASE vid
					WHEN 0
					Then user.uid = filter.uid
					WHEN 1
					Then user.uid in (select uid2 as Friends
								from friendship
								where uid1 = filter.uid
								union
								select uid1 as Friends
								from friendship
								where uid2 = filter.uid
								union
								select uid from user
								where uid = user.uid)
					ELSE user.uid in (select uid from user)
				END) filtertag
where filtertag.uid = inputuserid and CASE filtertag.tagid
		WHEN '-1' THEN notetag.tagid != ''
        ELSE notetag.tagid = filtertag.tagid
	END
and CASE 
		WHEN (filtertag.fstarttimestamp != '0000-00-00 00:00:00' and filtertag.fendtimestamp != '0000-00-00 00:00:00')
			THEN
				intime(filtertag.utimestamp, filtertag.fstarttimestamp, filtertag.fendtimestamp, filtertag.rid) = 1
		WHEN (filtertag.fstarttimestamp != '0000-00-00 00:00:00' and filtertag.fendtimestamp = '0000-00-00 00:00:00')
			THEN
				intime(filtertag.utimestamp, filtertag.fstarttimestamp, '', filtertag.rid) = 1
		ELSE filtertag.utimestamp != ''
	END
and intime(filtertag.utimestamp, notetag.nstarttimestamp, notetag.nendtimestamp, notetag.rid) = 1
and notetag.visibletouser = filtertag.uid and filtertag.visiblefromuser = notetag.uid
and haversine(filtertag.ulatitude, filtertag.ulongitude, notetag.nlatitude, notetag.nlongitude) <= notetag.nradius
and CASE 
		WHEN (filtertag.flatitude != -99.00000000 and filtertag.flongitude != -999.00000000)
        Then (haversine(filtertag.ulatitude, filtertag.ulongitude, filtertag.flatitude, filtertag.flongitude)/1000) <= notetag.nradius
        ELSE
        filtertag.ulatitude != ''
	END
and MATCH(note) AGAINST(keywords IN NATURAL LANGUAGE MODE)
group by nid, note;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `GETUSERS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `GETUSERS`(IN inputnoteid INT)
BEGIN
select filtertag.uid, uname
from (select note.uid, nid, note, tagid, nlatitude, nlongitude, nradius, nstarttimestamp, nendtimestamp, rid, user.uid as visibletouser
		from note natural join tagnotes, user
		where CASE vid
	WHEN 0
    Then user.uid = note.uid
    WHEN 1
    THEN user.uid in (select uid2 as Friends
						from friendship
						where uid1 = note.uid
						union
						select uid1 as Friends
						from friendship
						where uid2 = note.uid
                        union 
                        select uid as Friends
                        from user where uid = user.uid)
	ELSE user.uid in (select uid from user)
    END) notetag, (select filter.uid, tagid, fstarttimestamp, fendtimestamp, flatitude, flongitude, ulatitude, ulongitude, uloc.utimestamp, rid, user.uid as visiblefromuser
					from filter, user, (select uid, ulatitude, ulongitude, utimestamp
					from userlocation u1
					where utimestamp = (select max(utimestamp)
													from userlocation u2
													where u1.uid = u2.uid)) uloc
					where stateid = (select stateid from userstate where uid = filter.uid and iscurrent = 1) and uloc.uid = filter.uid and 
					CASE vid
					WHEN 0
					Then user.uid = filter.uid
					WHEN 1
					Then user.uid in (select uid2 as Friends
								from friendship
								where uid1 = filter.uid
								union
								select uid1 as Friends
								from friendship
								where uid2 = filter.uid
                                union
                                select uid from user
                                where uid = user.uid)
					ELSE user.uid in (select uid from user)
				END) filtertag, user
where notetag.nid = inputnoteid and CASE filtertag.tagid
		WHEN '-1' THEN notetag.tagid != ''
        ELSE notetag.tagid = filtertag.tagid
	END
and CASE 
		WHEN (filtertag.fstarttimestamp != '0000-00-00 00:00:00' and filtertag.fendtimestamp != '0000-00-00 00:00:00')
			THEN
				intime(filtertag.utimestamp, filtertag.fstarttimestamp, filtertag.fendtimestamp, filtertag.rid) = 1
		WHEN (filtertag.fstarttimestamp != '0000-00-00 00:00:00' and filtertag.fendtimestamp = '0000-00-00 00:00:00')
			THEN
				intime(filtertag.utimestamp, filtertag.fstarttimestamp, '', filtertag.rid) = 1
		ELSE filtertag.utimestamp != ''
	END
and intime(filtertag.utimestamp, notetag.nstarttimestamp, notetag.nendtimestamp, notetag.rid) = 1
and notetag.visibletouser = filtertag.uid and filtertag.visiblefromuser = notetag.uid
and haversine(filtertag.ulatitude, filtertag.ulongitude, notetag.nlatitude, notetag.nlongitude) <= notetag.nradius
and CASE 
		WHEN (filtertag.flatitude != -99.00000000 and filtertag.flongitude != -999.00000000)
        Then (haversine(filtertag.ulatitude, filtertag.ulongitude, filtertag.flatitude, filtertag.flongitude)/1000) <= notetag.nradius
        ELSE
        filtertag.ulatitude != ''
	END
and filtertag.uid = user.uid
group by filtertag.uid;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-12-17  6:55:48
