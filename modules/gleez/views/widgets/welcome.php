<?php defined('SYSPATH') OR die('No direct script access.'); ?>


	<p>
		<?php
			echo __('This is your administration dashboard and it provides a quick overview of status messages, recent updates, and frequently used options.');
		?>
	</p>
	<p>
		<?php
			echo __('The admin menu provides quick access to all of :cms_url\'s options and settings. Here are a few of the most used options to get you started.',
				array(':cms_url' => HTML::anchor('http://gleezcms.org', 'Gleez CMS', array('target'=>'_blank'))));
		?>
	</p>

	<ul>
		<li>
			<?php
				echo __('General Settings &mdash; choose your :settings_url.', 
					array(':settings_url' => HTML::anchor('admin/settings', __('settings')) ));
			?>
		</li>

		<li>
			<?php
				echo __('Customize &mdash; :modules_url to add cool features!',
					array(':modules_url' => HTML::anchor('admin/modules', __('modules'))  ));
			?>
		</li>
	</ul>

