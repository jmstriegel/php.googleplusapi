CREATE TABLE IF NOT EXISTS `plusperson` (
  `plusperson_id` bigint(20) NOT NULL auto_increment,
  `googleplus_id` varchar(32) collate utf8_bin NOT NULL,
  `profile_photo` varchar(255) collate utf8_bin NOT NULL,
  `first_name` varchar(128) collate utf8_bin NOT NULL,
  `last_name` varchar(128) collate utf8_bin NOT NULL,
  `introduction` text collate utf8_bin NOT NULL,
  `subhead` text collate utf8_bin NOT NULL,
  `raw_data` text collate utf8_bin NOT NULL,
  `fetched_relationships` int(11) NOT NULL default '0',
  `created_dt` datetime NOT NULL,
  `modified_dt` datetime NOT NULL,
  PRIMARY KEY  (`plusperson_id`),
  UNIQUE KEY `googleplus_id` (`googleplus_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
