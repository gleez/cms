<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<?php echo Form::open(Route::get('user')->uri(array('id' => $user->id, 'action' => 'photo')), array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data')); ?>

	<div class="modal-body">
		<?php include Kohana::find_file('views', 'errors/partial'); ?>

		<div class="control-group <?php echo isset($errors['picture']) ? 'error': ''; ?>">
			<?php echo Form::label('photo', __('Photo'), array('class' => 'control-label')) ?>
			<div class="controls">
				<?php print Form::file('picture', array('class' => 'input-file')); ?>
			</div>
		</div>

		<div class="modal-body-text">
			<p><?php echo __('Your picture will be changed proportionally to the size of :w&times;:h', array(':w' => 150, ':h' => 150)); ?></p>
			<?php /** @todo Show info about supported formats.. */ ?>
		</div>

		<div id="status"></div>
		<div class="progress progress-success progress-striped active hide">
			<div class="bar" style="width: 0%;"></div>
		</div>
	</div>

	<div class="modal-footer">
		<?php echo Form::submit('user_edit', __('Upload'), array('class' => 'btn btn-primary')) ?>
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo __('Close') ?></button>
	</div>

<?php echo Form::close(); ?>