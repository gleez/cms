<?php defined('SYSPATH') or die('No direct script access.'); ?>

    <table class="hor-minimalist-a" id="admin-info">
	<tbody>
	    
	    <tr>
		<th><?php echo __('Gleez Version ') ?></th>
		<td><?php echo Html::chars(Gleez::VERSION) ?></td>
	    </tr>
	    
	    <tr>
		<th><?php echo __('Host Name ') ?></th>
		<td><?php echo Html::chars(php_uname("n")) ?></td>
	    </tr>
		    
	    <tr>
		<th><?php echo __('Webserver ') ?></th>
		<td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
	    </tr>
	   
	    <tr>
		<th><?php echo __('PHP Version ') ?></th>
		<td><?php echo Html::chars(PHP_VERSION) ?></td>
	    </tr>
	    
	    <tr>
		<th><?php echo __("Database Version") ?></th>
		<td><?php echo function_exists("mysql_get_server_info") ? mysql_get_server_info() : __('Unavailable'); ?></td>
	    </tr>
	    
	    <tr>
		<th><?php echo __('Load Average ') ?></th>
		<td><?php echo @is_readable("/proc/loadavg") ? join(" ", array_slice(explode(" ",
			current(file("/proc/loadavg"))), 0, 3)) : __('Unavailable'); ?></td>
	    </tr>
	    
	</tbody>
    </table>