<div class="help">
	<p><?php echo __('Enter the path you wish to create the alias for, followed by the name of the new alias.'); ?></p>
</div>

<?php echo Form::open($action, array('id'=>'path-form ', 'class'=>'menu-form form form-horizontal well')); ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="form-group <?php echo isset($errors['source']) ? 'has-error': ''; ?>">
		<?php echo Form::label('source', __('Existing URL Path'), array('class' => 'control-label col-md-4')); ?>
		<div class="controls col-md-5">
			<?php echo Form::input('source', $post->source, array('class' => 'form-control col-md-5')); ?>
			<p class="help-block"><?php echo HTML::anchor($site_url.(isset($post->source) ? $post->source : '')); ?></p>
		</div>
	</div>

	<div class="form-group <?php echo isset($errors['alias']) ? 'has-error': ''; ?>">
		<?php echo Form::label('alias', __('Alias'), array('class' => 'control-label col-md-4')); ?>
		<div class="controls col-md-5">
			<?php echo Form::input('alias', $post->alias, array('class' => 'form-control col-md-5')); ?>
			<p class="help-block"><?php echo HTML::anchor($site_url.(isset($post->alias) ? $post->alias : '')); ?></p>
		</div>
	</div>

	<?php echo Form::button('add_path', __('Save'), array('class' => 'btn btn-success pull-right', 'type' => 'submit')); ?>
	<div class="clearfix"></div><br>

<?php echo Form::close() ?>