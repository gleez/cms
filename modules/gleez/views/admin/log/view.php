<?php defined('SYSPATH') or die('No direct script access.'); ?>

<table id="log-admin-view" class="table table-striped" >
	<colgroup><col class="oce-first"></colgroup>
	<thead>
		<tr>
			<th><?php echo __('Name') ?></th>
			<th><?php echo __('Value') ?></th>
		</tr>
	</thead>
	<tbody>

	<tr>
		<td><?php echo __('Type') ?></td>
		<td><?php echo Text::plain($log['type']) ?></td>
	</tr>
	<tr>
		<td><?php echo __('Time') ?></td>
		<td><?php echo Text::plain(date('Y-M-d h:i:s', $log['time']->sec)) ?></td>
	</tr>
	<tr>
		<td><?php echo __('Hostname') ?></td>
		<td><?php echo Text::plain($log['hostname']) ?></td>
	</tr>
	<tr>
		<td><?php echo __('User Agent') ?></td>
		<td><?php echo Text::plain($log['user_agent']) ?></td>
	</tr>
	<tr>
		<td><?php echo __('User') ?></td>
		<td><?php echo Text::plain($log['user']) ?></td>
	</tr>
	<tr>
		<td><?php echo __('Referer') ?></td>
		<td><?php echo Text::plain($log['referer']) ?></td>
	</tr>
	<tr>
		<td><?php echo __('Url') ?></td>
		<td><?php echo Text::plain($log['url']) ?></td>
	</tr>
	<tr>
		<td><?php echo __('Message') ?></td>
		<td><?php echo Text::plain($log['body']) ?></td>
	</tr>
	</tbody>
</table>