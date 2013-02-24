<?php defined('SYSPATH') or die('No direct script access.') ?>

<h3><?php echo __('No Tags!'); ?></h3>

<?php echo HTML::anchor(Route::get('admin/tag')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add New Tag'), array('class' => 'btn btn-danger pull-right')) ?>

<p>
	<?php echo __('There are no Tags.') ?>
</p>
