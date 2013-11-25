<div class="help">
	<?php _e('Add new category groups to your site, edit and reorganize existing categories.') ?>
</div>

<?php $params = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
	echo Form::open(Route::get('admin/taxonomy')->uri($params), array('id'=>'vocab-form', 'class'=>'form form-horizontal well', 'role' => 'form')) ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="form-group <?php echo isset($errors['name']) ? 'has-error': ''; ?>">
		<?php echo Form::label('name', __('Group Name'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-5">
			<?php echo Form::input('name', $post->rawname, array('class' => 'form-control')); ?>
		</div>
	</div>

	<div class="form-group <?php echo isset($errors['type']) ? 'has-error': ''; ?>">
		<?php echo Form::label('type', __('Type'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-5">
			<?php echo Form::select('type', Gleez::types(), $post->type, array('class' => 'form-control')); ?>
			<span class="help-block"><?php _e('For what type of content you intend to use categories from this group?') ?></span>
		</div>
	</div>

	<div class="form-group <?php echo isset($errors['description']) ? 'has-error': ''; ?>">
		<?php echo Form::label('description', __('Description'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-5">
			<?php echo Form::textarea('description', $post->description, array('class' => 'form-control', 'rows' => 5)) ?>
			<span class="help-block"><?php _e('Description not visible by default, however some themes may show it. The main purpose of this description - to inform administrator about the group.') ?></span>
		</div>
	</div>

	<div class="form-group ab-wrapper">
		<div class="col-md-12">
			<?php echo Form::button('vocab', __('Save'), array('class' => 'btn btn-success pull-right', 'type' => 'submit'))?>
		</div>
	</div>
<?php echo Form::close() ?>
