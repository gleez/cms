<?php include Kohana::find_file('views', 'errors/partial'); ?>

<div class="col-sm-6">
	<div class="panel panel-default window-shadow">
		<div class="panel-heading">
			<h3 class="panel-title">
				<?php _e('Fill in the information below to register') ?>
			</h3>
		</div>
		<?php echo Form::open($action, array('class' => 'form-horizontal', 'role' => 'form')); ?>
		<div class="panel-body">
			<fieldset>
				<?php if ($config->username): ?>

					<div class="form-group <?php echo isset($errors['name']) ? 'has-error': ''; ?>">
						<?php echo Form::label('name', __('Username'), array('class' => 'col-sm-3 control-label')); ?>
						<div class="col-xs-12 col-sm-8">
							<?php echo Form::input('name', $post->name, array('class' => 'form-control', 'type' => "text", 'title' => __('Username for login'))); ?>
						</div>
					</div>
				<?php endif ?>

				<div class="form-group <?php echo isset($errors['mail']) ? 'has-error': ''; ?>">
					<?php echo Form::label('mail', __('E-mail'), array('class' => 'col-sm-3 control-label')); ?>
					<div class="col-xs-12 col-sm-8">
						<?php echo Form::input('mail', $post->mail, array('class' => 'form-control',  'rel' => 'tooltip', 'data-placement' => 'right', 'title' => __('Will be private'))); ?>
					</div>
				</div>

				<div class="form-group <?php echo isset($errors['pass']) ? 'has-error': ''; ?>">
					<?php echo Form::label('pass', __('Password'), array('class' => 'col-sm-3 control-label')); ?>
					<div class="col-xs-12 col-sm-8">
						<?php echo Form::password('pass', NULL, array('class' => 'form-control',  'rel' => 'tooltip', 'data-placement' => 'right', 'title' => __('Try to come up with a complex password'))); ?>
					</div>
				</div>

				<?php if ($config->confirm_pass): ?>
					<div class="form-group <?php echo isset($errors['pass_confirm']) ? 'has-error': ''; ?>">
						<?php echo Form::label('pass_confirm', __('Confirm Password'), array('class' => 'col-sm-3 control-label')); ?>
						<div class="col-xs-12 col-sm-8">
							<?php echo Form::password('pass_confirm', NULL, array('class' => 'form-control',  'rel' => 'tooltip', 'data-placement' => 'right', 'title' => __('Repeat entered password'))); ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ($config->use_nick): ?>
					<div class="form-group <?php echo isset($errors['nick']) ? 'has-error': ''; ?>">
						<?php echo Form::label('nick', __('Display Name'), array('class' => 'col-sm-3 control-label')); ?>
						<div class="col-xs-12 col-sm-8">
							<?php echo Form::input('nick', $post->nick, array('class' => 'form-control',  'rel' => 'tooltip', 'data-placement' => 'right', 'title' => __('Will be public'))); ?>
						</div>
					</div>
				<?php endif ?>

				<div class="form-group <?php echo isset($errors['gender']) ? 'has-error': ''; ?>">
					<?php echo Form::label('gender', __('Gender'), array('class' => 'col-sm-3 control-label')); ?>
					<div class="col-xs-12 col-sm-8">
						<div class="radio">
							<?php echo Form::label('gender1', Form::radio('gender', 1, $male) . __('Male')); ?>
						</div>
						<div class="radio">
							<?php echo Form::label('gender2', Form::radio('gender', 2, $female) . __('Female')); ?>
						</div>
					</div>
				</div>

				<div class="form-group <?php echo isset($errors['dob']) ? 'has-error': ''; ?>">
					<?php echo Form::label('dob', __('Birthday'), array('class' => 'col-sm-3 control-label')); ?>
					<div class="col-sm-3">
						<?php echo Form::select('month', Date::months(Date::MONTHS_SHORT), '', array('class' => 'form-control')); ?>
					</div>
					<div class="col-sm-2">
						<?php echo Form::select('days',  Date::days(Date::DAY), '', array('class' => 'form-control')); ?>
					</div>
					<div class="col-sm-3">
						<?php echo Form::select('years', Date::years(date('Y') - 95, date('Y') - 5), date('Y') - 5, array('class' => 'form-control')); ?>
					</div>
				</div>

				<?php if ($config->use_captcha  AND ! $captcha->promoted()) : ?>
					<div class="form-group captcha <?php echo isset($errors['captcha']) ? 'has-error': ''; ?>">
						<?php echo Form::label('_captcha', __('Security code'), array('class' => 'col-sm-3 control-label')); ?>
						<div class="col-sm-4">
							<?php echo Form::input('_captcha', '', array('class' => 'form-control input-md')); ?>
							<br><span class="captcha-image"><?php echo $captcha; ?></span>
						</div>
						<div class="clearfix"></div><br>
					</div>
				<?php endif; ?>
			</fieldset>
		</div>
		<div class="panel-footer">
			<div class="col-md-12 clearfix">
				<?php echo Form::button('register', __('Register new account'), array('class' => 'btn btn-success pull-right', 'tabindex' => 11, 'type' => 'submit')) ?>
			</div>
		</div>
		<?php echo Form::close(); ?>
	</div>
</div>

<div class="col-sm-6">
	<div class="panel panel-default window-shadow">
		<div class="panel-heading">
			<h3 class="panel-title">
				<?php _e('Already have an account? Choose how you would like to sign in') ?>
			</h3>
		</div>
		<div class="panel-body">
			<div class="form-group oauth-buttons">
				<?php
					_e('You can sign in from any of the following services:');

					$providers = array_filter($config->providers);

					echo HTML::anchor(Route::get('user')->uri(array('action' => 'login')), '<i class="fa fa-home"></i> '.$site_name, array('class' => 'btn btn-default', 'title' =>__('Login with :provider', array(':provider' => $site_name)), 'rel' => 'tooltip', 'data-placement' => 'right'));

					foreach($providers as $provider => $key)
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
						echo HTML::anchor($url, '<i class="fa fa-'.$class.'"></i> '.ucfirst($provider), array('class' => 'btn btn-default', 'title' =>__('Login with :provider', array(':provider' => $provider)), 'rel' => 'tooltip', 'data-placement' => 'right'));
					}
				?>
			</div>
			<p class="help-signin">
				<?php _e("If you don't use any of these services, you can create an account.") ?>
				<?php _e('Fast, safe & secure way!') ?>
			</p>
		</div>
	</div>
</div>
