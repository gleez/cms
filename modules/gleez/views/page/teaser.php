<?php defined('SYSPATH') OR die('No direct script access.') ?>

<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->type . ' post teaser post-' . $post->status; ?>">

	<div class="row-fluid meta">

		<div class="span6">
			<?php if ($config->use_submitted): ?>
				<span class="author">
				<?php
					$nick = $post->user->nick;
					$url  = $post->user->url;
					$pic  = ( strlen($post->user->picture) > 4 ) ? $post->user->picture : 'media/images/commentor.jpg';
					$img = HTML::resize($pic, array('title' => $nick, 'width' => 32, 'height' => 32, 'type' => 'resize') );

					echo HTML::anchor($url, $img);
					echo HTML::anchor($url, $nick, array('title' => $nick));
					unset($nick, $img, $url);
				?>
				</span>

				<span class="DateCreated"><?php echo Date::date($post->created); ?></span>
			<?php endif;?>
		</div>

		<?php if ($post->taxonomy): ?>
			<div class="taxonomy span6"> <?php echo $post->taxonomy; ?> </div>
		<?php endif;?>
	</div>

	<div class="post-content">
		<?php echo $post->teaser; ?>
	</div>

		<?php if ($post->tagcloud): ?>
			<div class="tags"><?php echo __('Tagged with :tag', array(':tag' => $post->tagcloud)); ?></div>
		<?php endif;?>

</div>
