-- MySQL-Front 3.0


-- Host: server5-DEV    Database: typo3_dev
-- ------------------------------------------------------
-- Server version 4.0.18

--
-- Table structure for table amber_sys_objects
--

CREATE TABLE `amber_sys_objects` (
  `uid` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) default NULL,
  `timestamp` timestamp(14) NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `design` mediumtext,
  `class` varchar(255) default NULL,
  `code` mediumtext,
  `type` tinyint(4) default NULL,
  `version` int(4) default NULL,
  PRIMARY KEY  (`uid`),
  KEY `idxName` (`name`)
) TYPE=MyISAM ROW_FORMAT=FIXED;
