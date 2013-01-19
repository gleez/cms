<?php defined("SYSPATH") OR die("No direct script access.");

<div class="help">
  <?php echo __('The log component monitors your website, capturing system events in a log to be reviewed by an authorized individual at a later time. The log is simply a list of recorded events containing usage data, performance data, errors, warnings and operational information. It is vital to check the log report on a regular basis as it is often the only way to tell what is going on.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' =>'clear')), '<i class="icon-trash"></i> '.__('Clear all'), array('class' => 'btn btn-danger pull-right', 'title' => __('Clear all messages from the log'))); ?>
<div class="clearfix"></div><br>

<table id="log-admin-list" class="table table-bordered table-striped table-hover">
  <thead>
    <tr>
      <th><?php echo __('Date') ?></th>
      <th><?php echo __('Type') ?></th>
      <th><?php echo __('Message') ?></th>
      <th><?php echo __('Host') ?></th>
      <th><?php echo __('URL') ?></th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($logs as $log) : ?>
    <tr>
      <td><?php echo Date::date_time($log['time']->sec); ?></td>
      <td>
        <span class="label label-<?php echo strtolower($log['type']); ?>">
          <?php echo $log['type']; ?>
        </span>
      </td>
      <td>
        <?php echo HTML::anchor(Route::get('admin/log')->uri(array('action' => 'view', 'id' => $log['_id'] )), Text::plain(Text::limit_chars($log['body'], 50)), array('title'=> __('View event'))); ?>
      </td>
      <td><?php echo $log['host']; ?></td>
      <td><?php echo Text::limit_chars($log['url'], 25); ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php echo $pagination ?>