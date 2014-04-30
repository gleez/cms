<?php include Kohana::find_file('views', 'errors/partial');?>
<?php
    if ( isset($oaclient->id) AND Valid::digit($oaclient->id) )
    {
		$parms     = array('id' => $oaclient->id, 'action' => 'edit');
		$btntxt    = __("Save Changes");
    }
    else
    {
		$parms     = array('action' => 'register');
		$btntxt    = __("Register");
    }
?>
<?php echo Form::open(Route::get('oauth2/client')->uri($parms), array('class' => 'form form-horizontal', 'enctype' => 'multipart/form-data')) ?>
	<div class="row-fluid">
		<div class="form-group <?php echo isset($errors['title']) ? 'error' : ''; ?>">
			<?php echo Form::label('title', __('Title'), array('class' => 'control-label1'))?>
			<div class="controls ">
				<?php echo Form::input('title', $oaclient->title, array('class' => 'col-sm-5'));?>
			</div>
		</div>
		
		<div class="form-group <?php echo isset($errors['redirect_uri']) ? 'error' : ''; ?>">
			<?php echo Form::label('redirect_uri', __('Redirect URL'), array('class' => 'control-label1'))?>
			<div class="controls ">
				<?php echo Form::input('redirect_uri', $oaclient->redirect_uri, array('class' => 'col-sm-5'));?>
			</div>
		</div>
		
		<div class="form-group <?php echo isset($errors['description']) ? 'error' : ''; ?>">
			<?php echo Form::label('description', __('Description'), array('class' => 'control-label1'))?>
			<div class="controls ">
				<?php echo Form::textarea('description', $oaclient->description, array('class' => 'col-sm-5', 'rows' => 3));?>
			</div>
		</div>
		
		<?php if(User::is_admin()): ?>
			<div class="form-group <?php //echo isset($errors['grant_types']) ? 'error' : ''; ?>">
				<?php echo Form::label('grant_types', __('Grant Types'), array('class' => 'control-label1'))?>
				<div class="controls ">
					<?php
						$selected = explode(" ", $oaclient->grant_types);
					?>
					<?php foreach ($grant_types as $k => $v) : ?>
					<label for="grant_types[<?php echo $k?>]" class=" checkbox">
						<input type="checkbox" <?php echo in_array($k, $selected) ? "checked='checked'" : "";?> value="<?php echo $k?>" name="grant_types[<?php echo $k?>]" id="form-grant_types_<?php echo $k?>">
						<?php echo $v ?>
					</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif;?>
		<div class="form-group <?php echo isset($errors['logo']) ? 'error': ''; ?>">
			<?php echo Form::label('logo', __('Logo'), array('class' => 'control-label1') ) ?>
			<div class="controls">
				<?php echo Form::file('logo', array('class' => 'span12', 'title' => 'Upload')); ?>
			</div>
		</div>
		<?php if ($oaclient->logo): ?>
			<div class="thumbnail">
				<?php echo HTML::resize("media/logos/".$oaclient->logo); ?>
			</div>
		<?php endif; ?>
		
		<div class="form-group">
		    <div class="form-actions-left">
			<?php echo Form::submit('save', $btntxt, array('class' => 'btn btn-success'));?>
			<?php echo Form::submit('cancel', __('Cancel'), array('class' => 'btn btn-default'));?>
		    </div>
		</div>
	</div>
<?php echo Form::close(); ?>