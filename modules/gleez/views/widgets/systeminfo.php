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
			<th><?php _e('Webserver') ?></th>
			<td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
		</tr>
		<tr>
			<th><?php _e('PHP Version') ?></th>
			<td><?php echo HTML::chars(PHP_VERSION); ?></td>
		</tr>
		<tr>
			<th><?php _e('MySQL Version') ?></th>
			<td><?php echo HTML::chars(Database::instance()->version(TRUE)); ?></td>
		</tr>
		<tr>
			<th><?php _e('Load Average') ?></th>
			<td><?php echo System::get_avg(); ?></td>
		</tr>
	</tbody>
</table>
