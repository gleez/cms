<?php
	// @todo Should be moved to controller
	Assets::css('user', 'media/css/user.css', array('weight' => 2));
?>

<div class="col-md-3 col-md-offset-4 admin-log">
	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="panel panel-default window-shadow">
		<div class="panel-heading">
			<h1 class="panel-title"><?php echo $site_name ?></h1>
		</div>
		<?php echo Form::open($action, array('class' => 'form form-horizontal', 'role' => 'form')); ?>
			<div class="panel-body">
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-1">
						<p><?php echo __('Sign in using your registered account'); ?></p>
					</div>
				</div>
				<div class="form-group <?php echo isset($errors['name']) ? 'has-error': ''; ?>">
					<div class="col-sm-10 col-sm-offset-1">
						<?php echo Form::label('name', __('Username'), array('class' => 'sr-only control-label')) ?>
						<?php echo Form::input('name', $post->name, array('class' =>'form-control', 'placeholder' => __('Username'))); ?>
					</div>
				</div>
				<div class="form-group <?php echo isset($errors['password']) ? 'has-error': ''; ?>">
					<div class="col-sm-10 col-sm-offset-1">
						<?php echo Form::label('name', __('Password'), array('class' => 'sr-only control-label')) ?>
						<?php echo Form::password('password', NULL, array('class' =>'form-control', 'placeholder' => __('Password'))); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-1">
						<div class="checkbox">
							<label for="remember">
								<input id="remember" name="remember" type="checkbox" class="field login-checkbox" value="1" tabindex="4">&nbsp;
								<?php _e('Stay Signed in'); ?>
							</label>
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-10 col-sm-offset-1">
						<?php echo Form::submit('login', __('Login'), array('class' => 'btn btn-primary btn-lg btn-block', 'type' => 'submit')); ?>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-6">
						<?php echo HTML::anchor('user/reset/password', __('Forgot Password?')); ?>
					</div>
				</div>
			</div>
		<?php echo Form::close() ?>
	</div>
</div>