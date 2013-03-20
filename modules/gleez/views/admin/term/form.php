<?php defined("SYSPATH") OR die("No direct script access.") ?>

<?php echo Form::open($action, array('id'=>'term-form', 'class'=>'term-form form form-horizontal well')); ?>

<?php include Kohana::find_file('views', 'errors/partial'); ?>

<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
	<?php echo Form::label('name', __('Name'), array('class' => 'control-label')); ?>
	<div class="controls">
		<?php echo Form::input('name', $post->rawname, array('class' => 'input-xlarge')); ?>
	</div>
</div>

<div class="control-group <?php echo isset($errors['parent']) ? 'error': ''; ?>">
	<?php echo Form::label('parent', __('Parent'), array('class' => 'control-label')); ?>
	<div class="controls">
		<?php echo Form::select('parent', $terms, $post->pid, array('class' => 'input-xlarge')); ?>
	</div>
</div>

<div class="control-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
	<?php echo Form::label('path', __('Slug'), array('class' => 'nowrap control-label')) ?>
	<div class="controls">
		<?php echo Form::input('path', $path, array('class' => 'input-xlarge slug')); ?>
		<span class="help-block"><?php echo __('Slug for %slug', array('%slug' => $site_url)); ?></span>
	</div>
</div>

<div class="control-group <?php echo isset($errors['description']) ? 'error': ''; ?>">
	<?php echo Form::label('description', __('Description'), array('class' => 'control-label')); ?>
	<div class="controls">
		<?php echo Form::textarea('description', $post->description, array('class' => 'input-xlarge', 'rows' => 10)) ?>
	</div>
</div>

<?php echo Form::submit('term', __('Save'), array('class' => 'btn btn-primary btn-large')); ?>

<?php echo Form::close() ?>
