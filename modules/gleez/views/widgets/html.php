<?php defined("SYSPATH") OR die("No direct script access."); ?>

<div id="widget-<?php echo $widget->module; ?>-<?php echo $widget->name; ?> <?php echo (isset($id)) ? 'widget-'.$id : '' ?>" class="widget blockme widget-<?php echo $widget->name; ?> <?php echo ($widget->menu) ? 'widget-menu' : ''; ?> <?php echo (isset($zebra)) ? 'widget-'.$zebra : '' ?>">

	<?php if ($widget->show_title): ?>
		<div class="widget-header">
			<i class="icon-<?php echo $widget->name; ?>"></i>
			<h3><?php echo Text::plain($title); ?></h3>
		</div>
	<?php endif; ?>

	<div class="widget-content"><?php echo $content; ?></div>

</div>