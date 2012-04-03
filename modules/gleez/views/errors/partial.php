<?php defined('SYSPATH') or die('No direct script access.'); ?>

	<?php if ( ! empty($errors)): ?>
		<div id="formerrors" class="errorbox">
			<h3>Ooops!</h3>
			<ol>
				<?php foreach($errors as $field => $message): ?>
					<li> <?php echo $message ?> </li>
				<?php endforeach ?>
			</ol>
		</div>
	<?php endif ?>