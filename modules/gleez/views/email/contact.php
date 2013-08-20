<h1><?php echo __('Hello!')?></h1>

<p><?php echo __(':name sent a message using the contact form at :site.', array(
		':name' => Text::plain($name),
		':site' => URL::site('contact', TRUE)
	));
?></p>

<?php echo Text::markup($body); ?>
<br>
<hr>
<p>
	<?php echo __('Best Regards'); ?>,<br>
	<?php echo $config->get('site_url', 'www.gleezcms.org'); ?><br>
	<?php echo Template::getSiteName() ?>
</p>