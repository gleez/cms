<?php echo Form::open($action.URL::query($destination), array('id'=>'comment-form', 'class'=>'comment-form form')) ?>

<?php include Kohana::find_file('views', 'errors/partial'); ?>

<div class="row">
	<?php if (ACL::check('administer comment') AND $is_edit): ?>
		<div id="side-info-column" class="col-md-3 col-md-push-9">
			<div id="submitdiv" class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo __('Status'); ?></h3>
				</div>

				<div class="panel-body" id="submitpost">
					<div id="minor-publishing">
						<div class="form-group <?php echo isset($errors['status']) ? 'has-error': ''; ?>">
							<?php echo Form::label('status', __('Change Status'), array('class' => 'control-label')); ?>
							<?php echo Form::select('status', Comment::status(), $post->status, array('class' => 'form-control')); ?>
						</div>
						<div class="form-group <?php echo isset($errors['author_name']) ? 'has-error': ''; ?>">
							<?php echo Form::label('author_name', __('Author'), array('class' => 'control-label')); ?>
							<?php echo Form::input('author_name', $post->user->name,array('class' => 'form-control'), 'autocomplete/user'); ?>
						</div>

						<div class="form-group <?php echo isset($errors['author_date']) ? 'has-error': ''; ?>">
							<?php echo Form::label('author_date', __('Date'), array('class' => 'control-label') ); ?>
							<?php echo Form::date('author_date', $post->created, array('class' => 'form-control')); ?>
						</div>
					</div>
				</div>

				<div class="panel-footer">
					<div id="major-publishing-actions" class="row">
						<?php if ($post->loaded()): ?>
							<div id="delete-action" class="btn btn-default pull-left">
								<i class="fa fa-trash-o"></i>
								<?php echo HTML::anchor($post->delete_url.URL::query($destination), __('Move to Trash'), array('class' => 'submitdelete')) ?>
							</div>
						<?php endif; ?>

						<div id="publishing-action">
							<?php echo Form::submit('comment', __('Save'), array('class' => 'btn btn-success pull-right')) ?>
						</div>
					</div>
				</div>

			</div>
		</div>
	<?php endif; ?>

	<div id="comment-body" class="<?php echo ( ! ACL::check('administer comment') OR ! $is_edit) ? 'col-md-12' : 'col-md-9 col-md-pull-3'; ?>">

		<?php if ($auth->logged_in() == FALSE OR ($is_edit AND $post->author == 1)): ?>
			<div id="submitdiv" class="stuffbox">
				<h3 class='hndle'><?php echo __('Author') ?></h3>

				<div class="inside">
					<div class="form-group <?php echo isset($errors['guest_name']) ? 'error': ''; ?>">
						<?php echo Form::label('guest_name', __('Your Name'), array('class' => 'control-label nowrap') ) ?>
						<?php echo Form::input('guest_name', $post->guest_name, array('class' => 'form-control')); ?>
					</div>

					<div class="form-group <?php echo isset($errors['guest_email']) ? 'error': ''; ?>">
						<?php echo Form::label('guest_email', __('Email'), array('class' => 'control-label nowrap') ) ?>
						<?php echo Form::input('guest_email', $post->guest_email, array('class' => 'form-control')); ?>
					</div>

					<div class="form-group <?php echo isset($errors['guest_url']) ? 'error': ''; ?>">
						<?php echo Form::label('guest_url', __('Website'), array('class' => 'control-label nowrap') ) ?>
						<?php echo Form::input('guest_url', $post->guest_url, array('class' => 'form-control')); ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<div id="postdiv" class="postarea <?php echo isset($errors['body']) ? 'error': ''; ?>">
			<?php if ( ! ACL::check('administer comment') OR ! $is_edit) : ?>
				<h3 class='hndle'><?php echo __('Leave a Comment') ?></h3>
			<?php endif; ?>

			<div class="form-group <?php echo isset($errors['body']) ? 'error': ''; ?>">
				<?php echo Form::hidden('comment_post_id', $item->id); ?>
				<?php echo Form::hidden('comment_post_type', $item->type); ?>
				<?php echo Form::textarea('body', $post->rawbody, array('class' => 'form-control textarea', 'rows' => 7)); ?>
			</div>

		</div>

		<?php if ($use_captcha  AND ! $captcha->promoted()) : ?>
			<div class="form-group <?php echo isset($errors['captcha']) ? 'error': ''; ?>">
				<?php echo Form::label('_captcha', __('Security'), array('class' => 'form-control nowrap')) ?>
				<?php echo Form::input('_captcha', '', array('class' => 'text form-control')); ?>
				<?php echo $captcha; ?>
			</div>
		<?php endif; ?>
	</div>

</div>

	<?php if ( ! ACL::check('administer comment') OR ! $is_edit) : ?>
		<div class="form-actions">
			<?php echo Form::submit('comment', __('Post Comment'), array('class' => 'btn btn-default bth-lg')); ?>
		</div>
	<?php endif; ?>

<?php echo Form::close() ?>
