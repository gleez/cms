<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<div class="help">
	<?php echo __('Permissions let you control what users can do on your site. User defined permission override role based permissions.'); ?>
</div>

<?php echo Form::open( Route::get('admin/permission')->uri(array('action' => 'user', 'id' => (isset($post->id) ? $post->id : 0)))  ) ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<table id="permissions" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th></th>
				<th></th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($permissions as $key => $access_names): ?>
			<tr id="permission-group" class="<?php echo Text::alternate("odd", "even") ?>">
				<td class="permission-key" width="30%" colspan="2">
					<?php echo ucwords(Text::plain($key)) ?>
				</td>
			</tr>
			<?php foreach ( $access_names as $perm => $name): ?>
			<tr>
				<td>
					<?php echo ucwords($name['title']) ?>
					<div class="description">
						<?php echo Text::plain($name['description'])?>
					</div>
				</td>
				<td>
					<?php echo Form::label($key."[$perm]", Form::radio("perms[$perm]", ACL::PERM_ALLOW, (isset($oldperms[$perm]) AND $oldperms[$perm] == ACL::PERM_ALLOW) ? TRUE : FALSE). __('Allow'), array('class' => 'radio inline'))?>
					<?php echo Form::label($key."[$perm]", Form::radio("perms[$perm]", ACL::PERM_DENY, (isset($oldperms[$perm]) AND $oldperms[$perm] == ACL::PERM_DENY) ? TRUE : FALSE). __('Disallow'), array('class' => 'radio inline'))?>
					<?php echo Form::label($key."[$perm]", Form::radio("perms[$perm]", '', (isset($oldperms[$perm])) ? FALSE : TRUE). __('Inherit'), array('class' => 'radio inline'))?>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</tbody>
		
	</table>
	<?php echo Form::submit('permissions', __('Save Permissions'), array('class' => 'btn btn-primary btn-large')) ?>
	<?php echo Form::close() ?>