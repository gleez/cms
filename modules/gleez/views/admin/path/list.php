<?php defined('SYSPATH') or die('No direct script access.') ?>

<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	<div class="help">
		<?php echo __('An alias defines a different name for an existing URL path - for example, the alias \'about\' for the URL path \'page/1\'. A URL path can have multiple aliases.'); ?>
	</div>

	<?php echo HTML::anchor(Route::get('admin/path')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add Alias'), array('class' => 'btn btn-danger pull-right')) ?>
	<div class='clearfix'></div>

	<table id = "admin-list-paths" class="table table-striped table-bordered" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["1", "desc"]]'>
		<thead>
			<tr>
				<th width="45%" class="sorting_desc"><?php echo __("Path"); ?></th>
				<th width="45%" class="sorting_desc"><?php echo __("Alias"); ?></th>
				<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="7" class="dataTables_empty"><?php echo __("Loading data from server"); ?></td>
			</tr>
		</tbody>
	</table>

<?php endif; ?>