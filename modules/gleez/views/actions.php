<div class="btn-group">
	<a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
		<i class="icon-cog"></i> <span class="caret"></span>
	</a>
	<ul class="dropdown-menu">
		<?php foreach($actions as $action): ?>
			<li><?php echo HTML::anchor($action['link'], $action['text'], array('data-toggle' => 'popup')); ?></li>
		<?php endforeach; ?>
	</ul>
</div>