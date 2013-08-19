<?php foreach($posts as $i => $post): ?>
	<div id="post-<?php echo $i; ?>" class="post-list<?php if ($post->sticky) { echo ' sticky'; } ?> <?php echo ' post-'. $post->status; ?>">
		<h2 class="post-title"><?php echo HTML::anchor($post->url, $post->title) ?></h2>
		<?php
			echo View::factory($post->type.'/post')
					->set('post', $post)
					->set('page_title', TRUE)
					->set('teaser', TRUE);
		?>
	</div>
<?php endforeach; ?>

<?php echo $pagination ?>