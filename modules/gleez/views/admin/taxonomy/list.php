<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	<div class="help">
		<p><?php _e('Categories are needed for grouping content. Categories are grouped by category groups. For example, a category group called "Fruit" would contain the categories "Apple" and "Banana".'); ?></p>
	</div>
	<div class="row">
		<div class="col-sm-12 form-actions-right">
			<?php echo HTML::anchor($add_url, '<i class="fa fa-plus fa-white"></i> '.__('Add New Group'), array('class' => 'btn btn-success pull-right')) ?>
		</div>
	</div>

	<table id="admin-list-vocabs" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["0", "desc"]]'>
		<thead>
		<tr>
			<th width="60%" class="sorting_desc"><?php _e('Group name and description'); ?></th>
			<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			<th width="10%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
		</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="3" class="dataTables_empty"><?php _e('Loading data from server'); ?></td>
			</tr>
		</tbody>
	</table>

<?php endif; ?>
