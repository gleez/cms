<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php echo Form::open($action, array('id'=>'delete-form ', 'class'=>'form form-horizontal')); ?>
	<p class="warning"><?php _e("Unexpected bad things will happen if you don't read this!"); ?></p>

	<div class="control-group">
		<?php echo __('Are you sure you want to :del %title?', array(':del' => '<strong>'.__('delete').'</strong>', '%title' => $title)) ?>
	</div>

	<div class="form-actions">
		<?php echo Form::button('yes', __('Delete'), array('class' => 'btn btn-danger pull-right', 'type' => 'submit')); ?>
		<?php echo Form::button('no', __('Cancel'), array('class' => 'btn pull-right', 'type' => 'submit')); ?>
	</div>
<?php echo Form::close(); ?>