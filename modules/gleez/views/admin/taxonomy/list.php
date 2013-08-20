<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>
	<div class="help">
		<p><?php echo __('Taxonomy is for categorizing content. Terms are grouped into vocabularies. For example, a vocabulary called "Fruit" would contain the terms "Apple" and "Banana".'); ?></p>
	</div>

	<?php echo HTML::anchor($add_url, '<i class="icon-plus icon-white"></i> '.__('Add New Vocabulary'), array('class' => 'btn btn-success pull-right')) ?>
	<div class='clearfix'></div><br>

	<table id="admin-list-vocabs" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["0", "desc"]]'>
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