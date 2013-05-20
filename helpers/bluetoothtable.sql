CREATE DATABASE IF NOT EXISTS inoutboard;
USE inoutboard;

CREATE TABLE IF NOT EXISTS `bluetooth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(11) DEFAULT NULL,
  `bluetooth` varchar(255) DEFAULT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  `status` varchar(10) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  `priority` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;

INSERT INTO `config` (`id`, `name`, `value`)
VALUES
  (23,'apikey','aa625902eebedb7cf4fe100ada98996e'),
  (16,'service_is_running','true'),
  (17,'service_should_run','1'),
  (294,'users','bjcpgd,Brad Coudriet,38:0A:94:B1:31:6E,https://request.cias.rit.edu/avatar.php?username=bjcpgd'),
  (291,'users','jpspgd,Jay Sullivan,0C:71:5D:FC:B7:31,https://request.cias.rit.edu/avatar.php?username=jpspgd'),
  (290,'users','rsfpgd,Robert Fleck,40:B3:95:6F:98:9F,https://request.cias.rit.edu/avatar.php?username=rsfpgd'),
  (313,'users','rrhpph,Rob Henderson,54:26:96:35:F6:A4,https://request.cias.rit.edu/avatar.php?username=rrhpph');

/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;