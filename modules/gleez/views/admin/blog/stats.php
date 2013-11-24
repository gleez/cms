<div class="help">
	<p><?php _e('Summary statistics of blogs used on your site'); ?></p>
</div>

<div class="row">
	<div class="col-md-3 col-sm-6">
		<div class="dashboard-stat primary">
			<div class="visual">
				<i class="fa fa fa-folder-open"></i>
			</div>
			<div class="details">
				<span class="content"><?php _e('Categories'); ?></span>
				<span class="value"><?php _e('Total: %sum', array('%sum' => $stats['categories']['total'])); ?></span>
			</div>
			<?php echo HTML::anchor(Route::get('admin/taxonomy')->uri(array('action' => 'list')), '<i class="fa fa-play-circle more"></i>')?>
		</div>
	</div>
	<div class="col-md-3 col-sm-6">
		<div class="dashboard-stat secondary">
			<div class="visual">
				<i class="fa fa-tags"></i>
			</div>
			<div class="details">
				<span class="content"><?php _e('Tags'); ?></span>
				<span class="value"><?php _e('Total: %sum', array('%sum' => $stats['tags']['total'])); ?></span>
			</div>
			<?php echo HTML::anchor(Route::get('admin/taxonomy')->uri(array('action' => 'list')), '<i class="fa fa-play-circle more"></i>')?>
		</div>
	</div>
	<div class="col-md-3 col-sm-6">
		<div class="dashboard-stat tertiary">
			<div class="visual">
				<i class="fa fa-file"></i>
			</div>
			<div class="details">
				<span class="content"><?php _e('Posts'); ?></span>
				<span class="value"><?php _e('Total: %sum', array('%sum' => $stats['articles']['total'])); ?></span>
			</div>
			<?php echo HTML::anchor(Route::get('admin/taxonomy')->uri(array('action' => 'list')), '<i class="fa fa-play-circle more"></i>')?>
		</div>
	</div>
	<div class="col-md-3 col-sm-6">
		<div class="dashboard-stat">
			<div class="visual">
				<i class="fa fa-comments"></i>
			</div>
			<div class="details">
				<span class="content"><?php _e('Comments'); ?></span>
				<span class="value"><?php _e('Total: %sum', array('%sum' => $stats['comments']['total'])); ?></span>
			</div>
			<?php echo HTML::anchor(Route::get('admin/taxonomy')->uri(array('action' => 'list')), '<i class="fa fa-play-circle more"></i>')?>
		</div>
	</div>
</div>
