<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	<div class="help">
		<p><?php echo __('The Menu wizard provides an interface for managing menus. A menu is a hierarchical collection of links, which can be within or external to the site, generally used for navigation.', array(':menus' => 'admin/menu')); ?></p>
	</div>

	<?php echo HTML::anchor($add_url, '<i class="icon-plus icon-white"></i> '.__('Add Menu'), array('class' => 'btn btn-success pull-right')); ?>
	<div class='clearfix'></div><br>

	<table id="admin-list-menus" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["0", "desc"]]'>
		<thead>
		<tr>
			<th width="60%" class="sorting_desc"><?php echo __("Title"); ?></th>
			<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
		</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="3" class="dataTables_empty"><?php echo __("Loading data from server"); ?></td>
			</tr>
		</tbody>
	</table>

<?php endif; ?>