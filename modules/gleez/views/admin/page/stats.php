<?php defined('SYSPATH') OR die('No direct script access allowed.'); ?>

<ul class="page-stats">
	<li>
		<h3><?php _e('Categories'); ?></h3>
		<ul>
			<li><?php _e('Total'); ?>: <?php echo $stats['categories']['total'] ?></li>
		</ul>
	</li>
	<li>
		<h3><?php _e('Tags'); ?></h3>
		<ul>
			<li><?php _e('Total'); ?>: <?php echo $stats['tags']['total'] ?></li>
		</ul>
	</li>
	<li>
		<h3><?php _e('Posts'); ?></h3>
		<ul>
			<li><?php _e('Total'); ?>: <?php echo $stats['articles']['total'] ?></li>
		</ul>
	</li>
	<li>
		<h3><?php _e('Comments'); ?></h3>
		<ul>
			<li><?php _e('Total'); ?>: <?php echo $stats['comments']['total'] ?></li>
	</li>
</ul>
