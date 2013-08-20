<?php echo Form::open($action, array('class' => 'form form-horizontal')) ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="control-group <?php echo isset($errors['mail']) ? 'error': ''; ?>">
		<?php echo Form::label('mail', __('Email'), array('class' => 'control-label')); ?>
		<div class="controls">
			<div class="input-prepend">
				<span class="add-on">@</span>
				<?php echo Form::input('mail', $post['mail'], array('class' => 'input-large')); ?>
			</div>
		</div>
	</div>

	<?php echo Form::submit('reset_pass', __('Reset'), array('class' => 'btn btn-danger')); ?>

<?php echo Form::close() ?>