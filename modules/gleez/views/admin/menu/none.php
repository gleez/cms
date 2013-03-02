<?php defined('SYSPATH') OR die('No direct script access.') ?>

<h3><?php echo __('No Menus!'); ?></h3>
<?php echo HTML::anchor(Route::get('admin/menu')->uri(array('action' => 'add')), '<i class="icon-plus icon-white"></i> ' . __('Add Menu'), array('class' => 'btn btn-primary pull-right')) ?>

<p>
	<?php echo __('There are no Menus.') ?>
</p>
