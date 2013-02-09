<?php defined('SYSPATH') or die('No direct script access.') ?>

<h3>No Paths!</h3>
<?php echo Html::anchor(Route::get('admin/path')->uri(array('action' =>'add')), 'Add alias') ?>

<p>
	<?php echo __('There are no Paths.') ?>
</p>