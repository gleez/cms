<?php include Kohana::find_file('views', 'errors/partial'); ?>

<!-- ######### Sign Up start -->
<div id="signup" class="col-sm-6">
	<div class="title">
		<?php echo __('Fill in the information below to register'); ?>
	</div>
	<?php echo Form::open($action, array('class' => 'form-horizontal', 'role' => 'form')); ?>
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
			<hr>
			<div class="form-group">
				<div class="col-md-12">
					<?php echo Form::button('register', __('Register new account'), array('class' => 'btn btn-success pull-right', 'tabindex' => 11, 'type' => 'submit')) ?>
				</div>
			</div>
		</fieldset>
	<?php echo Form::close(); ?>
</div>
<!-- ######### Sign Up end -->

<!-- ######### Sign In start -->
<div id="signin"  class="col-sm-6">
	<div class="title">
		<?php echo __('Already have an account? Choose how you would like to sign in'); ?>
	</div>
	<div class="signin-wrapper">
		<p><?php echo __('You can sign in from any of the following services:'); ?></p>
		<ul id="providers">
			<?php $providers = array_filter($config->providers); ?>
			<li class="provider base">
				<?php
				$url = Route::get('user')->uri(array('action' => 'login'));
				echo HTML::anchor($url, $site_name, array('class' => 'picon-base', 'title' =>__('Login with :provider', array(':provider' => $site_name)), 'rel' => 'tooltip', 'data-placement' => 'right'));
				?>
			</li>
			<?php foreach($providers as $provider => $key): ?>
				<li class="provider <?php echo $provider; ?>">
					<?php
					$url = Route::get('user/oauth')->uri(array('controller' => $provider, 'action' => 'login'));
					echo HTML::anchor($url, ucfirst($provider), array('class' => 'picon-'.$provider, 'title' =>__('Login with :provider', array(':provider' => ucfirst($provider))), 'rel' => 'tooltip', 'data-placement' => 'right'));
					?>
				</li>
			<?php endforeach ?>
		</ul>
		<p class="help-signin">
			<?php echo __("If you don't use any of these services, you can create an account."); ?>
			<?php echo __('Fast, safe & secure way!'); ?>
		</p>
	</div>
</div>
<!-- ######### Sign In end -->
