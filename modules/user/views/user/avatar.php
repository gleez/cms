<?php defined('SYSPATH') OR die('No direct script access allowed.'); ?>

<div class="Photo">
	<?php if ($avatar AND $is_gravatar): ?>
		<?php echo $avatar->setDefaultImage(URL::site('media/images/avatar-user-400.png', TRUE))->setSize(150)->getImage(array('alt' => $nick)); ?>
	<?php elseif ($avatar): ?>
		<?php echo HTML::resize($avatar, array('alt' => $nick, 'height' => 150, 'width' => 150, 'type' => 'resize', 'itemprop' => 'image')); ?>
	<?php else: ?>
		<?php echo HTML::resize('media/images/avatar-user-400.png', array('alt' => $nick, 'height' => 150, 'width' => 150, 'type' => 'resize')); ?>
	<?php endif; ?>
</div>