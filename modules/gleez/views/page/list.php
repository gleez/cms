<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<?php foreach($posts as $i => $post): ?>
	<!-- Post #<?php echo $i; ?> Teaser start -->
	<div id="post-<?php echo $i; ?>" class="<?php echo 'post-list' . ($post->sticky) ? ' sticky' : ' post-'. $post->status; ?>">
		<h2 class="post-title">
			<?php echo HTML::anchor($post->url, $post->title) ?>
		</h2>
		<?php
			echo View::factory($post->type.'/teaser')
					->set('post', $post)
					->set('config', $config)
					->set('page_title', TRUE);
		?>
	</div>
	<!-- Post #<?php echo $i; ?> Teaser end -->
<?php endforeach; ?>

<?php echo $pagination ?>