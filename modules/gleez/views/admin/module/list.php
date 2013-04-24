<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<div class="help">
	<p><?php echo __('Power up your Gleez CMS by adding more modules! Each module provides new cool features.'); ?></p>
</div>

<?php echo Form::open($action, array('id'=>'module-form', 'class'=>'form')); ?>

	<table class="table table-striped table-bordered" id="admin-modules">
		<thead>
			<tr>
				<th>#</th>
				<th><?php echo __('Name'); ?></th>
				<th><?php echo __('Version'); ?></th>
				<th><?php echo __('Description'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($available as $module_name => $module_info):  ?>
			<tr class="<?php echo Text::alternate("odd", "even"); ?>">
				<td>
					<?php if ($module_info->locked): ?>
						<?php echo Form::checkbox($module_name, TRUE, $module_info->active, array('disabled')); ?>
					<?php else: ?>
						<?php echo Form::checkbox($module_name, TRUE, $module_info->active); ?>
					<?php endif ?>
				</td>
				<td><?php echo $module_info->name; ?></td>
				<td><?php echo $module_info->version; ?></td>
				<td>
					<p><?php echo __($module_info->description); ?></p>
					<p class="pull-right">
						<?php echo __($module_info->author) ?>
					</p>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>

	<?php echo Form::submit('modules', __('Save'), array('class'=>'btn btn-primary pull-right')); ?>
	<div class="clearfix"></div><br>
<?php echo Form::close(); ?>
