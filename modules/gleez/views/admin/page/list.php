<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	<div class="help">
		<p><?php echo __('View, edit, and delete your site\'s pages.'); ?></p>
	</div>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="content">
		<?php echo Form::open($action, array('id'=>'admin-page-form', 'class'=>'no-form')); ?>
			<fieldset class="bulk-actions form-actions rounded">
				<div class="row">
					<div class="form-group col-xs-7 col-sm-3 col-md-2">
						<div class="control-group <?php echo isset($errors['operation']) ? 'has-error': ''; ?>">
							<?php echo Form::select('operation', Post::bulk_actions(TRUE, 'page'), '', array('class' => 'form-control col-md-5')); ?>
						</div>
					</div>
					<div class="form-group col-xs-5 col-sm-2 col-md-2">
						<?php echo Form::submit('page-bulk-actions', __('Apply'), array('class'=>'btn btn-default col-md-5')); ?>
					</div>
					<div class="form-group col-xs-6 col-sm-7 col-md-8 form-actions-right">
						<?php echo HTML::anchor(Route::get('page')->uri(array('action' => 'add')), '<i class="fa fa-plus fa-white"></i> '.__('New entry'), array('class'=>'btn btn-success pull-right')); ?>
					</div>
				</div>
			</fieldset>
			<table id="admin-list-pages" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["4", "desc"]]'>
				<thead>
					<tr>
						<th width="5%" data-columns='{"bSortable":false, "bSearchable":false}'> # </th>
						<th width="40%"><?php echo __('Title'); ?></th>
						<th width="20%" data-columns='{"bSearchable":false}'><?php echo __('Author'); ?></th>
						<th width="10%" data-columns='{"bSearchable":false, "sClass": "status"}'><?php echo __('Status'); ?></th>
						<th width="12%" data-columns='{"bSearchable":false}'><?php echo __('Updated'); ?></th>
						<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan="6" class="dataTables_empty"><?php echo __("Loading data from server"); ?></td>
					</tr>
				</tbody>
			</table>
		<?php echo Form::close(); ?>
	</div>

<?php endif; ?>
