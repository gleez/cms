<div class="help">
	<p>
		<?php _e('If you want more information about the %sitename or if you have comments about this website please use the contact form below. If you message is about a specific page on the %site_url website please include the URL in your message for reference.',
			array(
				'%sitename' => $site_name,
				'%site_url' => $config->get('site_url', URL::site(NULL, TRUE ))
			));
		?>
	</p>
</div>

<?php echo Form::open($action, array('id'=>'contact-form', 'class'=>'contact-form form form-horizontal')); ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="row-fluid">
		<div class="span12">
			<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
				<?php echo Form::label('name', __('Your Name'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::input('name', $user->nick, array('class' => 'input-xlarge')); ?>
				</div>
			</div>
			<div class="control-group <?php echo isset($errors['email']) ? 'error': ''; ?>">
				<?php echo Form::label('email', __('Reply-to'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::input('email', $user->mail, array('class' => 'input-xlarge')); ?>
				</div>
			</div>
			<div class="control-group <?php echo isset($errors['subject']) ? 'error': ''; ?>">
				<?php echo Form::label('subject', __('Subject'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::input('subject', '', array('class' => 'input-xlarge')); ?>
					<p class="help-block"><?php echo __('Maximum of :num characters.', array(':num' => $config->subject_length)) ?></p>
				</div>
			</div>
			<div class="control-group <?php echo isset($errors['category']) ? 'error': ''; ?>">
				<?php echo Form::label('category', __('Category'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::select('category', $types, $post['category'], array('class' => 'input-xlarge')); ?>
				</div>
			</div>
			<div class="control-group <?php echo isset($errors['body']) ? 'error': ''; ?>">
				<?php echo Form::label('body', __('Body'), array('class' => 'control-label') ) ?>
				<div class="controls">
					<?php echo Form::textarea('body', '', array('class' => 'textarea span12', 'autofocus')) ?>
					<p class="help-block"><?php echo __('Maximum of :num characters.', array(':num' => $config->body_length)) ?></p>
				</div>
			</div>
			<hr>
			<?php if (isset($captcha)  AND ! $captcha->promoted()): ?>
				<div class="control-group <?php echo isset($errors['captcha']) ? 'error': ''; ?>">
					<?php echo Form::label('_captcha', __('Security'), array('class' => 'control-label')) ?>
					<div class="controls">
						<?php echo Form::input('_captcha', '', array('class' => 'text tiny')); ?><br>
						<?php echo $captcha; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php echo Form::submit('contact', __('Send message'), array('class' => 'btn pull-right')); ?>
	<div class="clearfix"></div><br>

<?php echo Form::close() ?>