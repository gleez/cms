<?php include Kohana::find_file('views', 'errors/partial'); ?>

<?php echo Form::open(Route::get('user')->uri($params).URL::query(array('destination' => Request::initial()->uri($destination))), array('class' => 'form-horizontal')); ?>

<div class="control-group <?php echo isset($errors['_external']['old_pass']) ? 'error': ''; ?>">
	<?php echo Form::label('old_pass', __('Current password'), array('class' => 'control-label')) ?>
	<div class="controls">
		<?php echo Form::password('old_pass', NULL, array('class' => 'span4')); ?>
	</div>
</div>

<div class="control-group <?php echo isset($errors['_external']['pass']) ? 'error': ''; ?>">
	<?php echo Form::label('pass', __('New password'), array('class' => 'control-label')) ?>
	<div class="controls">
		<?php echo Form::password('pass', NULL, array('class' => 'span4')); ?>
		<span class="help-block"><?php echo __('Minimum password length &mdash; :count characters', array(':count' => 4)) ?></span>
	</div>
</div>

<div class="control-group <?php echo isset($errors['_external']['pass_confirm']) ? 'error': ''; ?>">
	<?php echo Form::label('pass_confirm', __('New password (again)'), array('class' => 'control-label')) ?>
	<div class="controls">
		<?php echo Form::password('pass_confirm', NULL, array('class' => 'span4')); ?>
		<span class="help-block"><?php echo __('Confirm new password') ?></span>
	</div>
</div>

<div class="form-actions">
	<?php echo Form::submit('change_pass', __('Save'), array('class' => 'btn btn-success pull-right')); ?>
</div>
<?php echo Form::close() ?>
