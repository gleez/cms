<?php defined("SYSPATH") or die("No direct script access.") ?>

<div id="post-<?php echo $post->id; ?>" class="<?php echo $post->type ?> post<?php if ($post->sticky) { echo ' sticky'; } ?> <?php echo ' post-'. $post->status;  ?>">

        <?php
                $widget_p_top = Widgets::instance()->render('post_top');
                $widget_p_bot = Widgets::instance()->render('post_bottom');
        ?>

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

				<span class="DateCreated"> <?php echo Date::date($post->created); ?> </span>
			<?php endif;?>
		</div>

		<?php if ($post->taxonomy): ?> <div class="taxonomy span6"> <?php echo $post->taxonomy; ?> </div> <?php endif;?>
	</div>

	<div class="content"> <?php echo $post->body; ?> </div>

	<?php if ($post->tagcloud): ?>
	    <div class="tags"><?php echo __('Tagged with :tag', array(':tag' => $post->tagcloud) ); ?></div>
	<?php endif;?>

	<?php if($widget_p_bot): ?>
		<div id="post-bottom" class="clear-block"> <?php echo $widget_p_bot; ?> </div>
                <?php unset($widget_p_bot); ?>
	<?php endif; ?>
</div>
