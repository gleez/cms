<?php defined("SYSPATH") or die("No direct script access.") ?>

<div class="blog-page-wrapper">
	<?php if (isset($blog)): ?>
		<div class="blog-page">
			<?php echo $blog; ?>
		</div>
	<?php endif;?>
</div>

<div class="list-comments">
	<?php if (isset($comments)): ?>
		<div class="blog-comments">
			<?php echo $comments; ?>
		</div>
	<?php endif;?>
</div>

<div class="blog-comment-form-wrapper">
	<?php if (isset($provider_buttons)): ?>
		<div id="post-provider-buttons">
			<?php echo $provider_buttons; ?>
			<?php echo Form::textarea('comment', '', array('disabled' => TRUE, 'class' => 'textarea full', 'rows' => 5)); ?>
		</div>
	<?php endif;?>
	
	<?php if (isset($comment_form)): ?>
		<div class="post-comment-form">
			<?php echo $comment_form; ?>
		</div>
	<?php endif;?>
</div>
