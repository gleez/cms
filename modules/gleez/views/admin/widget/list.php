<div class="help">
	<?php echo __('This page provides interface for assigning a widget to a region, and for controlling the order of widgets within regions. Click the configure link next to each widget to configure its specific title and visibility settings.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/widget')->uri( array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add Widget'), array('class' => 'btn btn-success pull-right')) ?>
<div class="clearfix"></div><br>
<?php echo Form::open( Route::url('admin/widget', array('action' => 'index')), array('id'=>'widgets-form', 'class'=>'form') ); ?>
	<table id="widgets" class="table table-striped table-bordered table-highlight">
		<thead>
			<tr>
				<th><?php echo __('Title') ?></th>
				<th><?php echo __('Region') ?></th>
				<th class="tabledrag-hide" ><?php echo __('Order') ?></th>
				<th><?php echo __('Operations') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($widget_regions as $region => $title): ?>
				<tr class="region-title region-title-<?php echo $region?>">
					<td colspan="4"><?php echo HTML::chars($title) ?></td>
				</tr>
				<tr class="region-message region-<?php print $region?>-message <?php echo empty($widgets[$region]) ? 'region-empty' : 'region-populated'; ?>">
					<td colspan="4"><em><?php echo empty($widgets[$region]) ? __('No Widgets in this region') : '&nbsp;'; ?></em></td>
				</tr>
				<?php foreach ($widgets[$region] as $i => $widget): ?>
					<tr id="widget-row-<?php echo $widget->id ?>" class="draggable <?php echo Text::alternate('odd', 'even') ?>">
						<?php
							$split_name = explode('/', $widget->name);
							$static = ($split_name AND $split_name[0] == 'static') ? TRUE : FALSE;
						?>
						<td class="widget" id="widget-<?php echo $widget->id ?>">
							<?php echo Text::plain($widget->title) ?>
						</td>
						<td>
							<?php echo Form::select('widgets['.$widget->name.'][region]', $widget_regions, $widget->region, array('class' => 'large widget-region-select widget-region-'.$region)); ?>
						</td>
						<td class="tabledrag-hide" >
							<?php
								echo Form::weight('widgets['.$widget->name.'][weight]', $widget->weight, array('class' => 'widget-weight widget-weight-'.$region), $weight_delta);
								echo Form::hidden('widgets['.$widget->name.'][id]', $widget->id);
							?>
						</td>
						<td class="action">
							<?php
								echo HTML::anchor($widget->edit_url, '<i class="icon-cog"></i>', array('class'=>'action-edit', 'title'=>__('Configure')));

								if ($static)
								{
									echo HTML::anchor(Route::get('admin/widget')->uri(array('action' => 'delete', 'id'=> $widget->id)), ' <i class="icon-trash"></i>', array('class'=>'action-delete', 'title'=>__('Delete')));
								}

								unset($static);
							?>
						</td>
					</tr>
				<?php endforeach ?>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo Form::submit('widget-list', __('Save Widgets'), array('class'=>'btn btn-success pull-right')); ?>
<?php echo Form::close(); ?>
