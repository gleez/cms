CREATE TABLE `<?php echo $table_name; ?>` (
	`timestamp` varchar(14) NOT NULL,
	`description` varchar(100) NOT NULL,
	`group` varchar(100) NOT NULL,
	`applied` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`timestamp`,`group`),
	UNIQUE KEY `MIGRATION_ID` (`timestamp`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
