<div class="help">
	<p>
		<?php _e('If you want more information about the %sitename or if you have comments about this website please use the contact form below. If you message is about a specific page on the %site_url website please include the URL in your message for reference.',
			array('%sitename' => $site_name, '%site_url' => $site_url));
		?>
	</p>
</div>

<?php echo Form::open($action, array('id' => 'contact-form', 'class'=>'form-horizontal', 'role' => 'form')) ?>
	<div class="col-sm-12">
		<div class="form-group <?php echo isset($errors['name']) ? 'has-error': ''; ?>">
			<?php echo Form::label('name', __('Your Name'), array('class' => 'col-sm-3 control-label')) ?>
			<div class="col-sm-9">
				<div class="row">
					<div class="input-group col-sm-8">
						<?php echo Form::input('name', $user->nick, array('class' => 'form-control')); ?>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['email']) ? 'has-error': ''; ?>">
			<?php echo Form::label('email', __('Reply-to'), array('class' => 'col-sm-3 control-label')) ?>
			<div class="col-sm-9">
				<div class="row">
					<div class="input-group col-sm-8">
						<?php echo Form::input('email', $user->mail, array('class' => 'form-control')) ?>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['category']) ? 'has-error': ''; ?>">
			<?php echo Form::label('category', __('Category'), array('class' => 'col-sm-3 control-label')) ?>
			<div class="col-sm-9">
				<div class="row">
					<div class="input-group col-sm-8">
						<?php echo Form::select('category', $types, $post['category'], array('class' => 'form-control')) ?>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['subject']) ? 'has-error': ''; ?>">
			<?php echo Form::label('subject', __('Subject'), array('class' => 'col-sm-3 control-label')) ?>
			<div class="col-sm-9">
				<div class="row">
					<div class="input-group col-sm-12">
						<?php echo Form::input('subject', '', array('class' => 'form-control', 'autofocus')) ?>
						<p class="help-block">
							<?php echo __('Maximum of :num characters.', array(':num' => '<span id="subject_length">'.$config->subject_length.'</span>')) ?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['body']) ? 'has-error': ''; ?>">
			<?php echo Form::label('body', __('Body'), array('class' => 'col-sm-3 control-label')) ?>
			<div class="col-sm-9">
				<div class="row">
					<div class="input-group col-sm-12">
						<?php echo Form::textarea('body', '', array('class' => 'form-control', 'rows' => 6)) ?>
						<p class="help-block">
							<?php echo __('Maximum of :num characters.', array(':num' => '<span id="body_length">'.$config->body_length.'</span>')) ?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<hr>
		<?php if (isset($captcha)  AND ! $captcha->promoted()): ?>
			<div class="form-group <?php echo isset($errors['captcha']) ? 'has-error': ''; ?>">
				<?php echo Form::label('_captcha', __('Security'), array('class' => 'col-sm-3 control-label')) ?>
				<div class="col-sm-9">
					<div class="row">
						<div class="input-group col-sm-4">
							<?php echo Form::input('_captcha', '', array('class' => 'form-control')) ?><br>
							<?php echo $captcha; ?>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</div>

	<div class="form-group ab-wrapper">
		<div class="col-sm-12">
			<?php echo Form::button('contact', __('Send message'), array('class' => 'btn btn-success pull-right', 'type' => 'submit'))?>
		</div>
	</div>

<?php echo Form::close() ?>
