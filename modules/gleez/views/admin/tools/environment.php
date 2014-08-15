<?php
/**
 * @todo Use links to Gleez Doc for constants here
 * @todo Use Gleez::init here
 * @todo Use GitHub links here
 *
 * @var string $gleezEnv
 */
?>
<table id="info-admin-view" class="table table-striped table-bordered">
	<tbody>
		<tr>
			<th><?php echo __('Gleez Version') ?></th>
			<td><?php echo Gleez::getVersion()?></td>
		</tr>
		<?php if (!empty($gleezEnv)):?>
		<tr>
			<th>GLEEZ_ENV</th>
			<td>
				<?php foreach($gleezEnv['all'] as $label => $value): ?>
					<p>
						<?php if(defined($label) && Kohana::$environment == constant($label)):?>
							<i class="fa fa-check text-success"></i>&nbsp;
						<?php else: ?>
							<i class="fa fa-times text-muted"></i>&nbsp;
						<?php endif;?>
						<span class="title"><?php echo $label ?></span>
						<span class="pull-right"><code><?php echo $value ?></code></span>
					</p>
				<?php endforeach; ?>
			</td>
		</tr>

			<?php endif; ?>
		<tr>
			<th>EXT</th>
			<td><code><?php echo EXT?></code></td>
		</tr>
		<tr>
			<th>DOCROOT</th>
			<td><code><?php echo DOCROOT?></code></td>
		</tr>
		<tr>
			<th>APPPATH</th>
			<td><code><?php echo APPPATH?></code></td>
		</tr>
		<tr>
			<th>MODPATH</th>
			<td><code><?php echo MODPATH?></code></td>
		</tr>
		<tr>
			<th>GLZPATH</th>
			<td><code><?php echo GLZPATH?></code></td>
		</tr>
		<?php if (defined('SYSPATH')): ?>
		<tr>
			<th>SYSPATH</th>
			<td><code><?php echo SYSPATH?></code></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th>THEMEPATH</th>
			<td><code><?php echo THEMEPATH?></code></td>
		</tr>
		<tr>
			<th><?php echo __(':kohana settings', array(':kohana' => 'Kohana::init()')) ?></th>
			<td>
					"base_url" = <?php echo Debug::dump(Kohana::$base_url) ?><br>
					"index_file" = <?php echo Debug::dump(Kohana::$index_file) ?><br>
					"charset" = <?php echo Debug::dump(Kohana::$charset) ?><br>
					"cache_dir" = <?php echo Debug::dump(Kohana::$cache_dir) ?><br>
					"errors" = <?php echo Debug::dump(Kohana::$errors) ?><br>
					"profile" = <?php echo Debug::dump(Kohana::$profiling) ?><br>
					"caching" = <?php echo Debug::dump(Kohana::$caching) ?>
			</td>
		</tr>
	</tbody>
</table>
