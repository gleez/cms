<?php echo Form::open($action, array('class' => 'form form-horizontal')); ?>
	<div class="container">
		<div class="span12">
			<div class="control-group <?php echo isset($errors['database']) ? 'error': ''; ?>">
				<?php echo Form::label('database', __('Database Name'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::input('database', $form['database'], array('class' => 'input-xlarge')); ?>
				</div>
			</div>

			<div class="control-group <?php echo isset($errors['user']) ? 'error': ''; ?>">
				<?php echo Form::label('user', __('User'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::input('user', $form['user'], array('class' => 'input-xlarge')); ?>
				</div>
			</div>

			<div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
				<?php echo Form::label('pass', __('Password'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::password('pass', $form['pass'], array('class' => 'input-xlarge')); ?>
				</div>
			</div>

			<div class="control-group <?php echo isset($errors['hostname']) ? 'error': ''; ?>">
				<?php echo Form::label('hostname', __('Host'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::input('hostname', $form['hostname'], array('class' => 'input-xlarge')); ?>
				</div>
			</div>

			<div class="control-group <?php echo isset($errors['table_prefix']) ? 'error': ''; ?>">
				<?php echo Form::label('table_prefix', __('Table Prefix'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::input('table_prefix', $form['table_prefix'], array('class' => 'input-xlarge')); ?>
				</div>
			</div>
		</div>
	</div>

	<?php echo Form::submit('db', __('Next'), array('class' => 'btn btn-primary pull-right')); ?>
	<div class="clearfix"></div><br>
<?php echo Form::close() ?>