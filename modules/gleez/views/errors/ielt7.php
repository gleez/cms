<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<!--[if lt IE 7]>
	<div class="alert">
		<strong><?php echo __('Warning!'); ?></strong>
		<?php
			echo __('You are using an :out browser. Please :url or :frame to improve your experience', array(
						':out' => '<strong>'.__('outdated').'</strong>',
						':url' => HTML::anchor('http://browsehappy.com/', __('upgrade your browser')),
						':frame' => HTML::anchor('http://www.google.com/chromeframe/?redirect=true&hl='.substr(I18n::$lang, 0, 2), __('activate Google Chrome Frame')),
					))
		?>
	</div>
<![endif]-->