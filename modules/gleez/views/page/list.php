<?php defined("SYSPATH") OR die("No direct script access.") ?>

<?php foreach($posts as $i => $post): ?>

	<div id="post-<?php echo $i; ?>" class="post-list<?php if ($post->sticky) { echo ' sticky'; } ?> <?php echo ' post-'. $post->status; ?>">
		<h2 class="post-title"><?php echo HTML::anchor($post->url, $post->title) ?></h2>
			<?php
				// @todo It need be in Controller, but don't in View. Views must contains HTML only, but no php-code
				echo View::factory($post->type.'/teaser')
							->set('post', $post)
							->set('config', $config)
							->set('page_title', TRUE);
			?>
	</div>

<?php endforeach; ?>

<?php echo $pagination ?>