-- MySQL-Front 3.0


-- Host: server5-DEV    Database: typo3_dev
-- ------------------------------------------------------
-- Server version 4.0.18

--
-- Table structure for table tx_amber_sys_objects
--

CREATE TABLE `tx_amber_sys_objects` (
  `uid` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `tstamp` int(11) unsigned NOT NULL default '0',
  `crdate` int(11) unsigned NOT NULL default '0',
  `cruser_id` int(11) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `deleted` tinyint(4) unsigned NOT NULL default '0',
  `hidden` tinyint(4) unsigned NOT NULL default '0',
  `fe_group` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `design` mediumtext,
  `class` varchar(255) default NULL,
  `code` mediumtext,
  `type` tinyint(4) default NULL,
  `version` int(4) default NULL,
  PRIMARY KEY  (`uid`),
  KEY `parent` (`pid`)
) TYPE=MyISAM;
