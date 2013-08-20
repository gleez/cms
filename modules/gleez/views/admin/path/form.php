<div class="help">
	<p><?php echo __('Enter the path you wish to create the alias for, followed by the name of the new alias.'); ?></p>
</div>

<?php echo Form::open($action, array('id'=>'path-form ', 'class'=>'menu-form form form-horizontal well')); ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="control-group <?php echo isset($errors['source']) ? 'error': ''; ?>">
		<?php echo Form::label('source', __('Existing URL Path'), array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::input('source', $post->source, array('class' => 'input-xxlarge')); ?>
			<p class="help-block"><?php echo HTML::anchor($site_url.(isset($post->source) ? $post->source : '')); ?></p>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['alias']) ? 'error': ''; ?>">
		<?php echo Form::label('alias', __('Alias'), array('class' => 'control-label')); ?>
		<div class="controls">
			<?php echo Form::input('alias', $post->alias, array('class' => 'input-xxlarge')); ?>
			<p class="help-block"><?php echo HTML::anchor($site_url.(isset($post->alias) ? $post->alias : '')); ?></p>
		</div>
	</div>

	<?php echo Form::button('add_path', __('Save'), array('class' => 'btn btn-success pull-right', 'type' => 'submit')); ?>
	<div class="clearfix"></div><br>

<?php echo Form::close() ?>