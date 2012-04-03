<?php defined('SYSPATH') or die('No direct script access.'); ?>

<ul>
	<li>Categories:
		<ul>
			<li>Total: <?php echo $stats['categories']['total'] ?></li>
		</ul>
	</li>
	<li>Tags:
		<ul>
			<li>Total: <?php echo $stats['tags']['total'] ?></li>
		</ul>
	</li>
	<li>Posts:
		<ul>
			<li>Total: <?php echo $stats['articles']['total'] ?></li>
		</ul>
	</li>
	<li>Comments:
		<ul>
			<li>Total: <?php echo $stats['comments']['total'] ?></li>
	</li>
</ul>
