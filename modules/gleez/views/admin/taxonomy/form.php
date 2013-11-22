<div class="help">
	<?php _e('Add new vocabulary to your site, edit and reorganize existing vocabularies.') ?>
</div>

<?php $params = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
	echo Form::open(Route::get('admin/taxonomy')->uri($params), array('id'=>'vocab-form', 'class'=>'vocab-form form form-horizontal well clearfix')) ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="form-group <?php echo isset($errors['name']) ? 'has-error': ''; ?>">
		<?php echo Form::label('name', __('Name'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-5">
			<?php echo Form::input('name', $post->rawname, array('class' => 'form-control')); ?>
		</div>
	</div>

	<div class="form-group <?php echo isset($errors['type']) ? 'has-error': ''; ?>">
		<?php echo Form::label('type', __('Type'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-5">
			<?php echo Form::select('type', Gleez::types(), $post->type, array('class' => 'form-control')); ?>
		</div>
	</div>
	
	<div class="form-group <?php echo isset($errors['description']) ? 'has-error': ''; ?>">
		<?php echo Form::label('description', __('Description'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-5">
			<?php echo Form::textarea('description', $post->description, array('class' => 'form-control', 'rows' => 5)) ?>
		</div>
	</div>

	<?php echo Form::submit('vocab', __('Save'), array('class' => 'btn btn-success pull-right')) ?>
<?php echo Form::close() ?>