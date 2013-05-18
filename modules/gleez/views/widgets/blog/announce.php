<?php defined("SYSPATH") OR die("No direct script access."); ?>

<?php if (isset($items)): ?>
	<div class="recent-announce-blogs">
		<div class="recent-announce-wrapper" itemscope itemtype="http://schema.org/CreativeWork">
			<?php foreach($items as $item) : ?>
				<div class="announce-blog">
					<div class="image">
						<?php echo ( ! empty($item['image'])) ? HTML::resize($item['image'], array('alt' => $item['title'], 'height' => 140, 'width' => 180, 'type' => 'resize', 'itemprop' => 'image')) : '<div class="empty-photo"><i class="icon-camera-retro icon-2x"></i></div>'; ?>
					</div>
					<h3 itemprop='url'><?php echo HTML::anchor($item['url'], $item['title'], array('itemprop' => 'url')) ?></h3>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endif; ?>