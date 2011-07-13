CREATE TABLE IF NOT EXISTS `pluspost` (
  `pluspost_id` bigint(20) NOT NULL auto_increment,
  `googleplus_postid` varchar(128) collate utf8_bin NOT NULL,
  `author_id` varchar(32) collate utf8_bin NOT NULL,
  `post_data` text collate utf8_bin NOT NULL,
  `share_content` text collate utf8_bin NOT NULL,
  `shared_postid` varchar(128) collate utf8_bin NOT NULL,
  `raw_data` text collate utf8_bin NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_dt` datetime NOT NULL,
  PRIMARY KEY  (`pluspost_id`),
  UNIQUE KEY `googleplus_postid` (`googleplus_postid`),
  KEY `author_id` (`author_id`),
  KEY `shared_postid` (`shared_postid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

