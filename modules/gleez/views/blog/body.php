<?php defined("SYSPATH") or die("No direct script access.") ?>

<div id="blog-<?php echo $post->id; ?>" class="<?php echo $post->type ?> blog<?php if ($post->sticky) { echo ' sticky'; } ?> <?php echo ' blog-'. $post->status;  ?>">

    <?php
        $widget_p_top = Widgets::instance()->render('post_top');
        $widget_p_bot = Widgets::instance()->render('post_bottom');
    ?>

	<?php if($post->taxonomy OR $config->use_submitted): ?>
		<div class="row-fluid meta">
			<?php if ($config->use_submitted): ?>
				<div class="span6">
					<span class="author">
					<?php
						$nick = $post->user->nick;
						$url  = $post->user->url;
						$pic  = (strlen($post->user->picture) > 4) ? $post->user->picture : 'media/images/commentor.jpg';
						$img = HTML::resize($pic, array('title' => $nick, 'width' => 32, 'height' => 32, 'type' => 'resize'));
	
						echo HTML::anchor($url, $img);
						echo HTML::anchor($url, $nick, array('title' => $nick));
						unset($nick, $img, $url);
					?>
					</span>
	
					<span class="DateCreated">
						<?php echo Date::date_format($post->created); ?>
					</span>
				</div>
			<?php endif;?>
	
			<?php if ($post->taxonomy): ?>
				<div class="taxonomy span6 pull-right">
					<?php echo $post->taxonomy; ?>
				</div>
			<?php endif;?>
		</div>
	<?php endif;?>

	<div class="content">
		<?php echo $post->body; ?>
	</div>

	<?php if ($post->tagcloud): ?>
	    <div class="tags">
		    <?php echo __('Tagged with :tag', array(':tag' => $post->tagcloud) ); ?>
	    </div>
	<?php endif;?>

	<?php if($widget_p_bot): ?>
		<div id="blog-bottom" class="clear-block">
			<?php echo $widget_p_bot; ?>
		</div>
		<?php unset($widget_p_bot); ?>
	<?php endif; ?>
</div>