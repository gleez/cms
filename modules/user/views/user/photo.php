<?php echo Form::open(Route::get('user')->uri(array('id' => $user->id, 'action' => 'photo')), array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="control-group <?php echo isset($errors['picture']) ? 'error': ''; ?>">
		<?php echo Form::label('photo', __('Photo'), array('class' => 'control-label')) ?>
		<div class="controls">
			<?php print Form::file('picture', array('class' => 'input-file')); ?>
		</div>
	</div>

	<blockquote>
		<small class="muted">
			<?php _e('Your picture will be changed proportionally to the size of :w&times;:h', array(':w' => 210, ':h' => 210)); ?>
		</small>
		<small class="muted">
			<?php _e('Allowed image formats: :formats', array(':formats' => '<strong>'.implode('</strong>, <strong>', $allowed_types).'</strong>')) ?>
		</small>
	</blockquote>

	<?php echo Form::submit('user_edit', __('Upload'), array('class' => 'btn btn-success')); ?>

<?php echo Form::close(); ?>