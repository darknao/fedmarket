CREATE TABLE `apicache` (
  `host` varchar(64) NOT NULL,
  `path` varchar(64) NOT NULL,
  `params` varchar(64) NOT NULL,
  `content` longtext NOT NULL,
  `cachedUntil` datetime default NULL,
  PRIMARY KEY  (`host`,`path`,`params`)
) DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci;
