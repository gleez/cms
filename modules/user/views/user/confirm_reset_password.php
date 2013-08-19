<?php echo Form::open($action); ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
		<?php echo Form::label('pass', __('New password')) ?>
		<?php echo Form::password('pass', NULL, array('class' => 'text medium')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['pass_confirm']) ? 'error': ''; ?>">
		<?php echo Form::label('pass_confirm', __('New password (again)')) ?>
		<?php echo Form::password('pass_confirm', NULL, array('class' => 'text medium')); ?>
	</div>

	<?php echo Form::submit('password_confirm', __('Apply new password'), array('class' => 'btn btn-success')) ?>
<?php echo Form::close() ?>