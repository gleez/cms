<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="help">
      <p><?php //echo __('Fill in the information below to register.'); ?></p>
</div>

<?php echo Form::open( Route::get('user')->uri(array('action' => 'register')), array('class' => 'form-horizontal') ) ?>

      <?php if ( ! empty($errors)): ?>
	    <div id="formerrors" class="errorbox">
		  <h3>Ooops!</h3>
			<ol>
			      <?php foreach($errors as $field => $message): ?>
				    <li>	
					  <?php echo $message; ?>
				    </li>
			      <?php endforeach; ?>
			</ol>
	    </div>
      <?php endif ?>
      
<div class="register-left register-column">
      <h3 class='hndle'><?php echo __('Fill in the information below to register'); ?></h3>
      
      <div class="inside">
	    <?php if ($config->username): ?>
		  <div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
			<?php echo Form::label('name', __('Username:'), array('class' => 'control-label')) ?>
			<?php echo Form::input('name', $post->name, array('class' => 'input-large')); ?>
		  </div>
	    <?php endif ?>
      
	    <div class="control-group <?php echo isset($errors['mail']) ? 'error': ''; ?>">
		  <?php echo Form::label('mail', __('E-mail: <small>(Private)</small>'), array('class' => 'control-label')) ?>
		  <?php echo Form::input('mail', $post->mail, array('class' => 'input-large')); ?>
	    </div>

	    <div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
		  <?php echo Form::label('pass', __('Password:'), array('class' => 'control-label')) ?>
		  <?php echo Form::password('pass', NULL, array('class' => 'input-large')); ?>
	    </div>
      
	    <?php if ($config->confirm_pass): ?>
		  <div class="control-group <?php echo isset($errors['pass_confirm']) ? 'error': ''; ?>">
			<?php echo Form::label('pass_confirm', __('Confirm Password:'), array('class' => 'control-label')) ?>
			<?php echo Form::password('pass_confirm', NULL, array('class' => 'input-large')); ?>
		  </div>
	    <?php endif ?>

	    <?php if ($config->use_nick): ?>
		  <div class="control-group <?php echo isset($errors['nick']) ? 'error': ''; ?>">
			<?php echo Form::label('nick', __('Display Name: <small>(Public)</small>'), array('class' => 'control-label')) ?>
			<?php echo Form::input('nick', $post->nick, array('class' => 'input-large')); ?>
		  </div>
	    <?php endif ?>
      
	    <div class="control-group <?php echo isset($errors['gender']) ? 'error': ''; ?>">
		  <?php $gender1 = (isset($post->gender) && $post->gender == 1) ? TRUE : FALSE; ?>
		  <?php $gender2 = (isset($post->gender) && $post->gender == 2) ? TRUE : FALSE; ?>
	
		  <?php echo Form::label('gender', __('Gender: <small>(Private)</small>'), array('class' => 'control-label')) ?> 
		  <div class="controls">
			<?php echo Form::label('gender1', Form::radio('gender', 1, $gender1).__('Male'), array('class' => 'radio inline')) ?> 
			<?php echo Form::label('gender2', Form::radio('gender', 2, $gender2).__('Female'), array('class' => 'radio inline')) ?>
		  </div>
	    </div>

	    <div class="control-group <?php echo isset($errors['dob']) ? 'error': ''; ?>">
		  <?php echo Form::label('dob', 'Birthday: <small>(Private)</small>', array('class' => 'control-label')) ?>
		  <div class="controls">
			<?php echo Form::select('month', Date::months(Date::MONTHS_SHORT), '', array('class' => 'span1 inline')); ?>
			<?php echo Form::select('days',  Date::days(Date::DAY), '', array('class' => 'span1 inline')); ?>
			<?php echo Form::select('years', Date::years(date('Y') - 95,date('Y') - 5), 2000, array('class' => 'span1 inline')); ?>
		  </div>
	    </div>
      
	    <?php if( $config->use_captcha  AND ! $captcha->promoted() ) : ?>
		  <div class="control-group captcha <?php echo isset($errors['captcha']) ? 'error': ''; ?>">
			<?php echo Form::label('_captcha', __('Security:'), array('class' => 'control-label') ) ?>
			<?php echo Form::input('_captcha', '', array('class' => 'input-medium')); ?>
			<span class="captcha-image"><?php echo $captcha; ?></span>
		  </div><br><br>
	    <?php endif; ?>
      </div>
      
      <?php echo Form::submit('register', __('Register new account'), array('class' => 'btn btn-danger btn-large')) ?>
      <div class="clearfix"></div><br>
</div>

<div class="register-right register-column">
      <h3 class='hndle'><?php echo __('Choose how you would like to sign in?'); ?></h3>
      <div class="inside">
	    <?php echo __('You can sign in from any of the following services:'); ?><br><br>
	    <?php echo __('Already have an account?'); ?>

	    <ul id="providers">
		  
		  <li class="provider base">
			<?php
			      $url = Route::get('user')->uri( array( 'action' => 'login'));
			      echo HTML::anchor($url, __(':site Account', array(':site' => ucfirst($site_name) )), array('class' => 'picon-base', 'title' =>__('Login with :pro', array(':pro' => $site_name)) ) );
			?>
		  </li>
		  
		  <?php $providers = array_filter( $config->providers ); ?>
		  <?php foreach($providers as $provider => $key): ?>
			<li class="provider <?php echo $provider; ?>">
			      <?php
				    $url = Route::get('user/oauth')->uri(array('controller' => $provider, 'action' => 'login'));
				    echo HTML::anchor($url, __(':prov account', array(':prov' => ucfirst($provider) )), array('class' => 'picon-'.$provider, 'title' =>__('Login with :pro', array(':pro' => $provider) ) ) );
			      ?>
			</li>
		  <?php endforeach ?>
	    </ul>
	    <small><?php echo __('If you dont use any of these services, you can create an account.'); ?></small>
	    <small><?php echo __('Fast, safe & secure'); ?></small>
      </div>
      
</div>

<div class="clearfix"></div>

<?php echo Form::close() ?>