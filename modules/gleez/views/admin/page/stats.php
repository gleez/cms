<?php defined('SYSPATH') or die('No direct script access.'); ?>

<ul class="page-stats">
	<li>
		<h3><?php echo __('Categories'); ?></h3>
		<ul>
			<li><?php echo __('Total:'); ?> <?php echo $stats['categories']['total'] ?></li>
		</ul>
	</li>
	<li>
		<h3><?php echo __('Tags'); ?></h3>
		<ul>
			<li><?php echo __('Total:'); ?> <?php echo $stats['tags']['total'] ?></li>
		</ul>
	</li>
	<li>
		<h3><?php echo __('Posts'); ?></h3>
		<ul>
			<li><?php echo __('Total:'); ?> <?php echo $stats['articles']['total'] ?></li>
		</ul>
	</li>
	<li>
		<h3><?php echo __('Comments'); ?></h3>
		<ul>
			<li><?php echo __('Total:'); ?> <?php echo $stats['comments']['total'] ?></li>
	</li>
</ul>
