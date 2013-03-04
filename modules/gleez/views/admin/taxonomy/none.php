<?php defined('SYSPATH') OR die('No direct script access.') ?>

<h3><?php echo __('No Vocabulary!'); ?></h3>
<?php echo HTML::anchor(Route::get('admin/taxonomy')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i> ' . __('Add new Vocabulary'), array('class' => 'btn btn-primary pull-right')); ?>

<p>
	<?php echo __('There are no Terms.') ?>
</p>


