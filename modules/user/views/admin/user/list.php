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
			    User::roles($user),
			    $user->status == 1 ? '<span class="status-active"><i class="icon-ok-sign"></i></span>' : '<span class="status-blocked"><i class="icon-ban-circle"></i></span>',
                            HTML::anchor(Route::get('admin/user')->uri(array('action' => 'edit', 'id' => $user->id)), '<i class="icon-edit"></i>', array('class'=>'action-edit', 'title'=> __('Edit User'))) .
                            HTML::anchor(Route::get('admin/user')->uri(array('action' => 'delete', 'id' => $user->id)), '<i class="icon-trash"></i>', array('class'=>'action-edit', 'title'=> __('Delete User')))
                        ));
                }
                echo $datatables->render();
        ?>
<?php else:?>

<?php Assets::datatables(); ?>
<div class="help">
	<?php echo __('Gleez CMS allows users to register, login, log out, maintain user profiles, etc. Users of the site may not use their own names to post content until they have signed up for a user account.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/user')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add New User'), array('class' => 'btn btn-danger pull-right')) ?>

<div class='clearfix'></div><br />

	<table id = "admin-list-users" class="table table-striped table-bordered" data-toggle="datatable" data-lengthchange="false" data-target="<?php echo $url?>" data-sorting='[["2", "desc"]]'>
		<thead>
			<tr>
				<th width="20%" class="sorting_desc"><?php echo __("Username"); ?></th>
				<th width="23%" class="sorting_desc"><?php echo __("Email"); ?></th>
				<th width="15%" data-columns='{"bSearchable":false}'><?php echo __("First Visit"); ?></th>
                                <th width="15%" data-columns='{"bSearchable":false}'><?php echo __("Last Visit"); ?></th>
				<th width="12%" data-columns='{"bSortable":false, "bSearchable":false}'><?php echo __('Roles') ?></th>
				<th width="8%" data-columns='{"bSearchable":false, "sClass": "status"}'><?php echo __("Status"); ?></th>
				<th width="6%" data-columns='{"bSortable":false, "bSearchable":false}'></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="7" class="dataTables_empty"><?php echo __("Loading data from server"); ?></td>
			</tr>
		</tbody>
	</table>

<?php endif; ?>