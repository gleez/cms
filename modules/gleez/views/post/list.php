<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php foreach($posts as $i => $post) : ?>

	<div id="post-<?php echo $i; ?>" class="post<?php if ($post->sticky) { echo ' sticky'; } ?>
					<?php echo ' post-'. $post->status; ?>">
                
                <h2 class="post-title"><?php echo html::anchor($post->url, $post->title) ?></h2>
                
                <?php //echo View::factory($post->type.'/post')->set($post->type, $post)->set('use_excerpt', TRUE); ?>
                <?php echo isset( $teaser ) ? $post->teaser : $post->content ?>
	</div>
	
<?php endforeach; ?>

<?php echo $pagination ?>