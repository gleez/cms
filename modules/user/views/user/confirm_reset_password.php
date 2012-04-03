<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?php echo Form::open(Request::current()->uri().URL::query()) // The query string (with token) is required here ?>

	<?php if ( ! empty($errors)): ?>
		<div id="formerrors" class="errorbox">
			<h3>Ooops!</h3>
			<ol>
				<?php foreach($errors as $field => $message): ?>
					<li>	
						<?php echo $message; ?>
					</li>
				<?php endforeach ?>
			</ol>
		</div>
	<?php endif ?>
	
        <div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
		<?php echo Form::label('pass', 'New password:') ?>
		<?php echo Form::password('pass', NULL, array('class' => 'text medium')); ?>
        </div>
	
        <div class="control-group <?php echo isset($errors['pass_confirm']) ? 'error': ''; ?>">
		<?php echo Form::label('pass', 'New password again:') ?>
		<?php echo Form::password('pass_confirm', NULL, array('class' => 'text medium')); ?>
        </div>

<?php echo Form::submit('password_confirm', __('Apply password and go to signin form'), array('class' => 'btn btn-primary')) ?>
<?php echo Form::close() ?>