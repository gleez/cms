<?php defined("SYSPATH") or die("No direct script access.") ?>

<div class="post-page-wrapper">
	<?php if (isset($page)): ?>
		<div class="post-page">
			<?php echo $page; ?>
		</div>
	<?php endif;?>
</div>

<div class="list-comments">
	<?php if (isset($comments)): ?>
		<div class="post-comments">
			<?php echo $comments; ?>
		</div>
	<?php endif;?>
</div>

<div class="post-comment-form-wrapper">
	<?php if (isset($provider_buttons) AND ! isset($comment_form)): ?>
		<p>
			<?php
			_e('Only authorized users can post comments. :register or login using one of these services:',
				array(':register' => HTML::anchor(Route::get('user')->uri(array('action' => 'register')), __('Please register')))
			);
			?>
		</p>
		<div id="post-provider-buttons">
			<?php echo $provider_buttons; ?>
		</div>
	<?php endif;?>
	
	<?php if (isset($comment_form)): ?>
		<div class="post-comment-form">
			<?php echo $comment_form; ?>
		</div>
	<?php endif;?>
</div>
