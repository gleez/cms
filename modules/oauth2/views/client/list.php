<?php if (Request::is_datatables()): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	
	<div class="wellact">
	<table id="datatable-oaclient" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["3", "desc"]]'>
		<thead>
			<tr>
				<th width="20%"><?php echo __("Title"); ?></th>
				<th width="30%"><?php echo __("Client Id"); ?></th>
				<th width="20%"><?php echo __("Created By"); ?></th>
                <th width="20%" data-columns='{"bSearchable":false}'><?php echo __("Created On"); ?></th>
				<th width="10%"  data-columns='{"bSortable":false, "bSearchable":false}'></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="5" class="dataTables_empty">Loading data from server</td>
			</tr>
		</tbody>
	</table>
	</div>
<?php endif; ?>