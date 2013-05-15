<?php defined("SYSPATH") OR die("No direct script access."); ?>

<?php if (isset($items)): ?>
	<ul class="blogs-list">
		<?php foreach($items as $item) : ?>
			<li class="widget-title" id='widget-title-'<?php echo $item['id'] ?>>
				<?php echo HTML::anchor($item['url'], $item['title']) ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>