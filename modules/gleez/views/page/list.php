<div class="row">
	<div class="col-md-1 post-social pull-right">
		<?php echo HTML::icon($rss_link, 'fa-rss', array('title' => 'RSS 2.0', 'class' => 'post-rss')) ?>
	</div>

	<?php foreach($posts as $i => $post): ?>
		<article id="post-<?php echo $post->id; ?>" class="col-md-12 post-list <?php echo ($post->sticky) ? ' sticky' : ' post-'.$post->status; ?>" itemscope itemtype="http://schema.org/Article">
			<header>
				<h2 class="post-title" itemprop="name">
					<?php echo HTML::anchor($post->url, $post->title, array('itemprop' => 'url')); ?>
				</h2>
			</header>

				<?php if ($post->promote): ?>
				<i class="post-bookmark clearfix"></i>
				<?php endif; ?>
				<?php
				echo View::factory('page/teaser')
					->set('post',       $post)
					->set('config',     $config)
					->set('page_title', TRUE);
				?>
		</article>
	<?php endforeach; ?>

</div>

<?php echo $pagination; ?>
