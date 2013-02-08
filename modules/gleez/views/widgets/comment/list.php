<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php if( isset($comments) ): ?>
	<ul class="comments-list">
		<?php foreach($comments as $i => $comment) : ?>
			<li class="widget-title">
				<?php echo HTML::anchor($comment['post_url']."#Comment_{$comment['id']}", $comment['title']) ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
