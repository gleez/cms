<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<?php echo Form::open($action, array('id'=>'delete-form ', 'class'=>'form')); ?>

	<p><?php echo __('Are you sure you want to delete %title?', array('%title' => $title)) ?></p>
	<p><?php echo __('This action cannot be undone.'); ?></p>

	<div class="clearfix"></div>
	<?php echo Form::submit('yes', __('Yes'), array('class' => 'btn btn-primary')) ?>  &nbsp;
	<?php echo Form::submit('no', __('No'), array('class' => 'btn btn-danger')) ?>

<?php echo Form::close() ?>