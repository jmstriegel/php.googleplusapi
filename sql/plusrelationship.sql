CREATE TABLE IF NOT EXISTS `plusrelationship` (
  `plusrelationship_id` bigint(20) NOT NULL auto_increment,
  `owner_id` varchar(32) collate utf8_bin NOT NULL,
  `hasincircle_id` varchar(32) collate utf8_bin NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_dt` datetime NOT NULL,
  PRIMARY KEY  (`plusrelationship_id`),
  KEY `owner_id` (`owner_id`),
  KEY `hasincircle_id` (`hasincircle_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

