<div id="widget-<?php echo $widget->module; ?>-<?php echo Text::plain($widget->name); ?> <?php echo (isset($id)) ? 'widget-'.$id : '' ?>" class="panel panel-default widget widget-<?php echo Text::plain($widget->name); ?> <?php echo ($widget->menu) ? 'widget-menu' : ''; ?> <?php echo (isset($zebra)) ? 'widget-'.$zebra : '' ?>">
  	<?php if ($widget->show_title): ?>
		<div class="panel-heading">
			<h3 class="panel-title"><i class="fa <?php echo Text::plain($widget->icon); ?>"></i> <?php echo Text::plain($title); ?></h3>
		</div>
  	<?php endif; ?>
	<div class="panel-body">
		<?php echo $content; ?>
	</div>
</div>