<?php defined('SYSPATH') OR die('No direct script access.'); ?>

	<?php if ( ! empty($errors)): ?>
		<div class="alert alert-error alert-block">
			<h4><?php echo __('Error'); ?></h4>
				<?php foreach($errors as $field => $message): ?>
					<p><?php echo $message ?></p>
				<?php endforeach ?>
		</div>
	<?php endif; ?>
