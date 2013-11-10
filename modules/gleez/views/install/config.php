<?php echo '<?php'.PHP_EOL; ?>
return array
(
	'default' => array
	(
		'type'       => '<?php echo $type ?>',
		'connection' => array(
			/**
			 * The following options are available for MySQL:
			 *
			 * string   hostname     server hostname, or socket
			 * string   database     database name
			 * string   username     database username
			 * string   password     database password
			 * boolean  persistent   use persistent connections?
			 *
			 * Ports and sockets may be appended to the hostname.
			 */
			'hostname'   => '<?php echo $host ?>',
			'database'   => '<?php echo $dbname ?>',
			'username'   => '<?php echo $user ?>',
			'password'   => '<?php echo str_replace("'", "\\'", $password) ?>',
			'persistent' => FALSE,
		),
		'table_prefix' => '<?php echo $prefix ?>',
		'charset'      => 'utf8',
		'caching'      => FALSE,
		'profiling'    => FALSE,
	),
);
