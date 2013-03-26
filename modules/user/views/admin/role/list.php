<?php defined("SYSPATH") or die("No direct script access.") ?>

<div class="help">
	<?php echo __('Roles allow you to fine tune the security and administration of Gleez CMS. A role defines a group of users that have certain privileges as defined in user permissions. Examples of roles include: anonymous user, authenticated user, moderator, administrator and so on. In this area you will define the role names of the various roles.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/role')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add New Role'), array('class' => 'btn btn-danger pull-right')) ?>

<div class='clearfix'></div><br />
    <table id="role-admin-list" class="table table-striped table-bordered">
      <thead>
        <tr>
          <th><?php echo __("Name") ?></th>
          <th><?php echo __("Description") ?></th>
	   <th><?php echo __("Special") ?></th>
          <th><?php echo __("Actions") ?></th>
        </tr>
      </thead>
         <?php foreach ($roles as $i => $role): ?>
          <tr id="role-row-<?php echo $role->id ?>" class="<?php echo text::alternate("odd", "even") ?>">
            <td id="role-<?php echo $role->id ?>">
              <?php echo HTML::chars($role->name) ?>
            </td>
            <td>
            	<?php echo HTML::chars($role->description) ?>
            </td>
            <td><span class="status-<?php echo $role->special == 1 ? 'active' : 'blocked' ?>">
            	<?php echo $role->special == 1 ? '<i class="icon-ok-sign"></i>' : '<i class="icon-ban-circle"></i>'; ?>
            </td>

            <td class="action">
		<?php if($role->special): ?>
			<i class="icon-pencil"></i>  <i class="icon-remove"></i>
		<?php else: ?>
			<?php echo HTML::anchor(Route::get('admin/role')->uri(array('action' => 'edit', 'id' => $role->id)), '<i class="icon-edit"></i>', array('class'=>'action-edit', 'title'=> __('Edit Role'))) ?>
			<?php echo HTML::anchor(Route::get('admin/role')->uri(array('action' => 'delete', 'id' => $role->id)), '<i class="icon-trash"></i>', array('class'=>'action-delete', 'title'=> __('Delete Role'))) ?>
            	<?php endif ?>
		<?php echo HTML::anchor(Route::get('admin/permission')->uri(array('action' => 'role', 'id' => $role->id)), '<i class="icon-lock"></i>', array('class'=>'action-edit', 'title'=> __('Edit Permissions'))) ?>

		</td>
          </tr>
          <?php endforeach ?>
    </table>
    
  <?php echo $pagination ?>