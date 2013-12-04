<?php include Kohana::find_file('views', 'errors/partial'); ?>

<?php echo Form::open($action, array('role' => 'form')); ?>
	<p><?php echo __('Sign in using your registered account'); ?></p>

	<div class="form-group <?php echo isset($errors['name']) ? 'has-error': ''; ?>">
		<?php echo Form::label('name', __('Username/Email'), array('class' => 'sr-only control-label')) ?>
		<div class="col-sm-12">
			<div class="row">
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-user"></i></span>
					<?php echo Form::input('name', $post->name, array('class' => 'form-control', 'placeholder' => __('Username/Email'))) ?>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group <?php echo isset($errors['password']) ? 'has-error': ''; ?>">
		<?php echo Form::label('name', __('Password'), array('class' => 'sr-only control-label')) ?>
		<div class="col-sm-12">
			<div class="row">
				<div class="input-group">
					<span class="input-group-addon"><i class="fa fa-key"></i></span>
					<?php echo Form::password('password', NULL, array('class' => 'form-control', 'placeholder' => __('Password'))) ?>
				</div>
			</div>
		</div>
	</div>

	<div class="form-group">
		<div class="checkbox col-sm-12">
			<?php echo Form::checkbox('remember', TRUE, FALSE, array('tabindex' => 4)) . ' ' . __('Stay Signed in') ?>
		</div>
	</div>

	<div class="form-group">
		<?php echo Form::submit('login', __('Sign In'), array('class' => 'btn btn-primary btn-block')) ?>
	</div>

	<div class="form-group">
		<ul>
			<li><?php echo HTML::anchor('user/reset/password', __('Forgot Password?')) ?></li>
			<?php if ($register): ?>
				<li><?php echo __("Don't have an account? :url", array(':url' => HTML::anchor('user/register', __('Create One.')))) ?></li>
			<?php endif ?>
		</ul>
	</div>

	<?php if ($providers): ?>
		<hr>
		<div class="form-group">
			<p><?php echo __('Sign in using social network:');?></p>
			<div class="btn-group">
				<?php
					foreach ($providers as $provider => $key)
					{
						// @todo Ugly hack
						switch ($provider)
						{
							case 'google':
								$class = 'google-plus';
								break;
							case 'live':
								$class = 'windows';
								break;
							default:
								$class = $provider;
						}

						$url = Route::get('user/oauth')->uri(array('controller' => $provider, 'action' => 'login'));
						echo HTML::anchor($url, '<i class="fa fa-'.$class.'"></i>', array('class' => 'btn btn-default', 'title' =>__('Login with :provider', array(':provider' => $provider))));
					}
				?>
			</div>
			<p><small><?php echo __('Fast, safe & secure way!');?></small></p>
		</div>
	<?php endif; ?>

<?php echo Form::close() ?>
