<?php defined('SYSPATH') OR die('No direct script access.') ?>

<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->type . ' post teaser post-' . $post->status; ?>">

	<?php if($post->taxonomy OR $config->use_submitted): ?>
		<div class="row-fluid meta">
			<?php if ($config->use_submitted): ?>
				<div class="span6">
					<span class="author">
						<?php echo HTML::anchor($post->user->url, User::getAvatar($post->user)); ?>
						<?php echo HTML::anchor($post->user->url, $post->user->nick, array('title' => $post->user->nick)); ?>
					</span>

					<span class="DateCreated"><?php echo Date::date_format($post->created); ?></span>
				</div>
			<?php endif;?>
		
			<?php if ($post->taxonomy): ?> <div class="taxonomy span6 pull-right"> <?php echo $post->taxonomy; ?> </div> <?php endif;?>
		</div>
	<?php endif;?>

	<div class="post-content"> <?php echo $post->teaser; ?> </div>

	<?php if ($post->tagcloud): ?>
		<div class="tags"><?php echo __('Tagged with :tag', array(':tag' => $post->tagcloud) ); ?></div>
	<?php endif;?>

</div>
