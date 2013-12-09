<table class="table table-striped">
	<tbody>
		<tr>
			<th><?php _e('Gleez Version') ?></th>
			<td><?php echo HTML::chars(Gleez::VERSION); ?></td>
		</tr>
		<tr>
			<th><?php _e('Enabled modules') ?></th>
			<td><?php echo count(Module::active()); ?></td>
		</tr>
		<tr>
			<th><?php _e('Registered users') ?></th>
			<td><?php echo User::count_all(); ?></td>
		</tr>
		<tr>
			<th><?php _e('Host Name') ?></th>
			<td><?php echo HTML::chars(php_uname("n")); ?></td>
		</tr>
		<tr>
			<th><?php _e('Server Software') ?></th>
			<td><?php echo isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : __('Not available'); ?></td>
		</tr>
		<tr>
			<th><?php _e('Server Signature') ?></th>
			<td><?php echo isset($_SERVER['SERVER_SIGNATURE']) ? $_SERVER['SERVER_SIGNATURE'] : __('Not available'); ?></td>
		</tr>
		<tr>
			<th><?php _e('Server Name') ?></th>
			<td><?php echo isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : __('Not available'); ?></td>
		</tr>
		<tr>
			<th><?php _e('Server Address') ?></th>
			<td><?php
					// Windows running IIS v6 does not include $_SERVER['SERVER_ADDR']
					echo gethostbyname($_SERVER['SERVER_NAME']);
				?>
			</td>
		</tr>
		<tr>
			<th><?php _e('Server Port') ?></th>
			<td><?php echo isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : __('Not available'); ?></td>
		</tr>
		<tr>
			<th><?php _e('OS Type') ?></th>
			<td><?php echo php_uname(); ?></td>
		</tr>
		<tr>
			<th><?php _e('PHP Version') ?></th>
			<td><?php echo HTML::chars(PHP_VERSION); ?></td>
		</tr>
		<tr>
			<th><?php _e('Zend Engine Version') ?></th>
			<td><?php echo zend_version(); ?></td>
		</tr>
		<tr>
			<th><?php _e('Platform') ?></th>
			<td><?php echo (PHP_INT_SIZE * 8).'Bit'; ?></td>
		</tr>
		<tr>
			<th><?php _e('Loaded Extensions') ?></th>
			<td><?php echo implode(', ', get_loaded_extensions()); ?></td>
		</tr>
		<tr>
			<th><?php _e('MySQL Version') ?></th>
			<td><?php echo HTML::chars(Database::instance()->version(TRUE)); ?></td>
		</tr>
		<tr>
			<th><?php _e('Memory Limit') ?></th>
			<td><?php echo System::get_memory_limit(); ?></td>
		</tr>
		<tr>
			<th><?php _e('Load Average') ?></th>
			<td><?php echo System::get_avg(); ?></td>
		</tr>
	</tbody>
</table>
