<?php defined("SYSPATH") OR die("No direct script access.");

<div class="help">

<?php echo __('This shows the complete information on the recorded event from the log.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' =>'delete', 'id' => $log['_id'])), '<i class="icon-trash"></i> '.__('Delete'), array('class' => 'btn btn-danger pull-right', 'title' => __('Delete this event from log'))) ?>
<div class="clearfix"></div><br>

<table id="log-admin-view" class="table table-bordered table-striped">
  <colgroup><col class="oce-first"></colgroup>
  <thead>
    <tr>
      <th><?php echo __('Field')?></th>
      <th><?php echo __('Value')?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><?php echo __('Message')?></td>
      <td><?php echo $log['_id']; ?></td>
    </tr>
    <tr>
      <td><?php echo __('Type')?></td>
      <td>
        <span class="label label-<?php echo strtolower($log['type']); ?>">
          <?php echo $log['type']; ?>
        </span>
      </td>
    </tr>
    <tr>
      <td><?php echo __('Date')?></td>
      <td><?php echo Date::date_time($log['time']->sec); ?></td>
    </tr>
    <tr>
      <td><?php echo __('IP')?></td>
      <td><?php echo $log['host']; ?></td>
    </tr>
    <tr>
      <td><?php echo __('User Agent')?></td>
      <td><?php echo Text::plain($log['agent']) ?></td>
    </tr>
    <tr>
      <td><?php echo __('User')?></td>
      <td><?php echo Text::plain($log['user']) ?></td>
    </tr>
    <tr>
      <td><?php echo __('URL')?></td>
      <td><?php echo Text::plain($log['url']) ?></td>
    </tr>
    <tr>
      <td><?php echo __('Message')?></td>
      <td><?php echo Text::plain($log['body']) ?></td>
    </tr>
  </tbody>
</table>