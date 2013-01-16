<?php defined('SYSPATH') or die('No direct script access.'); ?>

    <div class="help">
	<?php echo __('The log component monitors your website, capturing system events in a log to be reviewed by an authorized individual at a later time. The log is simply a list of recorded events containing usage data, performance data, errors, warnings and operational information. It is vital to check the log report on a regular basis as it is often the only way to tell what is going on.'); ?>
    </div>
    
<table id="log-admin-list" class="tablesorter table table-striped">
	<thead>
		<tr>
			<th><?php echo __('Time'); ?></th>
			<th><?php echo __('Type'); ?></th>
			<th><?php echo __('Message'); ?></th>
			<th><?php echo __('User'); ?></th>
			<th><?php echo __('Client'); ?></th>
			<th><?php echo __('Agent'); ?></th>
			<!--<th><?php //echo __('Referer'); ?></th>-->
		</tr>
	</thead>
        
	<tbody>
        <?php foreach ($logs as $log) : ?>
                <?php
                    $message = Text::limit_chars($log['body'], 50);
                    $agent = Text::limit_chars($log['user_agent'], 5);
                    $referer = $log['referer'] == '""' ? '': Text::limit_chars($log['referer'], 50);
                    $user = User::lookup((int) $log['user']);
		    $time = date('Y-M-d h:i:s', $log['time']->sec);
    
                    $color = '#FFF';
                    if ($log['type'] == LOG::ERROR) $color = '#F96';
                    if ($log['type'] == LOG::INFO)  $color = '#9CF';
                ?>
	    <tr class="<?php echo Text::alternate('odd', 'even') ?>">
		<td><?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' => 'view', 'id' => $log['_id'])),
					    $time, array('class'=>'action-view', 'title'=> __('view log'))); ?>
		</td>
		<td style="background:<?php echo $color; ?>"> <?php echo $log['type']; ?></td>
		<td title="<?php echo Text::plain($log['body']); ?>"><?php echo Text::plain($message); ?></td>
		<td><?php echo $user->nick; ?></td>
		<td><?php echo Text::plain($log['hostname']); ?></td>
		<td title="<?php echo Text::plain($log['user_agent']); ?>"><?php echo Text::plain($agent); ?></td>
		<!--<td title="<?php //echo Text::plain($log['referer']); ?>"><?php //echo Text::plain($referer); ?></td>-->
	    </tr>
        
        <?php endforeach; ?>
        </tbody>
        
</table>
<?php echo $pagination ?>