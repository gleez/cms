<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>

	<div class="help">
		<p><?php echo __('The tags module allows you to create free-tagging for everything, to define the various properties of your content, for example \'Countries\' or \'Colors\'.'); ?></p>
	</div>

	<?php echo HTML::anchor(Route::get('admin/tag')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i> '.__('Add New Tag'), array('class' => 'btn btn-success pull-right')) ?>
	<div class='clearfix'></div><br>

	<table id = "admin-list-tags" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["0", "desc"]]'>
		<thead>
			<tr>
				<th width="30%" class="sorting_desc"><?php echo __("Name"); ?></th>
				<th width="45%" class="sorting_desc"><?php echo __("Slug"); ?></th>
				<th width="15%" class="sorting_desc"><?php echo __("Type"); ?></th>
				<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="4" class="dataTables_empty"><?php echo __("Loading data from server"); ?></td>
			</tr>
		</tbody>
	</table>

<?php endif; ?>
