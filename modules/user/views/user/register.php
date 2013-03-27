<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<div class="signin-form row-fluid">
	<div class="span6">
		<?php echo Form::open($action, array('class' => 'form-horizontal1')); ?>
		
		<div class="box corner-all">
			<div class="box-header grd-teal color-white corner-top">
				<span><?php echo __('Fill in the information below to register'); ?></span>
			</div>
		<?php include Kohana::find_file('views', 'errors/partial'); ?>
		<div class="box-body bg-white">
		<div class="inside">
			<?php if ($config->username): ?>
				<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
					<?php echo Form::label('name', __('Username'), array('class' => 'control-label')) ?>
					<div class="controls">
						<?php echo Form::input('name', $post->name, array('class' => 'span10',  'rel' => 'tooltip', 'data-placement' => 'top', 'title' => __('Username for login'))); ?>
					</div>
				</div>
			<?php endif ?>

			<div class="control-group <?php echo isset($errors['mail']) ? 'error': ''; ?>">
				<?php echo Form::label('mail', __('E-mail'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::input('mail', $post->mail, array('class' => 'span10',  'rel' => 'tooltip', 'data-placement' => 'top', 'title' => __('Will be private'))); ?>
				</div>
			</div>

			<div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
				<?php echo Form::label('pass', __('Password'), array('class' => 'control-label')); ?>
				<div class="controls">
					<?php echo Form::password('pass', NULL, array('class' => 'span10',  'rel' => 'tooltip', 'data-placement' => 'top', 'title' => __('Try to come up with a complex password'))); ?>
				</div>
			</div>

			<?php if ($config->confirm_pass): ?>
				<div class="control-group <?php echo isset($errors['pass_confirm']) ? 'error': ''; ?>">
					<?php echo Form::label('pass_confirm', __('Confirm Password'), array('class' => 'control-label')) ?>
					<div class="controls">
						<?php echo Form::password('pass_confirm', NULL, array('class' => 'span10',  'rel' => 'tooltip', 'data-placement' => 'top', 'title' => __('Repeat entered password'))); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($config->use_nick): ?>
				<div class="control-group <?php echo isset($errors['nick']) ? 'error': ''; ?>">
					<?php echo Form::label('nick', __('Display Name'), array('class' => 'control-label')) ?>
					<div class="controls">
						<?php echo Form::input('nick', $post->nick, array('class' => 'span10',  'rel' => 'tooltip', 'data-placement' => 'top', 'title' => __('Will be public'))); ?>
					</div>
				</div>
			<?php endif ?>

			<div class="control-group <?php echo isset($errors['gender']) ? 'error': ''; ?>">
				<?php echo Form::label('gender', __('Gender'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::label('gender1', Form::radio('gender', 1, $male) . __('Male'), array('class' => 'radio')); ?>
					<?php echo Form::label('gender2', Form::radio('gender', 2, $female) . __('Female'), array('class' => 'radio')); ?>
				</div>
			</div>

			<div class="control-group <?php echo isset($errors['dob']) ? 'error': ''; ?>">
				<?php echo Form::label('dob', __('Birthday'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::select('month', Date::months(Date::MONTHS_SHORT), '', array('class' => 'span4 inline')); ?>
					<?php echo Form::select('days',  Date::days(Date::DAY), '', array('class' => 'span3 inline')); ?>
					<?php echo Form::select('years', Date::years(date('Y') - 95, date('Y') - 5), date('Y') - 5, array('class' => 'span4 inline')); ?>
				</div>
			</div>

			<?php if ($config->use_captcha  AND ! $captcha->promoted()) : ?>
				<div class="control-group captcha <?php echo isset($errors['captcha']) ? 'error': ''; ?>">
					<?php echo Form::label('_captcha', __('Security code'), array('class' => 'control-label') ) ?>
					<div class="controls">
						<?php echo Form::input('_captcha', '', array('class' => 'input-medium',  'rel' => 'tooltip', 'data-placement' => 'top', 'title' => __('Please enter the code from the image.')) ); ?>
						<br><span class="captcha-image"><?php echo $captcha; ?></span>
					</div>
					<div class="clearfix"></div><br>
				</div>
			<?php endif; ?>
		</div>
		<?php echo Form::submit('register', __('Register new account'), array('class' => 'btn btn-danger btn-block btn-large')) ?>
		</div>
		</div>
		<?php echo Form::close(); ?>
	</div>

	<div class="span6">
		<div class="box corner-all">
			<div class="box-header grd-green color-white corner-top">
				<span><?php echo __("Already have an account?"); ?></span>
			</div>
		<div class="box-body bg-white">
		<div class="inside">
			<p><?php echo __('You can sign in from any of the following services:'); ?></p>
			<ul id="providers">
				<?php $providers = array_filter($config->providers); ?>
				<li class="provider base">
					<?php
						$url = Route::get('user')->uri(array('action' => 'login'));
						echo HTML::anchor($url, __(':site Account', array(':site' => ucfirst($site_name) )), array('class' => 'picon-base', 'title' =>__('Login with :provider', array(':provider' => $site_name)), 'rel' => 'tooltip', 'data-placement' => 'right'));
					?>
				</li>
				<?php foreach($providers as $provider => $key): ?>
					<li class="provider <?php echo $provider; ?>">
						<?php
							$url = Route::get('user/oauth')->uri(array('controller' => $provider, 'action' => 'login'));
							echo HTML::anchor($url, __(':prov account', array(':prov' => ucfirst($provider))), array('class' => 'picon-'.$provider, 'title' =>__('Login with :provider', array(':provider' => $provider)), 'rel' => 'tooltip', 'data-placement' => 'right'));
						?>
					</li>
				<?php endforeach ?>
			</ul>
			<hr>
			<small><?php echo __("If you don't use any of these services, you can create an account."); ?></small>
			<small><?php echo __('Fast, safe & secure way!'); ?></small>
		</div>
		</div>
		</div>
	</div>
</div>
