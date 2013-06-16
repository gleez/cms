<?php defined('SYSPATH') or die('No direct script access.') ?>

<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	<div class="help">
		<p><?php echo __('View, edit, and delete your site\'s comments.'); ?></p>
	</div>

	<?php echo Form::open(Route::get('admin/comment')->uri(array('action' => 'process')).$destination,
                               array('id'=>'admin-comment-form', 'class'=>'no-form')); ?>

	<div class="thumbnail">
		<legend><?php echo __('Bulk Actions'); ?></legend>
		<div class="control-group edit-operation <?php echo isset($errors['operation']) ? 'error': ''; ?>">
			<?php echo Form::select('operation', $bulk_actions, '', array('class' => 'input-xlarge')); ?>
			<?php echo Form::submit('comment-bulk-actions', __('Apply'), array('class'=>'btn btn-danger')); ?>
		</div>
	</div><br>

	<table id = "admin-list-paths" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["1","desc"],["2","asc"],["4","desc"]]'>
		<thead>
		<tr>
			<th width="5%">#</th>
			<th width="20%"><?php echo __("Subject"); ?></th>
			<th width="20%"><?php echo __('Author'); ?></th>
			<th width="35%"><?php echo __('Posted In');?></th>
			<th width="10%"><?php echo __('Created'); ?></th>
			<th width="5%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			<th width="5%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
		</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="7" class="dataTables_empty"><?php echo __("Loading data from server"); ?></td>
			</tr>
		</tbody>
	</table>
	<?php echo Form::close(); ?>
<?php endif; ?>