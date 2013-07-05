<?php defined('SYSPATH') OR die('No direct script access.') ?>

<div id="blog-<?php echo $post->id; ?>" class="<?php echo $post->type . ' blog teaser blog-' . $post->status; ?>">

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
	
					<span class="DateCreated"><?php echo Date::date_format($post->created); ?></span>
				</div>
			<?php endif;?>
		
			<?php if ($post->taxonomy): ?> <div class="taxonomy span6 pull-right"> <?php echo $post->taxonomy; ?> </div> <?php endif;?>
		</div>
	<?php endif;?>

	<div class="blog-content"> <?php echo $post->teaser; ?> </div>

	<?php if ($post->tagcloud): ?>
		<div class="tags"><?php echo __('Tagged with :tag', array(':tag' => $post->tagcloud) ); ?></div>
	<?php endif;?>

</div>
