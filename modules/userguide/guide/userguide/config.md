# Configuration

The userguide has the following config options, available in `config/userguide.php`.

	return array
	(
		// Enable the API browser.  TRUE or FALSE
		'api_browser'  => TRUE,
		
		// Enable these packages in the API browser.  TRUE for all packages, or a string of comma seperated packages, using 'None' for a class with no @package
		// Example: 'api_packages' => 'Kohana,Kohana/Database,Kohana/ORM,None',
		'api_packages' => TRUE,
		
	);

You can enable or disabled the entire api browser, or limit it to only show certain packages.  To disable a module from showing pages in the userguide, simply change that modules `enabled` option using the cascading filesystem.  For example:

	`application/config/userguide.php`
	
	return array
	(
		'modules' => array
		(
			'kohana' => array
			(
				'enabled' => FALSE,
			),
			'database' => array
			(
				'enabled' => FALSE,
			)
		)
	)
	
Using this you could make the userguide only show your modules and your classes in the api browser, if you wanted to host your own documentation on your site.  Feel free to change the styles and views as well, but be sure to give credit where credit is due!