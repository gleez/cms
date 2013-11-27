<div class="shortcuts">
	<?php foreach ($items as $key => $item): ?>
		<ul class="quick-actions">
			<li>
				<?php $title = '<i class="shortcut-icon fa '.$item['image'].' fa-2x"></i></br><span class="shortcut-label">'.Text::plain($item['title']).'</span>'; ?>
				<?php echo HTML::anchor($item['url'], $title, array('class' => 'shortcut')); ?>
			</li>
		</ul>
	<?php endforeach; ?>
</div>