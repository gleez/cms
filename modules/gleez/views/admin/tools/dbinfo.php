<div class="alert alert-warning">
	<?php echo __('Database space: :space KB', array(':space' => round($space, 2)) ); ?>
</div>

<div class="table-responsive">
	<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th><?php echo __('Tables (:count)', array(':count' => $count)); ?></th>
				<th><?php echo __('Rows'); ?></th>
				<th><?php echo __('Size'); ?> KB</th>
			</tr>
		</thead>

		<tbody>
			<?php foreach ($tables as $table): ?>
				<tr>
					<td><?php echo $table['name']; ?></td>
					<td><?php echo $table['rows']; ?></td>
					<td><?php echo $table['space']; ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>