<div class="help">
	<p><?php echo __('Permissions let you control what users can do on your site. User defined permission override role based permissions.'); ?></p>
</div>

<?php echo Form::open($action, array('id'=>'permission-form ', 'class'=>'permission-form form')); ?>

<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<table id="permissions" class="table table-striped table-bordered table-highlight">
		<thead>
		<tr>
			<th><?php echo __('Module'); ?></th>
			<th><?php echo __('Permission'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($permissions as $key => $access_names): ?>
			<tr class="permission-group">
				<td class="permission-key" width="30%" colspan="2">
					<?php echo ucwords(Text::plain($key)) ?>
				</td>
			</tr>
			<?php foreach ($access_names as $perm => $name): ?>
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
	<?php echo Form::submit('permissions', __('Save Permissions'), array('class' => 'btn btn-success pull-right')); ?>
	<div class="clearfix"></div><br>
<?php echo Form::close() ?>