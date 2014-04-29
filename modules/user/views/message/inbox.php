<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	<div class="row">
		<div class="col-md-12">
			<?php include Kohana::find_file('views', 'errors/partial'); ?>
			<div class="content">
				<?php echo Form::open($action, array('class'=>'form-inline')); ?>
					<fieldset class="bulk-actions form-actions rounded">
						<div class="row">
							<div class="form-group col-xs-8 col-sm-3 col-md-3">
								<div class="control-group <?php echo isset($errors['operation']) ? 'has-error': ''; ?>">
									<?php echo Form::select('operation', PM::bulk_actions(TRUE), '', array('class' => 'form-control')); ?>
								</div>
							</div>
							<div class="form-group col-xs-4 col-sm-2 col-md-2">
								<?php echo Form::submit('message-bulk-actions', __('Apply'), array('class'=>'btn btn-primary')); ?>
							</div>
							<div class="form-group col-xs-6 col-sm-7 col-md-7 form-actions-right">
								<?php echo HTML::anchor(Route::get('user/message')->uri(array('action' => 'compose')), '<i class="fa fa-plus fa-white"></i> '.__('Compose'), array('class'=>'btn btn-success')); ?>
							</div>
						</div>
					</fieldset>
					<table id="user-message-inbox" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["5", "desc"]]'>
						<thead>
							<tr>
								<th width="5%" data-columns='{"bSortable":false, "bSearchable":false}'> # </th>
								<th width="15%" data-columns='{"bSearchable":false}'><?php _e('Sender'); ?></th>
								<th width="60%"><?php _e('Message'); ?></th>
								<th width="12%" data-columns='{"bSearchable":false}'><?php _e('Sent'); ?></th>
								<th width="8%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="5" class="dataTables_empty"><?php _e('Loading data from server'); ?></td>
							</tr>
						</tbody>
					</table>
				<?php echo Form::close(); ?>
			</div>
		</div>
	</div>
<?php endif; ?>
