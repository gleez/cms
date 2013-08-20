<p class="lead">
  <?php echo __('Installing Gleez is very easy. We just need to know how to talk to your MySQL database, and we need a place to store config on your web host. Everything else will do Gleez Installer. But first we need to check your system on compliance to the minimum requirements.'); ?>
</p>

<?php if ( ! @is_writable(APPPATH)): ?>
<p>
  <code><?php echo APPPATH ?></code><br />
  <?php echo __('Make sure this folder writable by the webserver, or 777 permissions on this folder.'); ?>
</p>
<?php endif ?>

<?php echo Form::open(Route::get('install')->uri(array('action' => 'systemcheck'))); ?>
	<?php echo Form::button('continue', __('Continue'), array('class' => 'btn btn-primary pull-right', 'type' => 'submit')); ?>
<?php echo Form::close(); ?>
<div class="clearfix"></div><br>
