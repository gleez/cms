<?php defined("SYSPATH") OR die("No direct script access.");

<div class="help">

<?php echo __('This shows the complete information on the recorded event from the log.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' =>'delete', 'id' => $log['_id'])), '<i class="icon-trash"></i> Удалить событие', array('class' => 'btn btn-danger pull-right')) ?>
<div class="clearfix"></div><br>

<table id="log-admin-view" class="table table-bordered table-striped">
  <colgroup><col class="oce-first"></colgroup>
  <thead>
    <tr>
      <th>Field</th>
      <th>Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Message</td>
      <td><?php echo $log['_id']; ?></td>
    </tr>
    <tr>
      <td>Type</td>
      <td>
        <span class="label label-<?php echo strtolower($log['type']); ?>">
          <?php echo $log['type']; ?>
        </span>
      </td>
    </tr>
    <tr>
      <td>Date</td>
      <td><?php echo Date::date_time($log['time']->sec); ?></td>
    </tr>
    <tr>
      <td>Host</td>
      <td><?php echo $log['host']; ?></td>
    </tr>
    <tr>
      <td>User Agent</td>
      <td><?php echo Text::plain($log['agent']) ?></td>
    </tr>
    <tr>
      <td>User</td>
      <td><?php echo Text::plain($log['user']) ?></td>
    </tr>
    <tr>
      <td>URL</td>
      <td><?php echo Text::plain($log['url']) ?></td>
    </tr>
    <tr>
      <td>Message</td>
      <td><?php echo Text::plain($log['body']) ?></td>
    </tr>
  </tbody>
</table>