<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php $parms = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
        echo Form::open( Route::get('admin/role')->uri($parms) ) ?>

	<?php if ( ! empty($errors)): ?>
		<div id="formerrors" class="errorbox">
			<h3>Ooops!</h3>
			<ol>
				<?php foreach($errors as $field => $message): ?>
					<li>	
						<?php echo $message ?>
					</li>
				<?php endforeach ?>
			</ol>
		</div>
	<?php endif ?>


<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
    <?php echo Form::label('name', 'Name:') ?>
    <?php echo Form::input('name', $post->name, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['description']) ? 'error': ''; ?>">
	<?php echo Form::label('description', 'Description:') ?>
 	<?php echo Form::input('description', $post->description, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['special']) ? 'error': ''; ?>">
    	<?php echo Form::label('special', 'Special: ') ?> 
    	<?php echo Form::select('special', array(0 => 'No', 1 => 'Yes'), $post->special, array('class' => 'list small')); ?>
</div>

<?php echo Form::submit('role', __('Submit'), array('class' => 'btn btn-primary')) ?>


<?php echo Form::close() ?>


