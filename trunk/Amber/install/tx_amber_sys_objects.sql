#
# Table structure for table 'tx_amber_sys_objects'
#
CREATE TABLE IF NOT EXISTS tx_amber_sys_objects (
  uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
  pid int(11) unsigned DEFAULT '0' NOT NULL,
  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
  crdate int(11) unsigned DEFAULT '0' NOT NULL,
  cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
  sorting int(10) unsigned DEFAULT '0' NOT NULL,
  deleted tinyint(3) unsigned DEFAULT '0' NOT NULL,
  hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
  fe_group int(11) DEFAULT '0' NOT NULL,
  name varchar(255) DEFAULT '' NOT NULL,
  design mediumtext,
  class varchar(255) DEFAULT '',
  code mediumtext,
  `type` tinyint(4) unsigned DEFAULT '0' NOT NULL,
  version int(11) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (uid),
  UNIQUE KEY (`type`, name),
  KEY parent (pid)
);
