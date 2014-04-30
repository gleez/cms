<?php foreach ($groups as $group => $status): ?>
 * <?php echo $group ?> <?php echo (($status !== NULL) ? ($status['timestamp'].' '.(( ! empty($status['description'])) ? ('('.$status['description'].')') : '')) : 'Not installed'); ?>

<?php endforeach; ?>