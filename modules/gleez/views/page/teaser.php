<?php if ($post->taxonomy OR $config->use_submitted): ?>
	<section class="post-meta row">
		<?php if ($config->use_submitted): ?>
			<div class="col-md-7">
				<span class="post-created">
					<i class="fa fa-calendar"></i>
					<time itemprop="datePublished" content="<?php echo Date::date_format($post->created, DateTime::ISO8601)?>" datetime="<?php echo Date::date_format($post->created, DateTime::ISO8601)?>">
						<?php echo Date::date_format($post->created); ?>
					</time>
				</span>
				<span class="post-author">
					<i class="fa fa-user"></i>
					<?php echo HTML::anchor($post->user->url, $post->user->name, array('title' => $post->user->nick, 'itemprop' => 'author')); ?>
				</span>
			</div>
		<?php endif;?>
		<?php if ($post->taxonomy): ?>
			<div class="col-md-5 pull-right">
				<span class="post-taxonomy"><?php echo $post->taxonomy; ?></span>
			</div>
		<?php endif;?>
	</section>
<?php endif;?>

<section class="post-entry">
	<div class="teaser" itemprop="articleSection">
		<?php echo $post->teaser; ?>
	</div>
	<?php if ($post->tagcloud): ?>
		<div class="post-tags col-md-12">
			<span class="tagcloud">
				<?php echo __('Tagged with :tag', array(':tag' => $post->tagcloud)); ?>
			</span>
		</div>
	<?php endif;?>
	<div class="post-postarea">
		<?php echo HTML::anchor($post->url, __('Read More'), array('title' => __('Read more about :title', array(':title' => $post->title)), 'class' => 'readmore btn btn-default btn-xs', 'itemprop' => 'url')); ?>
	</div>
</section>
