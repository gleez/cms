<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<?php echo __('Hello!').PHP_EOL ?>

<?php echo __(':name sent a message using the contact form at :site.', array(
		':name' => Text::plain($name),
		':site' => URL::site('contact', TRUE)
	));
?>

<?php echo Text::markup($body); ?>