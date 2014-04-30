<div class="shortcuts">
	<ul class="quick-actions">
		<?php foreach ($items as $key => $item): ?>
			<li>
				<?php $title = '<i class="shortcut-icon fa '.$item['image'].' fa-2x"></i></br><span class="shortcut-label">'.Text::plain($item['title']).'</span>'; ?>
				<?php echo HTML::anchor($item['url'], $title, array('class' => 'shortcut')); ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>