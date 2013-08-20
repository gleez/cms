<div class="help">
	<?php _e('Add new vocabulary to your site, edit and reorganize existing vocabularies.') ?>
</div>

<?php $params = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
	echo Form::open(Route::get('admin/taxonomy')->uri($params), array('id'=>'vocab-form', 'class'=>'vocab-form form form-horizontal well clearfix')) ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
		<?php echo Form::label('name', __('Name'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::input('name', $post->rawname, array('class' => 'input-xlarge')); ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['type']) ? 'error': ''; ?>">
		<?php echo Form::label('type', __('Type'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::select('type', Gleez::types(), $post->type, array('class' => 'input-xlarge')); ?>
		</div>
	</div>
	
	<div class="control-group <?php echo isset($errors['description']) ? 'error': ''; ?>">
		<?php echo Form::label('description', __('Description'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::textarea('description', $post->description, array('class' => 'input-xlarge', 'rows' => 5)) ?>
		</div>
	</div>

	<?php echo Form::submit('vocab', __('Save'), array('class' => 'btn btn-success pull-right')) ?>
<?php echo Form::close() ?>