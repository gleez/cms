<div class="help">
	<?php echo __('Add new menus to your site, edit existing menus, and rename and reorganize menu links.') ?>
</div>

<?php echo Form::open($action, array('id'=>'menu-form ', 'class'=>'menu-form form form-horizontal well')); ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="form-group <?php echo isset($errors['title']) ? 'error': ''; ?>">
		<?php echo Form::label('title', __('Title'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-6">
			<?php echo Form::input('title', $post->title, array('class' => 'form-control col-md-6')); ?>
		</div>
	</div>

	<div class="form-group <?php echo isset($errors['descp']) ? 'error': ''; ?>">
		<?php echo Form::label('description', __('Description'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-6">
			<?php echo Form::textarea('descp', $post->descp, array('class' => 'form-control col-md-6', 'rows' => 3)) ?>
		</div>
	</div>

	<div class="clearfix"></div>
	<?php echo Form::submit('menu', __('Save'), array('class' => 'btn btn-success pull-right')) ?>

<?php echo Form::close() ?>
