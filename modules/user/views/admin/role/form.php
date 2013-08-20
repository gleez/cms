<?php echo Form::open($action, array('id'=>'role-form', 'class'=>'role-form form form-horizontal well')); ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
		<?php echo Form::label('name', __('Name'), array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::input('name', $post->name, array('class' => 'input-xlarge')); ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['description']) ? 'error': ''; ?>">
		<?php echo Form::label('description', __('Description'), array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::input('description', $post->description, array('class' => 'input-xlarge')); ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['special']) ? 'error': ''; ?>">
		<?php echo Form::label('special', __('Special Role'), array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::select('special', array(0 => __('No'), 1 => __('Yes')), $post->special, array('class' => 'input-small')); ?>
		</div>
	</div>

	<?php echo Form::submit('role', __('Save'), array('class' => 'btn btn-success pull-right')) ?>
	<div class="clearfix"></div><br>

<?php echo Form::close(); ?>