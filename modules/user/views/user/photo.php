<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?php echo Form::open( Route::get('user')->uri( array('id' => $user->id, 'action' => 'photo') ), array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data') ) ?>

<div class="modal-body">
	<?php if ( ! empty($errors)): ?>
		<div id="formerrors" class="errorbox">
			<h3>Ooops!</h3>
			<ol>
				<?php foreach($errors as $field => $message): ?>
					<li>	
						<?php echo $field .': '.$message ?>
					</li>
				<?php endforeach ?>
			</ol>
		</div>
	<?php endif ?>

        <div class="control-group <?php echo isset($errors['picture']) ? 'error': ''; ?>">
                <?php echo Form::label('photo', 'Photo:', array('class' => 'control-label')) ?>
                <?php print Form::file('picture', array('class' => 'input-file')); ?>
        </div>
</div>

<div id="status"></div>
<div class="progress progress-success progress-striped active hide">	<div class="bar" style="width: 0%;"></div > </div>

<div class="modal-footer">
	<?php echo Form::submit('user_edit', __('Upload Photo'), array('class' => 'btn btn-primary')) ?>
</div>

<?php echo Form::close() ?>
