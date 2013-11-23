<div class="account-container">
	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="content clearfix">
		<?php echo Form::open($action, array('class' => 'row-fluid form-password')) ?>
			<h1><?php echo __('Change Password') ?></h1>

			<div class="login-fields">
				<div class="field">
					<div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
						<?php echo Form::label('pass', __('New password'), array('class' => 'control-label')); ?>
						<div class="controls">
							<div class="input-prepend">
								<span class="add-on"><i class="fa fa-key"></i></span>
								<?php echo Form::password('pass', NULL); ?>
							</div>
						</div>
					</div>

					<div class="control-group <?php echo isset($errors['pass_confirm']) ? 'error': ''; ?>">
						<?php echo Form::label('pass_confirm', __('New password (again)'), array('class' => 'control-label')); ?>
						<div class="controls">
							<div class="input-prepend">
								<span class="add-on"><i class="fa fa-key"></i></span>
								<?php echo Form::password('pass_confirm', NULL); ?>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<?php echo Form::submit('password_confirm', __('Apply new password'), array('class' => 'btn btn-inverse pull-right')); ?>
			</div>
		<?php echo Form::close(); ?>
	</div>
</div>
