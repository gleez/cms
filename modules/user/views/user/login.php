<?php defined('SYSPATH') or die('No direct script access.'); ?>
<?php Assets::css('user', 'media/css/user.css', array('weight' => 2)); ?>

<?php $destin = isset($_GET['destination']) ? $_GET['destination'] : Request::initial()->uri();
      echo Form::open(Route::get('user')->uri(array('action' => 'login')). URL::query( array('destination' => $destin))); ?>

      <?php if ( ! empty($errors)): ?>
	    <div id="formerrors" class="errorbox">
		  <h3>Ooops!</h3>
		  <ol>
			<?php foreach($errors as $field => $message): ?>
			      <li> <?php echo $message; ?> </li>
			<?php endforeach ?>
		  </ol>
	    </div>
      <?php endif ?>

<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
      <?php //echo Form::label('username', 'Username/Email', array('class' => 'control-label')) ?>
      <div class="controls">
	    <?php echo Form::input('name', $post->name, array('class' => 'input-large', 'placeholder' => __('Email'))); ?>
      </div>
</div>

<div class="control-group <?php echo isset($errors['password']) ? 'error': ''; ?>">
      <?php //echo Form::label('password', 'Password', array('class' => 'control-label')) ?>
      <div class="controls">
	    <?php echo Form::password('password', NULL, array('class' => 'input-large', 'placeholder' => __('Password'))); ?>
      </div>
</div>

      <?php echo Form::checkbox('remember',TRUE) . ' ' . __('Stay Signed in'); ?>
      <?php echo Form::submit('login', __('Log In'), array('class' => 'btn btn-primary')) ?>
      
      <ul>
	    <li><?php echo HTML::anchor('user/reset/password', __('Forgot Password?')); ?></li>
	    <li><?php echo __("Don't have an account? :url", array(':url' => HTML::anchor('user/register', __('Create One.'))) ); ?></li>
      </ul>
      
      <?php if($providers): ?>
		<ul id="auth-providers">
			<?php echo __('Login with any of these providers:');?> <br>
		<?php foreach($providers as $provider => $key): ?>
			<li class="provider <?php echo $provider; ?>">
				<?php
				$url = Route::get('user/oauth')->uri( array('controller' => $provider, 'action' => 'login'));
					echo HTML::anchor($url, ucfirst($provider), array('id' => $provider,
						'title' =>__('Login with :provider', array(':provider' => $provider) ) ) );
				?>
			</li>
		<?php endforeach ?>
			<br><small><?php echo __('Fast, safe & secure way!');?></small>
		</ul>
      <?php endif; ?>
      
<?php echo Form::close() ?>
