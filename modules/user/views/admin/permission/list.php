<div class="help">
	<p><?php echo __('Permissions let you control what users can do on your site. Each user role (defined on the :user-roles) has its own set of permissions. Permissions also allow trusted users to share the administrative burden of running a busy site.', array(':user-roles' => HTML::anchor(Route::get('admin/role')->uri(), __('user roles page')))); ?></p>
</div>

<?php echo Form::open(Route::get('admin/permission')->uri(), array('id'=>'permission-form ', 'class'=>'permission-form form')) ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<table id="permissions" class="table table-striped table-bordered table-highlight">
		<thead>
			<tr>
				<th><?php echo __('Permission') ?></th>
				<?php foreach ($roles as $i => $role): ?>
					<th class="row-checkbox">
						<?php
							// @todo But if they will be 30, 50?...
							echo ucwords(Text::plain($role->name));
						?>
					</th>
				<?php endforeach ?>
			</tr>
		</thead>

	<?php 
		foreach ($perms as $row)
		{
			$role_perms[$row->rid][$row->permission] = TRUE;
		}
	?>

		<tbody>
	
		<?php foreach ($permissions as $key => $access_names): ?>
			<tr class="permission-group">
				<td class="permission-key" width="30%" colspan="<?php echo $count +1 ?>">
					<?php echo ucwords(Text::plain($key)) ?>
				</td>
			</tr>
	
			<?php foreach ($access_names as $perm => $name): ?>
				<tr class="<?php echo Text::alternate("odd", "even") ?>">
					<td class="permission" >
						<div class="permission-item" id="permission-<?php echo str_replace(' ', '-', $perm) ?>" >
							<strong><?php echo ucwords($name['title']) ?></strong>
							<div class="description">
								<p class="muted"><?php echo Text::plain($name['description'])?></p>
								<?php if ( ! empty($name['restrict access'])): ?>
									<cite class="permission-warning text-warning">
										<?php echo __('Warning! Give to trusted roles only; this permission has security implications.'); ?>
									</cite>
								<?php endif; ?>
							</div>
						</div>
	
					</td>
					<?php foreach ($roles as $i => $role): ?>
						<td class="role-checkbox">
							<?php echo Form::checkbox("roles[$role->id][$key$perm$i][name]", Text::plain($perm), isset($role_perms[$role->id][$perm])); ?>		
							<?php echo Form::hidden("roles[$role->id][$key$perm$i][module]", $key ); ?>
							<?php echo Form::hidden("roles[$role->id][$key$perm$i][id]", $role->id) ?>
						</td>
					<?php endforeach ?>
					 </tr>
			<?php endforeach ?>
		<?php endforeach ?>
		</tbody>
		
	</table>

	<?php echo Form::close() ?>