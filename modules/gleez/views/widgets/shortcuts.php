<div class="shortcuts">
	<?php foreach ($items as $key => $item): ?>
		<?php $title = '<i class="shortcut-icon '.$item['image'].'"></i><span class="shortcut-label">'.Text::plain($item['title']).'</span>'; ?>
		<?php echo HTML::anchor($item['url'], $title, array('class' => 'shortcut')); ?>
	<?php endforeach; ?>
</div>