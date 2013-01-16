<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php echo Form::open(Route::get('install')->uri(array('action' => 'database')), array('class' => 'form-horizontal')); ?>
<div class="box">
	<div class="inside">
		<div class="control-group <?php echo isset($errors['database']) ? 'error': ''; ?>">
			<?php echo Form::label('database', 'Database Name :', array('class' => 'control-label')) ?>
			<?php echo Form::input('database', $form['database'], array('class' => 'input-large')); ?>
		</div>
	
		<div class="control-group <?php echo isset($errors['user']) ? 'error': ''; ?>">
			<?php echo Form::label('user', 'User :', array('class' => 'control-label')) ?>
			<?php echo Form::input('user', $form['user'], array('class' => 'input-large')); ?>
		</div>

		<div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
			<?php echo Form::label('pass', 'Password :', array('class' => 'control-label')) ?>
			<?php echo Form::password('pass', $form['pass'], array('class' => 'input-large')); ?>
		</div>
	
		<div class="control-group <?php echo isset($errors['hostname']) ? 'error': ''; ?>">
			<?php echo Form::label('hostname', 'Host :', array('class' => 'control-label')) ?>
			<?php echo Form::input('hostname', $form['hostname'], array('class' => 'input-large')); ?>
		</div>
	
		<div class="control-group <?php echo isset($errors['table_prefix']) ? 'error': ''; ?>">
			<?php echo Form::label('table_prefix', 'Table Prefix :', array('class' => 'control-label')) ?>
			<?php echo Form::input('table_prefix', $form['table_prefix'], array('class' => 'input-large')); ?>
		</div>
	</div>
</div>

<p><?php echo Form::submit('db', 'Next', array('class' => 'btn btn-primary btn-large')) ?></p>
<?php echo Form::close() ?>