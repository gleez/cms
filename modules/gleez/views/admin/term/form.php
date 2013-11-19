<?php echo Form::open($action, array('id'=>'term-form', 'class'=>'term-form form form-horizontal well clearfix')); ?>

<?php include Kohana::find_file('views', 'errors/partial'); ?>

<div class="form-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
	<?php echo Form::label('name', __('Name'), array('class' => 'control-label col-md-3')); ?>
	<div class="controls col-md-5">
		<?php echo Form::input('name', $post->rawname, array('class' => 'form-control')); ?>
	</div>
</div>

<div class="form-group <?php echo isset($errors['parent']) ? 'error': ''; ?>">
	<?php echo Form::label('parent', __('Parent'), array('class' => 'control-label col-md-3')); ?>
	<div class="controls col-md-5">
		<?php echo Form::select('parent', $terms, $post->pid, array('class' => 'form-control')); ?>
	</div>
</div>

<div class="form-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
	<?php echo Form::label('path', __('Slug'), array('class' => 'nowrap control-label col-md-3')) ?>
	<div class="controls col-md-5">
		<?php echo Form::input('path', $path, array('class' => 'form-control slug')); ?>
		<span class="help-block"><?php echo __('Slug for %slug', array('%slug' => $site_url)); ?></span>
	</div>
</div>

<div class="form-group <?php echo isset($errors['description']) ? 'error': ''; ?>">
	<?php echo Form::label('description', __('Description'), array('class' => 'control-label col-md-3')); ?>
	<div class="controls col-md-5">
		<?php echo Form::textarea('description', $post->description, array('class' => 'form-control', 'rows' => 5)) ?>
	</div>
</div>

<?php echo Form::submit('term', __('Save'), array('class' => 'btn btn-success pull-right')); ?>

<?php echo Form::close() ?>
