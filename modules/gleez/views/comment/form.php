<?php echo Form::open($action.URL::query($destination), array('id'=>'comment-form', 'class'=>'comment-form form')) ?>

<?php include Kohana::find_file('views', 'errors/partial'); ?>

<?php if (ACL::check('administer comment') AND $is_edit): ?>
	<div id="side-info-column" class="inner-sidebar">
		<div id="submitdiv" class="stuffbox">
			<h3 class='hndle'><?php echo __('Status') ?></h3>

			<div class='inside' id="submitpost">
				<div id="minor-publishing">
					<div class="control-group <?php echo isset($errors['status']) ? 'error': ''; ?>">
						<?php echo Form::label('status', __('Change Status'), array('class' => 'abovecontent')) ?>
						<?php echo Form::select('status', Comment::status(), $post->status, array('class' => 'list large')); ?>
					</div>

					<div class="control-group <?php echo isset($errors['author_name']) ? 'error': ''; ?>">
						<?php echo Form::label('author_name', __('Author'), array('class' => 'above')) ?>
						<?php echo Form::input('author_name', $post->user->name,array('class' => 'text large'), 'autocomplete/user'); ?>
					</div>

					<div class="control-group <?php echo isset($errors['author_date']) ? 'error': ''; ?>">
						<?php echo Form::label('author_date', __('Date'), array('class' => 'abovecontent') ) ?>
						<?php echo Form::input('author_date', Date::formatted_time($post->created), array('class' => 'text large')); ?>
					</div>
				</div>

				<div id="major-publishing-actions">
					<?php if ($post->loaded()): ?>
						<div id="delete-action">
							<?php echo HTML::anchor($post->delete_url.URL::query($destination), __('Move to Trash'), array('class' => 'submitdelete deletion')) ?>
						</div>
					<?php endif; ?>

					<div id="publishing-action">
						<?php echo Form::submit('comment', __('Save'), array('class' => 'btn')) ?>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<div id="post-body">

	<?php if ($auth->logged_in() == FALSE OR ($is_edit AND $post->author == 1)): ?>
		<div id="submitdiv" class="stuffbox">
			<h3 class='hndle'><?php echo __('Author') ?></h3>

			<div class="inside">
				<div class="control-group <?php echo isset($errors['guest_name']) ? 'error': ''; ?>">
					<?php echo Form::label('guest_name', __('Your Name'), array('class' => 'nowrap') ) ?>
					<?php echo Form::input('guest_name', $post->guest_name, array('class' => 'text medium')); ?>
				</div>

				<div class="control-group <?php echo isset($errors['guest_email']) ? 'error': ''; ?>">
					<?php echo Form::label('guest_email', __('Email'), array('class' => 'nowrap') ) ?>
					<?php echo Form::input('guest_email', $post->guest_email, array('class' => 'text medium')); ?>
				</div>

				<div class="control-group <?php echo isset($errors['guest_url']) ? 'error': ''; ?>">
					<?php echo Form::label('guest_url', __('Website'), array('class' => 'nowrap') ) ?>
					<?php echo Form::input('guest_url', $post->guest_url, array('class' => 'text medium')); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<div id="postdiv" class="postarea <?php echo isset($errors['body']) ? 'error': ''; ?>">
		<h3 class='hndle'><?php echo __('Leave a Comment') ?></h3>

		<div class="inside">
			<div class="control-group <?php echo isset($errors['body']) ? 'error': ''; ?>">
				<?php echo Form::hidden('comment_post_id', $item->id); ?>
				<?php echo Form::hidden('comment_post_type', $item->type); ?>
				<?php echo Form::textarea('body', $post->rawbody, array('class' => 'textarea full', 'rows' => 7)); ?>
			</div>
		</div>

	</div>

</div>

<div class="clearfix"></div>

<?php if ($use_captcha  AND ! $captcha->promoted()) : ?>
	<div class="control-group <?php echo isset($errors['captcha']) ? 'error': ''; ?>">
		<?php echo Form::label('_captcha', __('Security'), array('class' => 'nowrap')) ?>
		<?php echo Form::input('_captcha', '', array('class' => 'text tiny align')); ?>
		<?php echo $captcha; ?>
	</div>
<?php endif; ?>

<?php if ( ! ACL::check('administer comment') OR ! $is_edit) : ?>
	<br>
	<?php echo Form::submit('comment', __('Post Comment'), array('class' => 'btn')) ?>
<?php endif; ?>

<?php echo Form::close() ?>
