<?php include Kohana::find_file('views', 'errors/partial'); ?>

<?php echo Form::open($action, array('role' => 'form')); ?>
	<p><?php echo __('Sign in using your registered account'); ?></p>

	<div class="form-group <?php echo isset($errors['name']) ? 'has-error': ''; ?>">
		<?php echo Form::label('name', __('Username/Email'), array('class' => 'sr-only control-label')); ?>

		<div class="input-group">
			<span class="input-group-addon"><i class="fa fa-user"></i></span>
			<?php echo Form::input('name', $post->name, array('class' => 'form-control', 'placeholder' => __('Email'))); ?>
		</div>
	</div>

	<div class="form-group <?php echo isset($errors['password']) ? 'has-error': ''; ?>">
		<?php echo Form::label('name', __('Password'), array('class' => 'sr-only control-label')) ?>

		<div class="input-group">
			<span class="input-group-addon"><i class="fa fa-key"></i></span>
			<?php echo Form::password('password', NULL, array('class' => 'form-control', 'placeholder' => __('Password'))); ?>
		</div>
	</div>

	<div class="form-group">
		<div class="checkbox">
			<label for="remember">
				<input id="remember" name="remember" type="checkbox" class="field login-checkbox" value="1" tabindex="4">&nbsp;
				<?php _e('Stay Signed in'); ?>
			</label>
		</div>
	</div>

	<div class="form-group">
		<?php echo Form::submit('login', __('Sign In'), array('class' => 'btn btn-primary btn-block')); ?>
	</div>

	<div class="form-group">
		<div class="col-sm-8">
			<?php echo HTML::anchor('user/reset/password', __('Forgot Password?')); ?>
		</div>
		<?php if ($register): ?>
			<div class="col-sm-4">
				<?php echo HTML::anchor('user/register', __('Register'), array('class' => 'pull-right')); ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ($providers): ?>
		<hr>
		<div class="form-group">
			<p><?php echo __('Sign in using social network:');?></p>
			<div class="btn-group">
				<?php
					foreach ($providers as $name => $provider)
					{
						echo HTML::anchor($provider['url'], '<i class="fa fa-lg fa-'.$provider['icon'].'"></i>', array('class' => 'btn btn-default', 'title' =>__('Login with :provider', array(':provider' => $name))));
					}
				?>
			</div>
		</div>
	<?php endif; ?>

<?php echo Form::close(); ?>