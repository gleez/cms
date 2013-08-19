<div class="account-container">
	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<?php echo Form::open($action, array('class' => 'row-fluid')); ?>
		<p><?php echo __('Sign in using your registered account:'); ?></p>

		<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on"><i class="icon-large icon-user"></i></span>
					<?php echo Form::input('name', $post->name, array('class' => 'span10', 'placeholder' => __('Username/Email'))); ?>
				</div>
			</div>
		</div>

		<div class="control-group <?php echo isset($errors['password']) ? 'error': ''; ?>">
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on"><i class="icon-large icon-key"></i></span>
					<?php echo Form::password('password', NULL, array('class' => 'span10', 'placeholder' => __('Password'))); ?>
				</div>
			</div>
		</div>

		<div class="control-group clearfix">
			<div class="span6">
				<?php echo Form::checkbox('remember', TRUE, FALSE, array('tabindex' => 4)) . ' ' . __('Stay Signed in'); ?>
			</div>
			<div class="span6 clearfix">
				<?php echo Form::submit('login', __('Sign In'), array('class' => 'btn btn-danger')); ?>
			</div>
			<div class="span10">
				<ul>
					<li><?php echo HTML::anchor('user/reset/password', __('Forgot Password?')); ?></li>
					<li><?php echo __("Don't have an account? :url", array(':url' => HTML::anchor('user/register', __('Create One.'))) ); ?></li>
				</ul>
			</div>
		</div>

		<?php if ($providers): ?>
			<div class="control-group clearfix">
				<p><?php echo __('Sign in using social network:');?></p>
				<ul id="auth-providers">
					<?php foreach ($providers as $provider => $key): ?>
						<li class="provider <?php echo $provider; ?>">
							<?php
							$url = Route::get('user/oauth')->uri(array('controller' => $provider, 'action' => 'login'));

							echo HTML::anchor($url, ucfirst($provider), array('id' => $provider, 'title' =>__('Login with :provider', array(':provider' => $provider))));
							?>
						</li>
					<?php endforeach; ?>
					<br><small><?php echo __('Fast, safe & secure way!');?></small>
				</ul>
			</div>
		<?php endif; ?>

	<?php echo Form::close() ?>
</div>