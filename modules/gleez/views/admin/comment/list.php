<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	<div class="help">
		<p><?php echo __('View, edit, and delete your site\'s comments.'); ?></p>
	</div>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="content">
		<?php echo Form::open($action, array('id'=>'admin-comment-form', 'class'=>'no-form')); ?>
			<fieldset class="bulk-actions form-actions rounded">
				<div class="row-fluid">
					<div class="span12">
						<div class="control-group <?php echo isset($errors['operation']) ? 'error': ''; ?>">
							<?php echo Form::select('operation', $bulk_actions, '', array('class' => 'span6')); ?>
							<?php echo Form::submit('comment-bulk-actions', __('Apply'), array('class'=>'btn')); ?>
						</div>
					</div>
				</div>
			</fieldset>
			<table id="admin-list-comments" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["1","desc"],["2","asc"],["4","desc"]]'>
				<thead>
					<tr>
						<th width="5%">#</th>
						<th width="20%"><?php echo __('Subject'); ?></th>
						<th width="15%"><?php echo __('Author'); ?></th>
						<th width="33%"><?php echo __('Posted In');?></th>
						<th width="18%"><?php echo __('Created'); ?></th>
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
	</div>
<?php endif; ?>