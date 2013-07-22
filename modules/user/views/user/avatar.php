<?php defined('SYSPATH') OR die('No direct script access allowed.'); ?>

<div class="Photo">
	<?php
		echo $avatar
			? HTML::resize($avatar, array('alt' => $nick, 'height' => 150, 'width' => 150, 'type' => 'resize', 'itemprop' => 'image'))
			: '<div class="empty-photo"><i class="icon-camera-retro icon-4x"></i></div>';
	?>
</div>