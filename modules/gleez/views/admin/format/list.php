<div class="help">
	<?php echo __('Text formats defines the HTML tags, code, and other formatting that can be used when entering text. Improper text format configuration is a security risk. Text formats are presented on content editing pages in the order defined on this page. The first format available to a user will be selected by default.'); ?>
</div>

<table id="text-format-order" class="table table-striped table-bordered table-highlight">
	<thead>
		<tr>
			<th><?php echo __('Name') ?></th>
			<th><?php echo __('Roles') ?></th>
			<th class="tabledrag-hide"><?php echo __('Weight') ?></th>
			<th><?php echo __('Actions') ?></th>
		</tr>
	</thead>

	<?php foreach ($formats as $id => $format): ?>
		<tr id="text-format-row-<?php echo $id ?>" class="draggable <?php echo Text::alternate("odd", "even") ?>">
			<td id="text-format-<?php echo $id ?>">
				<?php echo $format['name'] ?>
			</td>
			<td>
				<?php echo $format['roles'] ?>
			</td>
			<td class="tabledrag-hide">
				<?php
					echo Form::weight('formats['.$id.'][weight]', $format['weight'], array('class' => 'text-format-order-weight'));
				?>
			</td>
			<td class="action">
				<?php
					$route_param = array(
						'id' => $id,
						'action' => 'configure'
					);

					echo HTML::anchor(Route::get('admin/format')->uri($route_param), '<i class="icon-cog"></i>', array('class' => 'action-list', 'title' => __('Configure')));
				?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>