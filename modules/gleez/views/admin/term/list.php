<?php echo HTML::anchor(Route::get('admin/term')->uri($params), '<i class="fa fa-plus fa-white"></i> '.__('Add New Term'), array('title'=>__('Add New Term'),'class' => 'btn btn-success pull-right')); ?>
<div class="clearfix"></div><br>

<?php echo Form::open(Route::get('admin/term')->uri(array('action' => 'confirm', 'id' => $id)), array('id'=>'menu-form', 'class'=>'form')); ?>
	<div class="clearfix"></div>

	<table id="term-admin-list" class="table table-striped table-bordered table-highlight" data-toggle="tabledrag">
		<thead>
		<tr>
			<th width="30%"><?php echo __('Name'); ?></th>
			<th class="tabledrag-hide"><?php echo __('Weight'); ?></th>
			<th width="50%"><?php echo __('Description'); ?></th>
			<th width="10%"><?php echo __('Actions'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($terms as $item): ?>
			<tr id="term-row-<?php echo $item['id'] ?>" class="draggable">
				<td id="term-<?php echo $item['id'] ?>">
					<?php
						$c = 2;
						while ($c < $item['lvl'])
						{
							echo '<div class="indentation">&nbsp;</div>';
							$c++;
						}

						echo HTML::chars($item['name'])
					?>
				</td>
				<td class="tabledrag-hide">
					<?php echo Form::weight('tid:'.$item['id'].'[weight]', 0, array('class' => 'row-weight')) ?>
					<?php echo Form::hidden('tid:'.$item['id'].'[pid]', $item['pid'], array('class' => 'row-parent')) ?>
					<?php echo Form::hidden('tid:'.$item['id'].'[tid]', $item['id'], array('class' => 'row-id')) ?>
					<?php echo Form::hidden('tid:'.$item['id'].'[depth]', $item['lvl'], array('class' => 'term-depth')) ?>
				</td>
				<td>
					<p class="text text-muted"> <?php echo Text::plain($item['description']); ?> </p>
				</td>
				<td class="action">
					<?php echo HTML::anchor(Route::get('admin/term')->uri(array('action' => 'edit', 'id' => $item['id'])), '<i class="fa fa-edit"></i>', array('class' => 'btn btn-sm btn-default', 'title' => __('Edit Term'))); ?>
					<?php echo HTML::anchor(Route::get('admin/term')->uri(array('action' => 'delete', 'id' => $item['id'])), '<i class="fa fa-times"></i>', array('class' => 'btn btn-sm btn-default btn-danger', 'title' => __('Delete Term'))); ?>
				</td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>

<?php echo Form::submit('term-list', __('Save'), array('class'=>'btn btn-success pull-right')); ?>
	<div class="clearfix"></div><br>
<?php echo Form::close(); ?>
