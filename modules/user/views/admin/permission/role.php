<div class="help">
	<p><?php echo __('Permissions let you control what users can do on your site. Each user role (defined on the :user-roles) has its own set of permissions. Permissions also allow trusted users to share the administrative burden of running a busy site.', array(':user-roles' => HTML::anchor(Route::get('admin/role')->uri(), __('user roles page')))); ?></p>
</div>

<?php echo Form::open(Route::get('admin/permission')->uri(array('action' => 'role', 'id' => $id)), array('id'=>'permission-form ', 'class'=>'permission-form form')) ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<table id="permissions" class="table table-bordered table-striped table-highlight">
		<thead>
			<tr>
				<th><?php echo __('Permission') ?></th>
				<th><?php  _e('Role :role', array(':role' => ucwords(Text::plain($role->name)))) ?></th>
			</tr>
		</thead>
		<?php
			foreach ($perms as $row)
			{
				$role_perms[$row->rid][$row->permission] = TRUE;
			}
		?>

		<?php foreach ($permissions as $key => $access_names): ?>
			<tr class="permission-group">
				<td class="permission-key" width="30%" colspan="2">
					<?php _e('Subsystem'); ?>:&nbsp;<span class="label label-info"><?php _e(ucwords(Text::plain($key))) ?></span>
				</td>
			</tr>

			<?php foreach ($access_names as $perm => $name): ?>
				<tr class="<?php echo Text::alternate("odd", "even") ?>">
					<td class="permission" >
						<div class="permission-item" id="permission-<?php echo str_replace(' ', '-', $perm) ?>" >
							<strong><?php echo ucwords($name['title']) ?></strong>
							<div class="description">
								<p class="muted"><?php echo Text::plain($name['description'])?></p>
								<?php if (!empty($name['restrict access'])): ?>
									<cite class="permission-warning text-warning">
										<?php echo __('Warning! Give to trusted roles only; this permission has security implications.'); ?>
									</cite>
								<?php endif; ?>
							</div>
						</div>

					</td>
					<td class="role-checkbox">
						<?php
							echo Form::checkbox("role[$key$perm][name]", Text::plain($perm), isset($role_perms[$role->id][$perm]));
							echo Form::hidden("role[$key$perm][module]", $key );
							echo Form::hidden("role[$key$perm][id]", $role->id);
						?>
					</td>
				</tr>
			<?php endforeach ?>
		<?php endforeach ?>

	</table>
	<?php echo Form::submit('permissions', __('Save Permissions'), array('class' => 'btn btn-success pull-right')) ?>
	<div class="clearfix"></div><br>
	<?php echo Form::close() ?>