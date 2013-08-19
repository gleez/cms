<?php
	echo
		__('Hello :name!', array(':name' => $name)) . PHP_EOL . PHP_EOL .
		__('A request to reset the password for your account has been made at !site.', array('!site' => $config->site_url)) . PHP_EOL . PHP_EOL .
		__('You may now log in to !uri_brief clicking on this link or copying and pasting it in your browser.', array('!uri_brief' => $url)) . PHP_EOL . PHP_EOL .
		__("This is a one-time URL, so it can be used only once. It expires after one day and nothing will happen if it's not used.") . PHP_EOL .
		__('After logging in, you will be redirected, so you can change your password. We do not encourage you to lose your password again. Be aware that this behaviour can be dangerous.') . PHP_EOL  . PHP_EOL . PHP_EOL .
		__('Best Regards') . PHP_EOL .
		$config->site_name  . PHP_EOL .
		$config->site_slogan;
?>