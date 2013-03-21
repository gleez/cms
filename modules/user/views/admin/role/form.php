<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<?php $parms = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add'); ?>

<?php echo Form::open(Route::get('admin/role')->uri($parms), array('id'=>'role-form', 'class'=>'role-form form form-horizontal')); ?>

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
		<?php echo Form::label('special', __('Special'), array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::select('special', array(0 => __('No'), 1 => __('Yes')), $post->special, array('class' => 'input-xlarge')); ?>
		</div>
	</div>

	<?php echo Form::submit('role', __('Save'), array('class' => 'btn btn-primary')) ?>

<?php echo Form::close(); ?>