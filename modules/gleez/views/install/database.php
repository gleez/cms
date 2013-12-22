<?php echo Form::open($action, array('class' => 'form form-horizontal')); ?>
	<div class="container1">
			<div class="form-group <?php echo isset($errors['database']) ? 'error': ''; ?>">
				<?php echo Form::label('database', __('Database Name'), array('class' => 'col-sm-3 control-label')) ?>
				<div class="col-sm-6">
					<?php echo Form::input('database', $form['database'], array('class' => 'form-control')); ?>
				</div>
			</div>

			<div class="form-group <?php echo isset($errors['user']) ? 'error': ''; ?>">
				<?php echo Form::label('user', __('User'), array('class' => 'col-sm-3 control-label')) ?>
				<div class="col-sm-6">
					<?php echo Form::input('user', $form['user'], array('class' => 'form-control')); ?>
				</div>
			</div>

			<div class="form-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
				<?php echo Form::label('pass', __('Password'), array('class' => 'col-sm-3 control-label')) ?>
				<div class="col-sm-6">
					<?php echo Form::password('pass', $form['pass'], array('class' => 'form-control')); ?>
				</div>
			</div>

			<div class="form-group <?php echo isset($errors['hostname']) ? 'error': ''; ?>">
				<?php echo Form::label('hostname', __('Host'), array('class' => 'col-sm-3 control-label')) ?>
				<div class="col-sm-6">
					<?php echo Form::input('hostname', $form['hostname'], array('class' => 'form-control')); ?>
				</div>
			</div>

			<div class="form-group <?php echo isset($errors['table_prefix']) ? 'error': ''; ?>">
				<?php echo Form::label('table_prefix', __('Table Prefix'), array('class' => 'col-sm-3 control-label')) ?>
				<div class="col-sm-6">
					<?php echo Form::input('table_prefix', $form['table_prefix'], array('class' => 'form-control')); ?>
				</div>
			</div>
	</div>

	<?php echo Form::submit('db', __('Next'), array('class' => 'btn btn-primary pull-right')); ?>
	<div class="clearfix"></div><br>
<?php echo Form::close() ?>
