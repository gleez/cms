CREATE TABLE `<?php echo $table_name; ?>` (
	`timestamp` varchar(14) NOT NULL,
	`filename` varchar(255) NOT NULL,
	`description` varchar(100) NOT NULL,
	`mgroup` varchar(100) NOT NULL,
	`applied` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`timestamp`,`mgroup`),
	UNIQUE KEY `MIGRATION_ID` (`timestamp`,`filename`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
