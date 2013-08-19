<?php echo Form::open($action, array('id'=>'multi-delete-form ', 'class'=>'form')); ?>

<p>
	<?php echo __n( count($items),
			'Are you sure you want to delete this item?',
			'Are you sure you want to delete these items?'
			); ?>

	<?php echo __('This action cannot be undone.') ?>
</p>

<ul class="bulk-delete">
        
	<?php foreach($items as $id => $title): ?>
		<li><?php echo Text::plain($title) ?></li>
		<?php echo Form::hidden('items[]', $id ); ?>
	<?php endforeach ?>
</ul>

<div class="clearfix"></div>
<?php echo Form::submit('no', __('Cancel'), array('class' => 'btn')) ?> &nbsp;
<?php echo Form::submit('yes', __('Delete'), array('class' => 'btn btn-danger')) ?>

<?php echo Form::close(); ?>