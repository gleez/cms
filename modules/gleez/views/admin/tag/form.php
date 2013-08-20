<div class="help">
	<p><?php echo __('Add and edit your tags using the form below.'); ?></p>
</div>

<?php echo Form::open($action, array('id'=>'tag-form ', 'class'=>'tag-form form form-horizontal well')); ?>
	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="control-group <?php echo isset($errors['tag']) ? 'error': ''; ?>">
		<?php echo Form::label('name', __('Tag'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::input('name', $post->name, array('class' => 'input-large')); ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['type']) ? 'error': ''; ?>">
		<?php echo Form::label('type', __('Type'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::select('type', Gleez::types(), $post->type, array('class' => 'input-large')); ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
		<?php echo Form::label('path', __('Slug'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::input('path', $path, array('class' => 'input-large slug')); ?>
			<p class="help-block"><?php echo HTML::anchor($site_url.$path, $site_url.$path); ?></p>
		</div>
	</div>

	<?php echo Form::submit('tag', __('Save'), array('class' => 'btn btn-success pull-right')); ?>
	<div class="clearfix"></div><br>

<?php echo Form::close() ?>
