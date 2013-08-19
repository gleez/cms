<?php foreach($posts as $i => $post): ?>
	<div id="blog-<?php echo $i; ?>" class="blog-list <?php echo ($post->sticky) ? ' sticky' : ' blog-'. $post->status; ?>">
		<h2 class="blog-title">
			<?php echo HTML::anchor($post->url, $post->title); ?>
		</h2>
		<?php
		echo View::factory($post->type.'/teaser')
			->set('post',       $post)
			->set('config',     $config)
			->set('page_title', TRUE);
		?>
	</div>
<?php endforeach; ?>

<?php echo $pagination; ?>