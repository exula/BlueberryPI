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
  (16,'service_is_running','true'),
  (17,'service_should_run','1')


/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;