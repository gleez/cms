<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="help">
        <?php echo __('Add new menus to your site, edit existing menus, and rename and reorganize menu links.') ?>
</div>
    
<?php $parms = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
	echo Form::open(Route::get('admin/menu')->uri($parms), array('id'=>'menu-form ', 'class'=>'menu-form form')) ?>
    
<?php if ( ! empty($errors)): ?>
	<div id="formerrors" class="errorbox">
		<h3>Ooops!</h3>
		<ol>
			<?php foreach($errors as $field => $message): ?>
				<li> <?php echo $message ?> </li>
			<?php endforeach ?>
		</ol>
	</div>
<?php endif ?>

	<div class="control-group <?php echo isset($errors['title']) ? 'error': ''; ?>">
		<?php echo Form::label('title', 'Title:') ?>
		<?php echo Form::input('title', $post->title, array('class' => 'text small')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
		<?php echo Form::label('name', 'Slug:') ?>
		<?php echo Form::input('name', $post->name, array('class' => 'text small')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['descp']) ? 'error': ''; ?>">
		<?php echo Form::label('description', 'Description:') ?>
		<?php echo form::textarea('descp', $post->descp, array('class' => 'textarea small', 'rows' => 3)) ?>
	</div>

<div class="clearfix"></div>
<?php echo Form::submit('menu', __('Submit'), array('class' => 'btn btn-primary')) ?>

<?php echo Form::close() ?>
	