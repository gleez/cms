<?php if ($is_datatables): ?>
	<?php echo $datatables->render(); ?>
<?php else:?>
	<?php Assets::datatables(); ?>

	<div class="help">
		<?php echo __('Gleez CMS allows users to register, login, log out, maintain user profiles, etc. Users of the site may not use their own names to post content until they have signed up for a user account.'); ?>
	</div>

	<?php echo HTML::anchor(Route::get('admin/user')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i> '.__('Add New User'), array('class' => 'btn btn-success pull-right')); ?>
	<div class='clearfix'></div><br>

	<table id = "admin-list-users" class="table table-striped table-bordered table-highlight" data-toggle="datatable" data-target="<?php echo $url?>" data-sorting='[["2", "desc"]]'>
		<thead>
			<tr>
				<th width="20%" class="sorting_desc"><?php echo __("Username"); ?></th>
				<th width="22%" class="sorting_desc"><?php echo __("Email"); ?></th>
				<th width="15%" data-columns='{"bSearchable":false}'><?php echo __("First Visit"); ?></th>
				<th width="15%" data-columns='{"bSearchable":false}'><?php echo __("Last Visit"); ?></th>
				<th width="12%" data-columns='{"bSortable":false, "bSearchable":false}'><?php echo __('Roles') ?></th>
				<th width="8%" data-columns='{"bSearchable":false, "sClass": "status"}'><?php echo __("Status"); ?></th>
				<th width="8%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="7" class="dataTables_empty"><?php echo __("Loading data from server"); ?></td>
			</tr>
		</tbody>
	</table>

<?php endif; ?>