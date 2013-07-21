<?php defined('SYSPATH') OR die('No direct script access allowed.') ?>

<?php if (isset($messages) AND !empty($messages)): ?>
	<?php foreach ($messages as $message): ?>
		<div class="alert alert-<?php echo $message->type ?>">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<?php echo $message->text ?>
		</div>
        <?php endforeach; ?>
<?php endif;?>