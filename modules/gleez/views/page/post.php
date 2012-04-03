<?php defined("SYSPATH") or die("No direct script access.") ?>

<div class="post-page-wrapper">
	<?php if ( isset($page) ): ?>
		<div class="post-page"><?php echo $page; ?></div>
	<?php endif;?>
</div>

<div class="list-comments">
	<?php if ( isset($comments) ): ?>
		<div class="post-comments"><?php echo $comments; ?></div>
	<?php endif;?>
</div>

<div class="post-comment-form-wrapper">
	<?php if ( isset($provider_buttons) ): ?>
		<div id="post-provider-buttons">
			<?php echo $provider_buttons; ?>
			<?php echo Form::textarea('comment', '', array('disabled' => true,
								       'class' => 'textarea full', 'rows' => 5)) ?>
		</div>
	<?php endif;?>
	
	<?php if ( isset($comment_form) ): ?>
		<div class="post-comment-form"><?php echo $comment_form; ?></div>
	<?php endif;?>
</div>
