<table class="hor-minimalist-a" id="admin-info">
	<tbody>
		<tr>
			<th><?php echo __('Gleez Version') ?></th>
			<td><?php echo HTML::chars(Gleez::VERSION); ?></td>
		</tr>
		<tr>
			<th><?php echo __('Host Name') ?></th>
			<td><?php echo HTML::chars(php_uname("n")); ?></td>
		</tr>
		<tr>
			<th><?php echo __('Webserver') ?></th>
			<td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
		</tr>
		<tr>
			<th><?php echo __('PHP Version') ?></th>
			<td><?php echo HTML::chars(PHP_VERSION); ?></td>
		</tr>
		<tr>
			<th><?php echo __('MySQL Version') ?></th>
			<td><?php echo HTML::chars(Database::instance()->version(TRUE)); ?></td>
		</tr>
		<tr>
			<th><?php echo __('Load Average') ?></th>
			<td><?php echo System::get_avg(); ?></td>
		</tr>
	</tbody>
</table>