<?php defined('SYSPATH') or die('No direct script access.') ?>

<h3>No Terms!</h3>

<?php echo html::anchor(Route::get('admin/taxonomy')->uri(array('action' =>'add')), 'Add new Vocabulary') ?>

<p>
	<?php echo __('There are no Terms.') ?>
</p>


