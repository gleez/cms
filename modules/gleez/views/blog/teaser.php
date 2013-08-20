<div class="<?php echo $post->type ?> blog teaser">

	<?php if($post->taxonomy OR $config->use_submitted): ?>
		<div class="row-fluid meta">
			<?php if ($config->use_submitted): ?>
				<div class="span7">
					<span class="author">
						<?php echo HTML::anchor($post->user->url, User::getAvatar($post->user)); ?>
						<?php echo HTML::anchor($post->user->url, $post->user->nick, array('title' => $post->user->nick)); ?>
					</span>
					<span class="date-created"><?php echo Date::date_format($post->created); ?></span>
				</div>
			<?php endif;?>
		
			<?php if ($post->taxonomy): ?> <div class="taxonomy span5 pull-right"> <?php echo $post->taxonomy; ?> </div> <?php endif;?>
		</div>
	<?php endif;?>

	<div class="blog-content"> <?php echo $post->teaser; ?> </div>

	<?php if ($post->tagcloud): ?>
		<div class="tagcloud"><?php echo __('Tagged with :tag', array(':tag' => $post->tagcloud) ); ?></div>
	<?php endif;?>

</div>
