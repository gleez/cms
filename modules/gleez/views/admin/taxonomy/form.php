<?php defined('SYSPATH') or die('No direct script access.') ?>

<?php $parms = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
	echo Form::open(Route::get('admin/taxonomy')->uri($parms), array('id'=>'vocab-form', 'class'=>'form')) ?>

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
	<?php echo Form::label('name', __('Name:'), array('class' => 'control-label')) ?>
   	<?php echo Form::input('name', $post->rawname, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['type']) ? 'error': ''; ?>">
	<?php echo Form::label('type', __('Type:'), array('class' => 'control-label')) ?>
	<?php echo Form::select('type', Gleez::types(), $post->type, array('class' => 'list small')); ?> 
</div>
	
<div class="control-group <?php echo isset($errors['description']) ? 'error': ''; ?>">
 	<?php echo Form::label('description', __('Description:'), array('class' => 'control-label')) ?>
 	<?php echo Form::textarea('description', $post->description, array('class' => 'input-large', 'rows' => 5)) ?>
</div>

<?php echo Form::submit('vocab', __('Submit'), array('class' => 'btn btn-primary btn-large')) ?>
<?php echo Form::close() ?>