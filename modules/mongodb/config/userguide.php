<?php

return array(
	// Leave this alone
	'modules' => array(

		// This should be the path to this modules userguide pages, without the 'guide/'. Ex: '/guide/modulename/' would be 'modulename'
		'mongodb' => array(

			// Whether this modules userguide pages should be shown
			'enabled' => true,

			// The name that should show up on the userguide index page
			'name' => 'Gleez Mango',

			// A short description of this module, shown on the index page
			'description' => 'Simple object wrapper for the Mongo PHP driver classes which makes using Mongo in your PHP application more like ORM',

			// Copyright message, shown in the footer for this module
			'copyright' => '&copy; 2011-'.date('Y').' Gleez Technologies',
		)
	)
);
