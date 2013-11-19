<div class="help">
	<p><?php echo __('Add and edit your tags using the form below.'); ?></p>
</div>

<?php echo Form::open($action, array('id'=>'tag-form ', 'class'=>'tag-form form form-horizontal well')); ?>
	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="form-group <?php echo isset($errors['tag']) ? 'error': ''; ?>">
		<?php echo Form::label('name', __('Tag'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-5">
			<?php echo Form::input('name', $post->name, array('class' => 'form-control')); ?>
		</div>
	</div>

	<div class="form-group <?php echo isset($errors['type']) ? 'error': ''; ?>">
		<?php echo Form::label('type', __('Type'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-5">
			<?php echo Form::select('type', Gleez::types(), $post->type, array('class' => 'form-control')); ?>
		</div>
	</div>

	<div class="form-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
		<?php echo Form::label('path', __('Slug'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-5">
			<?php echo Form::input('path', $path, array('class' => 'form-control slug')); ?>
			<p class="help-block"><?php echo HTML::anchor($site_url.$path, $site_url.$path); ?></p>
		</div>
	</div>

	<?php echo Form::submit('tag', __('Save'), array('class' => 'btn btn-success pull-right')); ?>
	<div class="clearfix"></div><br>

<?php echo Form::close() ?>
