<?php
	if (isset($widget->id) AND Valid::digit($widget->id))
	{
		$parms = array('id' => $widget->id, 'action' => 'edit');
		$split_name = explode('/', $widget->name);
		$static = ($split_name AND $split_name[0] == 'static') ? TRUE : FALSE;
	}
	else
	{
		$parms = array('action' => 'add');
		$static = TRUE;
	}

	echo Form::open(Route::get('admin/widget')->uri( $parms ), array('id'=>'widget-form', 'class'=>'form'));
?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="row">
		<div class="col-md-6">

			<div class="form-group <?php echo isset($errors['title']) ? 'has-error': ''; ?>">
				<?php echo Form::label('title', __('Title'), array('class' => 'control-label')) ?>
				<?php echo Form::input('title', $widget->title, array('class' => 'form-control')); ?>
			</div>


			<div class="form-group <?php echo isset($errors['region']) ? 'has-error': ''; ?>">
				<?php echo Form::label('region', __('Region'), array('class' => 'control-label')) ?>
				<?php echo Form::select('region', $regions, $widget->region, array('class' => 'form-control')); ?>
			</div>

			<div class="form-group <?php echo isset($errors['status']) ? 'has-error': ''; ?>">
				<?php $enabled_off = (isset($widget->status) AND $widget->status == 0) ? TRUE : FALSE; ?>
				<?php $enabled_on  = (isset($widget->status) AND $widget->status == 1) ? TRUE : FALSE; ?>

				<?php echo Form::label('status', __('Status'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::label('status', Form::radio('status', 0, $enabled_off).__('Disabled'), array('class' => 'radio-inline')) ?>
					<?php echo Form::label('status', Form::radio('status', 1, $enabled_on).__('Enabled'),  array('class' => 'radio-inline')) ?>
				</div>
			</div>

			<div class="form-group <?php echo isset($errors['roles']) ? 'has-error': ''; ?>">
				<?php echo Form::label('roles', __('Roles'), array('class' => 'control-label')) ?>
				<?php foreach($roles as $role => $name): ?>
					<div class="checkbox">
						<?php echo Form::label('roles', Form::checkbox('roles['.$role.']', $role, in_array($role, explode(',', $widget->roles)) ? TRUE : FALSE).ucfirst($name)); ?>
					</div>
				<?php endforeach ?>
			</div>

		</div>

		<div class="col-md-6">

			<div class="form-group <?php echo isset($errors['icon']) ? 'has-error': ''; ?>">
				<?php echo Form::label('icon', __('Icon'), array('class' => 'control-label')) ?>
				<div class="controls sys-icon">
					<?php echo Form::select('icon', $widget->icons, $widget->icon, array('class' => 'select-icons col-md-12')); ?>
				</div>
			</div>

			<div class="form-group <?php echo isset($errors['show_title']) ? 'has-error': ''; ?>">
				<?php $show_title_off = (isset($widget->show_title) AND $widget->show_title == 0) ? TRUE : FALSE; ?>
				<?php $show_title_on = (isset($widget->show_title) AND $widget->show_title == 1) ? TRUE : FALSE; ?>

				<?php echo Form::label('show_title', __('Show Title'), array('class' => 'control-label')); ?>
				<div class="controls">
					<?php echo Form::label('show_title', Form::radio('show_title', 0, $show_title_off).__('Hide'), array('class' => 'radio-inline')); ?>
					<?php echo Form::label('show_title', Form::radio('show_title', 1, $show_title_on).__('Show'), array('class' => 'radio-inline')); ?>
				</div>
			</div>

			<div class="form-group <?php echo isset($errors['visibility']) ? 'has-error': ''; ?>">
				<?php $visible_off = (isset($widget->status) AND $widget->visibility == 0) ? TRUE : FALSE; ?>
				<?php $visible_on  = (isset($widget->status) AND $widget->visibility == 1) ? TRUE : FALSE; ?>

				<?php echo Form::label('pages', __('Show widget on specific pages'), array('class' => 'control-label')) ?>
				<div class="radio">
					<?php echo Form::label('visibility', Form::radio('visibility', 0, $visible_off).__('All pages except those listed')); ?>
				</div>
				<div class="radio">
					<?php echo Form::label('visibility', Form::radio('visibility', 1, $visible_on).__('Only the listed pages')); ?>
				</div>
			</div>

			<div class="form-group <?php echo isset($errors['pages']) ? 'has-error': ''; ?>">
				<?php echo Form::textarea('pages', $widget->pages, array('class' => 'textarea form-control nowrap', 'rows' => 3)) ?>
			</div>

			<?php echo $fields; /* custom fields set by widget */ ?>

			<?php if ($static): ?>
				<div class="form-group <?php echo isset($errors['body']) ? 'has-error': ''; ?>">
					<?php echo Form::label('body', __('Content'), array('class' => 'control-label')) ?>
					<?php echo Form::textarea('body', $widget->body, array('class' => 'textarea form-control nowrap', 'rows' => 5)) ?>
				</div>

				<div class="form-group <?php echo isset($errors['format']) ? 'has-error': ''; ?>">
					<div class="controls">
						<?php echo Form::label('format', __('Text format'), array('class' => 'control-label')) ?>
						<?php echo Form::select('format', Filter::formats(), $widget->format, array('class' => 'form-control')); ?>
					</div>
				</div>
			<?php endif ?>
		</div>

	</div>

	<div class="clearfix"></div>

	<?php echo Form::hidden('widget', $widget->name); ?>
	<?php echo Form::submit('widget', __('Save'), array('class' => 'btn btn-success pull-right')); ?>
<?php echo Form::close(); ?>
