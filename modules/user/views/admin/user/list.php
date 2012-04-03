<?php defined("SYSPATH") or die("No direct script access.") ?>

<div class="help">
	<?php echo __('Gleez CMS allows users to register, login, log out, maintain user profiles, etc. Users of the site may not use their own names to post content until they have signed up for a user account.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/user')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add New User'), array('class' => 'btn btn-danger pull-right')) ?>

    <table id="user-admin-list" class="table table-striped table-bordered">
      <thead>
        <tr>
          <th><?php echo __('Name') ?></th>
          <th><?php echo __('Email') ?></th>
	  <th><?php echo __('First Visit') ?></th>
	  <th><?php echo __('Last Visit') ?></th>
	  <th><?php echo __('Roles') ?></th>
	  <th><?php echo __('Status') ?></th>
          <th><?php echo __('Actions') ?></th>
        </tr>
      </thead>
         <?php foreach ($users as $i => $user): ?>
	   <tr id="user-row-<?php echo $user->id ?>" class="<?php echo Text::alternate('odd', 'even') ?>">
	  
            <td id="user-<?php echo $user->id ?>">
              <?php echo $user->name ?>
            </td>
            <td>
            	<?php echo $user->mail ?>
            </td>
            <td>
            	<?php echo Gleez::date($user->created) ?>
            </td>
            <td>
            	<?php echo ($user->login > 0) ? Gleez::date($user->login) : __('Never') ?>
            </td>
            <td>
		<ul class="user-roles">
		  <?php foreach ($user->roles() as $role): ?>
            		<li><?php echo Text::plain($role->name) ?></li>
		  <?php endforeach ?>
		</ul>
            </td>
            <td><span class="status-<?php echo $user->status == 1 ? 'active' : 'blocked' ?>">
		  <?php echo $user->status == 1 ? __('Active') : __('Blocked') ?>
		</span>
            </td>

            <td class="action">
               <?php echo html::anchor(Route::get('admin/user')->uri(array('action' => 'edit', 'id' => $user->id)), __('Edit'), array('class'=>'action-edit', 'title'=> __('Edit User'))) ?>
               <?php echo html::anchor(Route::get('admin/user')->uri(array('action' => 'delete', 'id' => $user->id)), __('Delete'), array('class'=>'action-delete', 'title'=> __('Delete User'))) ?>
            </td>
          </tr>
          <?php endforeach ?>
    </table>
    
  <?php echo $pagination ?>