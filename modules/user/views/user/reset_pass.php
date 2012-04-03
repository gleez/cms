<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?php echo Form::open( Route::get('user/reset')->uri(array('action' => 'password')) ) ?>

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
	
        <div class="control-group <?php echo isset($errors['mail']) ? 'error': ''; ?>">
		<?php echo Form::label('mail', 'Email') ?>
		<?php echo Form::input('mail', $post['mail'], array('class' => 'text medium')); ?>
        </div>

	<?php echo Form::submit('reset_pass', __('Reset password'), array('class' => 'btn btn-primary')) ?>
<?php echo Form::close() ?>