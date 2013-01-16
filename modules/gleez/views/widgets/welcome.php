<?php defined("SYSPATH") or die("No direct script access.") ?>


    <?php echo __("This is your administration dashboard and it provides a quick overview of status
		messages, recent updates, and frequently used options.<br /> <br />The admin menu
		provides quick access to all of <strong>:cms_url</strong>'s options and settings.
		Here are a few of the most used options to get you started.",
		array(':cms_url' => Html::anchor('http://gleezcms.org', 'Gleez CMS', array('target'=>'_blank')))); ?>
    
    <ul>
	<li>
	    <?php echo __('General Settings - choose your :settings_url.', 
		array(':settings_url' => Html::anchor('admin/settings', 'Settings'))) ?>
	</li>
	
	<li>
	    <?php echo __('Customize - :modules_url to add cool features!',
		array(':modules_url' => Html::anchor('admin/modules', 'Modules'))) ?>
	</li>
    </ul>

