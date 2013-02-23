<?php defined('SYSPATH') or die('No direct script access.'); ?>

<ul>
  <li><?php echo __('Categories:'); ?>
		<ul>
      <li><?php echo __('Total:'); ?> <?php echo $stats['categories']['total'] ?></li>
		</ul>
	</li>
  <li><?php echo __('Tags:'); ?>
		<ul>
			<li><?php echo __('Total:'); ?> <?php echo $stats['tags']['total'] ?></li>
		</ul>
	</li>
  <li><?php echo __('Posts:'); ?>
		<ul>
			<li><?php echo __('Total:'); ?> <?php echo $stats['articles']['total'] ?></li>
		</ul>
	</li>
  <li><?php echo __('Comments:'); ?>
		<ul>
			<li><?php echo __('Total:'); ?> <?php echo $stats['comments']['total'] ?></li>
	</li>
</ul>
