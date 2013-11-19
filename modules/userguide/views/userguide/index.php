<h1><?php _e('User Guide')?></h1>

<p><?php _e('The following modules have userguide pages:')?></p>

<?php if ( ! empty($modules)): ?>

	<?php foreach($modules as $url => $options): ?>

		<p>
			<strong><?php echo HTML::anchor(Route::get('docs/guide')->uri(array('module' => $url)), $options['name'], NULL, NULL, TRUE) ?></strong> -
			<?php _e($options['description']) ?>
		</p>

	<?php endforeach; ?>

<?php else: ?>

	<p class="error"><?php _e("I couldn't find any modules with userguide pages.")?></p>

<?php endif; ?>
