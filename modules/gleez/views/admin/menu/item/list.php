<?php echo HTML::anchor(Route::get('admin/menu/item')->uri(array('action' => 'add', 'id' => $id)), '<i class="icon-plus icon-white"></i>'.__('Add New Item'), array('title'=>__('Add New Item'), 'class' => 'btn btn-success pull-right')); ?>
<div class='clearfix'></div><br/>

	<?php echo Form::open(Route::get('admin/menu/item')->uri(array('action' => 'confirm', 'id' => $id)), array('id'=>'menu-form', 'class'=>'form')); ?>

	<table id="admin-list-menu-items" class="table table-striped table-bordered table-highlight">
		<thead>
			<tr>
				<th><?php echo __('Name') ?></th>
				<th><?php echo __('Enabled') ?></th>
				<th class="tabledrag-hide"><?php echo __('Weight') ?></th>
				<th><?php echo __('Actions') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($items as $item): ?>
			<tr id="item-row-<?php echo $item['id'] ?>" class="draggable <?php echo Text::alternate("odd", "even") ?>">
				<td id="item-<?php echo $item['id'] ?>"  class="lid-<?php echo $item['lvl'] ?>">
					<?php
						$c = 2;
						while ($c < $item['lvl'])
						{
							echo '<div class="indentation">&nbsp;</div>';
							$c++;
						}
						echo HTML::chars($item['title'])
					?>
				</td>

				<td>
					<?php echo Form::checkbox('mlid:'.$item['id'].'[hidden]', TRUE, $item['active'] ? TRUE : FALSE); ?>
				</td>

				<td class="tabledrag-hide">
					<?php echo Form::weight('mlid:'.$item['id'].'[weight]', 0, array('class' => 'menu-weight')) ?>
					<?php echo Form::hidden('mlid:'.$item['id'].'[plid]', $item['pid'], array('class' => 'menu-plid')) ?>
					<?php echo Form::hidden('mlid:'.$item['id'].'[mlid]', $item['id'], array('class' => 'menu-mlid')) ?>
				</td>

				<td class="action">
					<?php echo HTML::anchor(Route::get('admin/menu/item')->uri(array('action' => 'edit', 'id' => $item['id'])), '<i class="btn-icon-only icon-edit"></i>', array('class' => 'btn btn-small', 'title' => __('Edit Item'))) ?>
					<?php echo HTML::anchor(Route::get('admin/menu/item')->uri(array('action' => 'delete', 'id' => $item['id'])), '<i class="btn-icon-only icon-trash"></i>', array('class' => 'btn btn-small', 'title' => __('Delete Item'))) ?>
				</td>
			  </tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php echo Form::submit('menu-item-list', __('Save'), array('class'=>'btn btn-success pull-right')); ?>
    <div class="clearfix"></div><br>
<?php echo Form::close(); ?>