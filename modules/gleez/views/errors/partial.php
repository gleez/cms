<?php if ( ! empty($errors)): ?>
	<div class="alert alert-error alert-block">
		<h4 class="alert-heading"><?php echo __('Error'); ?></h4>
		<?php foreach($errors as $field => $messages): ?>
			<?php if (is_array($messages)): ?>
				<?php foreach($messages as $message): ?>
					<p><?php echo $message ?></p>
				<?php endforeach ?>
			<?php else: ?>
				<p><?php echo $messages ?></p>
			<?php endif; ?>
		<?php endforeach ?>
	</div>
<?php endif; ?>
