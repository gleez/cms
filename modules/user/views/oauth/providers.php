<?php Assets::css('user', 'media/css/user.css', array('weight' => 2)); ?>

<ul id="oauth-providers">
	<li class="oprovider base">
		<?php
			$url = Route::get('user')->uri( array('action' => 'login'));
			$url .= URL::query( array('destination' => Request::current()->uri()));

			echo HTML::anchor($url, __('Log In'), array('class' => 'base', 'title' =>__('Login with :provider account', array(':provider' => $site_name))));
			unset($url);
		?>
	</li>

	<?php foreach ($providers as $name => $provider): ?>
		<li class="oprovider <?php echo $name; ?>">
			<?php
				$url = $provider['url'] . URL::query( array('destination' => Request::current()->uri()) );

				echo HTML::anchor($url, __('Log In'), array('class' => $name, 'title' =>__('Login with :provider account', array(':provider' => ucfirst($name)))));
				unset($url);
			?>
		</li>
	<?php endforeach; ?>
</ul>
<div class="clearfix"></div><br>