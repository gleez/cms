<?php defined('SYSPATH') OR die('No direct script access.') ?>

<h3><?php echo __('No Paths!'); ?></h3>
<?php echo HTML::anchor(Route::get('admin/path')->uri(array('action' =>'add')), 'Add alias') ?>

<p>
	<?php echo __('There are no Paths.') ?>
</p>
