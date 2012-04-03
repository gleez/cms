<?php defined('SYSPATH') or die('No direct script access.'); ?>

	<?php if ( isset($errors['_external']) ): ?>
		<div id="formerrors" class="errorbox">
			<h3>Ooops!</h3>
			<ol>
				<?php foreach($errors['_external'] as $field => $message): ?>
					<li>	
						<?php echo $message; ?>
					</li>
				<?php endforeach ?>
			</ol>
		</div>
	<?php endif ?>

<?php echo Form::open(Route::get('user')->uri(array('action' => 'password')). URL::query( array('destination' => Request::initial()->uri()) )) ?>

<div class="control-group <?php echo isset($errors['_external']['old_pass']) ? 'error': ''; ?>">
	<?php echo Form::label('old_pass', 'Current password:', array('class' => 'wrap')) ?>
   	<?php echo Form::password('old_pass', NULL, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['_external']['pass']) ? 'error': ''; ?>">
	<?php echo Form::label('pass', 'New password: ', array('class' => 'wrap')) ?>
   	<?php echo Form::password('pass', NULL, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['_external']['pass_confirm']) ? 'error': ''; ?>">
	<?php echo Form::label('pass_confirm', 'New password (again): ', array('class' => 'wrap')) ?>
   	<?php echo Form::password('pass_confirm', NULL, array('class' => 'text small')); ?>
</div>

<?php echo Form::submit('change_pass', __('Submit'), array('class' => 'btn btn-primary')) ?>
<?php echo Form::close() ?>
