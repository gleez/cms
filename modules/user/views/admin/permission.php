<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php $r_count =  count($roles); ?>

<div class="help">
	<?php echo __('Permissions let you control what users can do on your site. Each user role (defined on the :user-roles) has its own set of permissions. Permissions also allow trusted users to share the administrative burden of running a busy site.', array(':user-roles' => HTML::anchor(Route::get('admin/role')->uri(), 'user roles page'))); ?>
</div>

<?php echo Form::open( Route::get('admin/permission')->uri()  ) ?>

	<?php if ( ! empty($errors)): ?>
		<div id="formerrors" class="errorbox">
			<h3>Ooops!</h3>
			<ol>
				<?php foreach($errors as $field => $message): ?>
					<li>	
						<?php echo $message ?>
					</li>
				<?php endforeach ?>
			</ol>
		</div>
	<?php endif ?>

    <table id="permissions" class="table table-striped table-bordered">
      <thead>
        <tr>
          <th><?php echo __('Permission') ?></th>
		<?php foreach ($roles as $i => $role): ?>
          		<th class="row-checkbox"><?php echo ucwords(Text::plain($role->name)) ?></th>
		<?php endforeach ?>
        </tr>
      </thead>
	<?php 
		foreach ($perms as $row)
		{
        		$role_perms[$row->rid][$row->permission] = TRUE;
      		}
	?>

	<?php foreach ($permissions as $key => $access_names): ?>
		<tr id="permission-group" class="<?php echo text::alternate("odd", "even") ?>">
		    <td class="permission-key" width="30%" colspan="<?php echo $r_count +1 ?>">
              		<?php echo ucwords(Text::plain($key)) ?>
            	    </td>
		</tr>
		
		<?php foreach ($access_names as $perm => $name): ?>
          		<tr class="<?php echo text::alternate("odd", "even") ?>">
            			<td class="permission" >
					<div class="permission-item" id="permission-<?php echo str_replace(' ', '-', $perm) ?>" >
						<?php echo ucwords($name['title']) ?>
						<div class="description">
							<?php echo Text::plain($name['description'])?>
							<?php if (!empty($name['restrict access'])): ?>
							<cite class="permission-warning">
								<?php echo __('Warning: Give to trusted roles only; this permission has security implications.');?>
							</cite>
							<?php endif; ?>
						</div>
					</div>

            			</td>
				<?php foreach ($roles as $i => $role): ?>
					<td class="role-checkbox">
						<?php echo Form::checkbox("roles[$role->id][$key$perm$i][name]", Text::plain($perm), isset($role_perms[$role->id][$perm])); ?>		
						<?php //echo Form::hidden("role[$role->id][$key$perm$i][name]", $perm) ?>
						<?php echo Form::hidden("roles[$role->id][$key$perm$i][module]", $key ); ?>
						<?php echo Form::hidden("roles[$role->id][$key$perm$i][id]", $role->id) ?>
					</td>
				<?php endforeach ?>
         		 </tr>
		<?php endforeach ?>
	<?php endforeach ?>
        
    </table>
	<?php echo Form::submit('permissions', __('Save Permissions'), array('class' => 'btn btn-primary btn-large')) ?>
	<?php echo Form::close() ?>