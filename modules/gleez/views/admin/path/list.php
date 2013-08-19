<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	<div class="help">
		<p><?php echo __('An alias defines a different name for an existing URL path &mdash; for example, the alias \'about\' for the URL path \'page/1\'. A URL path can have multiple aliases.'); ?></p>
	</div>

	<?php echo HTML::anchor($add_url, '<i class="icon-plus icon-white"></i> '.__('Add Alias'), array('class' => 'btn btn-success pull-right')) ?>
	<div class='clearfix'></div><br>

	<table id="admin-list-paths" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["1", "desc"]]'>
		<thead>
		<tr>
			<th width="50%" class="sorting_desc"><?php echo __("URL Path"); ?></th>
			<th width="40%" class="sorting_desc"><?php echo __("Alias"); ?></th>
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
