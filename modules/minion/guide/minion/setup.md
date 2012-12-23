# Minion Setup

To use minion, you'll need to make a small change to your index.php file:

	-/**
	- * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
	- * If no source is specified, the URI will be automatically detected.
	- */
	-echo Request::factory()
	-       ->execute()
	-       ->send_headers(TRUE)
	-       ->body();
	+if (PHP_SAPI == 'cli') // Try and load minion
	+{
	+       class_exists('Minion_Task') OR die('minion required!');
	+       set_exception_handler(array('Kohana_Minion_Exception_Handler', 'handler'));
	+
	+       Minion_Task::factory(Minion_CLI::options())->execute();
	+}
	+else
	+{
	+       /**
	+        * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
	+        * If no source is specified, the URI will be automatically detected.
	+        */
	+       echo Request::factory()
	+               ->execute()
	+               ->send_headers(TRUE)
	+               ->body();
	+}

This will short-circuit your index file to intercept any cli calls, and route them to the minion module.