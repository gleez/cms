<?php defined("SYSPATH") OR die("No direct script access.") ?>

<?php
	$parms = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add', 'id' => $menu->id);
	$items = isset($post->id) ? $post->select_list('id', 'title', '--') : $menu->select_list('id', 'title', '--');

	echo Form::open(Route::get('admin/menu/item')->uri($parms), array('id'=>'menu-form', 'class'=>'form form-horizontal well'));

	include Kohana::find_file('views', 'errors/partial');
?>

	<div class="control-group <?php echo isset($errors['title']) ? 'error': ''; ?>">
		<?php echo Form::label('title', __('Title'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::input('title', $post->title, array('class' => 'input-large')); ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
		<?php echo Form::label('name', __('Slug'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::input('name', $post->name, array('class' => 'input-large')); ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['url']) ? 'error': ''; ?>">
		<?php echo Form::label('url', __('Link'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::input('url', $post->url, array('class' => 'input-large')); ?>
		</div>
	</div>

	<?php if( ! isset($post->id) ):?>
		<div class="control-group <?php echo isset($errors['parent']) ? 'error': ''; ?>">
			<?php echo Form::label('parent', __('Parent'), array('class' => 'control-label')) ?>
			<div class="controls">
				<?php echo Form::select('parent', $items, $post->pid, array('class' => 'input-large')); ?>
			</div>
		</div>
	<?php endif; ?>

		<div class="control-group <?php echo isset($errors['image']) ? 'error': ''; ?>">
			<?php echo Form::label('parent', __('Icon'), array('class' => 'control-label')) ?>
			<div class="controls">
				<?php echo Form::rawselect('image', Menu::icons(), $post->image, array('class' => 'input-large')); ?>
			</div>
		</div>

	<div class="control-group <?php echo isset($errors['descp']) ? 'error': ''; ?>">
	 	<?php echo Form::label('description', __('Description'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php echo Form::textarea('descp', $post->descp, array('class' => 'input-large', 'rows' => 5)) ?>
		</div>
	</div>

	<?php echo Form::submit('menu-item', __('Save'), array('class' => 'btn btn-primary')) ?>
<?php echo Form::close() ?>
