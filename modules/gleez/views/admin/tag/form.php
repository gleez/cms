<?php defined('SYSPATH') OR die('No direct script access.') ?>

<?php echo Form::open($action, array('id'=>'tag-form ', 'class'=>'tag-form form form-horizontal well')); ?>
	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="control-group <?php echo isset($errors['tag']) ? 'error': ''; ?>">
		<?php echo Form::label('name', __('Tag: ')) ?>
		<?php echo Form::input('name', $post->name, array('class' => 'text small')); ?>
	</div>
	
	<div class="control-group <?php echo isset($errors['type']) ? 'error': ''; ?>">
		<?php echo Form::label('type', __('Type:'), array('class' => 'aboveconte')) ?>
		<?php echo Form::select('type', Gleez::types(), $post->type, array('class' => 'list small')); ?> 
	</div>
	
	<div class="control-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
		<?php echo Form::label('path', __('Slug: %slug', array('%slug' => $site_url )),
									array('class' => 'nowrap')) ?>
		<?php echo Form::input('path', $path, array('class' => 'text small slug')); ?>
	</div>
	
	<?php echo Form::submit('tag', __('Submit'), array('class' => 'btn btn-primary')) ?>

<?php echo Form::close() ?>
