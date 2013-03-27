<?php defined('SYSPATH') or die('No direct access to script'); ?>

<?php if( Request::is_datatables() ): ?>
        <?php
                foreach ($datatables->result() as $user)
                {
			$datatables->add_row(array
                        (
                            Text::plain($user->name),
                            Text::plain($user->mail),
			    date('M d, Y',$user->created),
                            ($user->login > 0) ? date('M d, Y',$user->login) : __('Never'),
			    $user->status == 1 ? '<i class="icon-ok-sign"></i>' : '<i class="icon-ban-circle"></i>',
                            HTML::anchor(Route::get('admin/user')->uri(array('action' => 'edit', 'id' => $user->id)), '<i class="icon-edit"></i>', array('class'=>'action-edit', 'title'=> __('Edit User'))) .
                            HTML::anchor(Route::get('admin/user')->uri(array('action' => 'delete', 'id' => $user->id)), '<i class="icon-trash"></i>', array('class'=>'action-edit', 'title'=> __('Delete User')))
                        ));
                }
                echo $datatables->render();
        ?>
<?php else:?>

	<?php Assets::datatables(); ?>

	<table class="table table-striped" data-toggle="datatable" data-target="<?php echo $url?>" data-aasorting='[["2", "desc"]]'>
		<thead>
			<tr>
				<th width="25%"><?php echo __("Username"); ?></th>
				<th width="25%"><?php echo __("Email"); ?></th>
				<th width="15%" data-aocolumns='{"bSearchable":false}'><?php echo __("First Visit"); ?></th>
                                <th width="15%" data-aocolumns='{"bSearchable":false}'><?php echo __("Last Visit"); ?></th>
				<th width="5%" data-aocolumns='{"bSearchable":false}'><?php echo __("Status"); ?></th>
				<th width="7%" data-aocolumns='{"bSortable":false, "bSearchable":false}'></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="6" class="dataTables_empty"><?php echo __("Loading data from server"); ?></td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>