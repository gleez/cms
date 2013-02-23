<?php defined('SYSPATH') OR die('No direct script access.') ?>

<h3><?php echo __('No Pages!'); ?></h3>

<p>
	<?php echo __('There are no Pages that have been published.'); ?>
</p>

<?php echo HTML::anchor(Route::get('page')->uri(array('action' => 'add')), '<i class="icon-plus icon-white"></i> ' . __('Add Page'), array('class' => 'btn btn-primary pull-right')) ?>
<div class='clearfix'></div><br/>