<?php
	$parms = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add', 'id' => $menu->id);
	$items = isset($post->id) ? $post->select_list('id', 'title', '--') : $menu->select_list('id', 'title', '--');

	echo Form::open(Route::get('admin/menu/item')->uri($parms), array('id'=>'menu-form', 'class'=>'form form-horizontal well')); ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

<div class="form-group <?php echo isset($errors['title']) ? 'error': ''; ?>">
	<?php echo Form::label('title', __('Title'), array('class' => 'control-label col-md-3')) ?>
	<div class="controls col-md-6">
		<?php echo Form::input('title', $post->title, array('class' => 'form-control')); ?>
	</div>
</div>

<div class="form-group <?php echo isset($errors['url']) ? 'error': ''; ?>">
	<?php echo Form::label('url', __('Link'), array('class' => 'control-label col-md-3')) ?>
	<div class="controls col-md-6">
		<?php echo Form::input('url', $post->url, array('class' => 'form-control'), 'admin/autocomplete/links'); ?>
	</div>
</div>

<?php if( ! isset($post->id) ):?>
	<div class="form-group <?php echo isset($errors['parent']) ? 'error': ''; ?>">
		<?php echo Form::label('parent', __('Parent'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-6">
			<?php echo Form::select('parent', $items, $post->pid, array('class' => 'form-control')); ?>
		</div>
	</div>
<?php endif; ?>

	<div class="form-group <?php echo isset($errors['image']) ? 'error': ''; ?>">
		<?php echo Form::label('image', __('Icon'), array('class' => 'control-label col-md-3')) ?>
		<div class="controls col-md-6">
			<?php echo Form::select('image', System::icons(), $post->image, array('class' => 'form-control select-icons')); ?>
		</div>
	</div>

<div class="form-group <?php echo isset($errors['descp']) ? 'error': ''; ?>">
 	<?php echo Form::label('descp', __('Description'), array('class' => 'control-label col-md-3')) ?>
	<div class="controls col-md-6">
		<?php echo Form::textarea('descp', $post->descp, array('class' => 'form-control', 'rows' => 3)) ?>
	</div>
</div>

<?php echo Form::submit('menu-item', __('Save'), array('class' => 'btn btn-success pull-right')) ?>
<?php echo Form::close() ?>