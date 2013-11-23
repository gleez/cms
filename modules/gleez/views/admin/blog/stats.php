<div class="help">
	<p><?php _e('Summary statistics of blogs used on your site'); ?></p>
</div>


<div class="content col-sm-12">
	<div class="row">
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php _e('Categories'); ?></h3>
				</div>
				<div class="panel-body">
					<?php _e('Total: %sum', array('%sum' => $stats['categories']['total'])); ?>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php _e('Tags'); ?></h3>
				</div>
				<div class="panel-body">
					<?php _e('Total: %sum', array('%sum' => $stats['tags']['total'])); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php _e('Posts'); ?></h3>
				</div>
				<div class="panel-body">
					<?php _e('Total: %sum', array('%sum' => $stats['articles']['total'])); ?>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php _e('Comments'); ?></h3>
				</div>
				<div class="panel-body">
					<?php _e('Total: %sum', array('%sum' => $stats['comments']['total'])); ?>
				</div>
			</div>
		</div>
	</div>
</div>
