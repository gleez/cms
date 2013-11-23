<?php Assets::css('user', 'media/css/user.css', array('weight' => 2)); ?>

<div class="account-container col-md-4">
	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="content clearfix">
		<?php echo Form::open($action, array('class' => 'row', 'role' => 'form')); ?>
			<h1><?php echo $site_name ?></h1>

			<div class="login-fields">
				<p><?php echo __('Sign in using your registered account'); ?></p>

				<div class="form-group <?php echo isset($errors['name']) ? 'has-error': ''; ?>">
					<?php echo Form::label('name', __('Username/Email'), array('class' => 'sr-only control-label')) ?>
					<?php echo Form::input('name', $post->name, array('class' =>'form-control', 'placeholder' => __('Username/Email'))); ?>
				</div>

				<div class="form-group <?php echo isset($errors['password']) ? 'has-error': ''; ?>">
					<?php echo Form::label('name', __('Password'), array('class' => 'sr-only control-label')) ?>
					<?php echo Form::password('password', NULL, array('class' =>'form-control', 'placeholder' => __('Password'))); ?>
				</div>
			</div>

			<div class="login-actions">
				<?php echo Form::submit('login', __('Login'), array('class' => 'btn btn-primary btn-lg btn-block', 'type' => 'submit')); ?>
				<div class="clearfix"></div><br>
				<span class="login-checkbox">
					<input id="remember" name="remember" type="checkbox" class="field login-checkbox" value="TRUE" tabindex="4">
					<label class="choice" for="remember"><?php echo __('Stay Signed in'); ?></label>
				</span>
				<?php echo HTML::anchor('user/reset/password', __('Forgot Password?'), array('class' => 'pull-right')); ?>
				<div class="clearfix"></div><br>

			 	<?php if ($register): ?>
			 		<div class="text-center">
						<?php echo HTML::anchor('user/register', __("Don't have an account?")); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ($providers): ?>
				<div class="login-social">
					<hr>
					<p><?php echo __('Sign in using social network:')?></p>
					<?php foreach ($providers as $provider => $key): ?>
						<div class="<?php echo $provider?>">
							<?php
								$url = Route::get('user/oauth')->uri(array('controller' => $provider, 'action' => 'login'));
								echo HTML::anchor($url, ucfirst($provider), array('class' => 'btn-social', 'title' => __('Login with :provider', array(':provider' => ucfirst($provider)))));
							?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		<?php echo Form::close() ?>
	</div>
</div>
