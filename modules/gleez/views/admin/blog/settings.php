<div class="help">
	<p><?php echo __('Blog specific settings, default status, tags, comments etc.'); ?></p>
</div>

<?php include Kohana::find_file('views', 'errors/partial'); ?>

<?php echo Form::open($action, array('class'=>'blog-settings-form form form-horizontal')); ?>

	<div class="control-group <?php echo isset($errors['items_per_page']) ? 'error': ''; ?>">
		<?php echo Form::label('title', __('Blog entries per page'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::select('items_per_page', HTML::per_page(), $config['items_per_page'], array('class' => 'span2')); ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['default_status']) ? 'error': ''; ?>">
		<?php echo Form::label('default_status', __('Default Blog Status'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::select('default_status', Post::status(), isset($config['default_status']) ? $config['default_status'] : NULL, array('class' => 'span2')); ?>
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
			echo Form::hidden('primary_image',     0);
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
				echo Form::label('use_cache',         Form::checkbox('use_cache', TRUE, $use_cache).__('Enable Blog Cache'), array('class' => 'checkbox'));
				echo Form::label('primary_image',     Form::checkbox('primary_image', TRUE, $primary_image).__('Use Primary Image'), array('class' => 'checkbox'));
				echo Form::label('comment_anonymous', Form::checkbox('comment_anonymous', TRUE, $comment_anonymous).__('Allow anonymous commenting (with contact information)'), array('class' => 'checkbox'));
			?>
		</div>
	</div>

	<hr>

	<div class="control-group <?php echo isset($errors['comment']) ? 'error': ''; ?>">
		<?php echo Form::label('comment', __('Allow people to post comments'), array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::label('comment', Form::radio('comment', 0, $comment1).__('Disabled'), array('class' => 'radio')); ?>
			<?php echo Form::label('comment', Form::radio('comment', 1, $comment2).__('Read only'), array('class' => 'radio')); ?>
			<?php echo Form::label('comment', Form::radio('comment', 2, $comment3).__('Read/Write'), array('class' => 'radio')); ?>
			<p class="help-block"><?php echo __('These settings may be overridden for individual posts.'); ?></p>
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
			<?php echo Form::select('comment_order', array('asc'=> __('Older'), 'desc'=>__('Newer')), isset($config['comment_order']) ? $config['comment_order'] : 'asc', array('class' => 'span2')); ?>
			<p class="help-block"><?php echo __('Comments should be displayed with the older/new comments at the top of each blog'); ?></p>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['comments_per_page']) ? 'error': ''; ?>">
		<?php echo Form::label('comments_per_page', __('Comments per page'), array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::select('comments_per_page', HTML::per_page(), isset($config['comments_per_page']) ? $config['comments_per_page'] : 50, array('class' => 'span2'));	?>
		</div>
	</div>

	<?php echo Form::submit('blog_settings', __('Save'), array('class' => 'btn btn-success pull-right')); ?>
	<div class="clearfix"></div><br>
<?php echo Form::close() ?>