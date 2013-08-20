<?php

	$use_captcha       = (isset($post['use_captcha']) AND $post['use_captcha'] == 1) ? TRUE : FALSE;
	$use_authors       = (isset($post['use_authors']) AND $post['use_authors'] == 1) ? TRUE : FALSE;
	$use_comment       = (isset($post['use_comment']) AND $post['use_comment'] == 1) ? TRUE : FALSE;
	$use_category      = (isset($post['use_category']) AND $post['use_category'] == 1) ? TRUE : FALSE;
	$use_excerpt       = (isset($post['use_excerpt']) AND $post['use_excerpt'] == 1) ? TRUE : FALSE;
	$use_tags          = (isset($post['use_tags'])    AND $post['use_tags'] == 1) ? TRUE : FALSE;
	$use_submitted     = (isset($post['use_submitted']) AND $post['use_submitted'] == 1) ? TRUE : FALSE;
	$comment_anonymous = (isset($post['comment_anonymous']) AND $post['comment_anonymous'] == 1) ? TRUE : FALSE;
	$use_cache         = (isset($post['use_cache']) AND $post['use_cache'] == 1) ? TRUE : FALSE;

	$comment1 = (isset($post['comment']) && $post['comment'] == 0) ? TRUE : FALSE;
	$comment2 = (isset($post['comment']) && $post['comment'] == 1) ? TRUE : FALSE;
	$comment3 = (isset($post['comment']) && $post['comment'] == 2) ? TRUE : FALSE;

	$mode1 = (isset($post['comment_default_mode']) && $post['comment_default_mode'] == 1) ? TRUE : FALSE;
	$mode2 = (isset($post['comment_default_mode']) && $post['comment_default_mode'] == 2) ? TRUE : FALSE;
	$mode3 = (isset($post['comment_default_mode']) && $post['comment_default_mode'] == 3) ? TRUE : FALSE;
	$mode4 = (isset($post['comment_default_mode']) && $post['comment_default_mode'] == 4) ? TRUE : FALSE;
?>

<div class="help">
	<p><?php echo __('Page specific settings, default status, tags, comments etc.'); ?></p>
</div>

<?php include Kohana::find_file('views', 'errors/partial'); ?>

<?php echo Form::open($action, array('class'=>'page-settings-form form form-horizontal')) ?>

	<div class="control-group <?php echo isset($errors['items_per_page']) ? 'error': ''; ?>">
		<?php echo Form::label('title', __('Page entries per page'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::select('items_per_page', HTML::per_page(), $post['items_per_page'], array('class' => 'span2')); ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['default_status']) ? 'error': ''; ?>">
		<?php echo Form::label('default_status', __('Default Page Status'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::select('default_status', Post::status(), isset($post['default_status']) ? $post['default_status'] : NULL, array('class' => 'span2')); ?>
		</div>
	</div>

	<div class="control-group">
		<?php
			// @important the hidden filed should be before checkbox
			echo Form::hidden('use_excerpt',       0);
			echo Form::hidden('use_comment',       0);
			echo Form::hidden('use_authors',       0);
			echo Form::hidden('use_captcha',       0);
			echo Form::hidden('use_category',      0);
			echo Form::hidden('use_tags',          0);
			echo Form::hidden('use_submitted',     0);
			echo Form::hidden('use_cache',         0);
			echo Form::hidden('comment_anonymous', 0);
		?>
		<div class="controls">
			<?php
				echo Form::label('use_excerpt',       Form::checkbox('use_excerpt', TRUE, $use_excerpt).__('Enable excerpt'), array('class' => 'checkbox'));
				echo Form::label('use_comment',       Form::checkbox('use_comment', TRUE, $use_comment).__('Enable comments'), array('class' => 'checkbox'));
				echo Form::label('use_authors',       Form::checkbox('use_authors', TRUE, $use_authors).__('Enable authors'), array('class' => 'checkbox'));
				echo Form::label('use_captcha',       Form::checkbox('use_captcha', TRUE, $use_captcha).__('Enable captcha'), array('class' => 'checkbox'));
				echo Form::label('use_category',      Form::checkbox('use_category', TRUE, $use_category).__('Enable Category'), array('class' => 'checkbox'));
				echo Form::label('use_tags',          Form::checkbox('use_tags', TRUE, $use_tags).__('Enable tag cloud'), array('class' => 'checkbox'));
				echo Form::label('use_submitted',     Form::checkbox('use_submitted', TRUE, $use_submitted).__('Show Submitted Info'), array('class' => 'checkbox'));
				echo Form::label('use_cache',         Form::checkbox('use_cache', TRUE, $use_cache).__('Enable Page Cache'), array('class' => 'checkbox'));
				echo Form::label('comment_anonymous', Form::checkbox('comment_anonymous', TRUE, $comment_anonymous).__('Allow anonymous commenting (with contact information)'), array('class' => 'checkbox'));
			?>
		</div>
	</div>

	<hr>

	<div class="control-group <?php echo isset($errors['comment']) ? 'error': ''; ?>">
		<?php echo Form::label('comment', __('Allow people to post comments'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::label('comment', Form::radio('comment', 0, $comment1).__('Disabled'), array('class' => 'radio')) ?>
			<?php echo Form::label('comment', Form::radio('comment', 1, $comment2).__('Read only'), array('class' => 'radio')) ?>
			<?php echo Form::label('comment', Form::radio('comment', 2, $comment3).__('Read/Write'), array('class' => 'radio')) ?>
			<p class="help-block"><?php echo __('These settings may be overridden for individual posts.') ?></p>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['comment_default_mode']) ? 'error': ''; ?>">
		<?php echo Form::label('comment_default_mode', __('Comment display mode'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::label('comment_default_mode', Form::radio('comment_default_mode', 1, $mode1).__('Flat list &mdash; collapsed'), array('class' => 'radio')) ?>
			<?php echo Form::label('comment_default_mode', Form::radio('comment_default_mode', 2, $mode2).__('Flat list &mdash; expanded'), array('class' => 'radio')) ?>
			<?php echo Form::label('comment_default_mode', Form::radio('comment_default_mode', 3, $mode3).__('Threaded list &mdash; collapsed'), array('class' => 'radio')) ?>
			<?php echo Form::label('comment_default_mode', Form::radio('comment_default_mode', 4, $mode4).__('Threaded list &mdash; expanded'), array('class' => 'radio')) ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['comment_order']) ? 'error': ''; ?>">
		<?php echo Form::label('comment_order', __('Comment Order'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::select('comment_order', array('asc'=> __('Older'), 'desc'=>__('Newer')), isset($post['comment_order']) ? $post['comment_order'] : 'asc', array('class' => 'span2')); ?>
			<p class="help-block"><?php echo __('Comments should be displayed with the older/new comments at the top of each page'); ?></p>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['comments_per_page']) ? 'error': ''; ?>">
		<?php echo Form::label('comments_per_page', __('Comments per page'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::select('comments_per_page', HTML::per_page(), isset($post['comments_per_page']) ? $post['comments_per_page'] : 50, array('class' => 'span2')); ?>
		</div>
	</div>

	<?php echo Form::submit('page_settings', __('Save'), array('class' => 'btn btn-success pull-right')) ?>
	<div class="clearfix"></div><br>
<?php echo Form::close() ?>
