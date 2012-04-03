<?php defined("SYSPATH") or die("No direct script access.") ?>

<p>
Installing Gleez is very easy.  We just need to know how to talk to your MySQL
database, and we need a place to store config on your web host. 
</p>

<?php if (!@is_writable(APPPATH)): ?>
    <p>
	<code><?php echo APPPATH ?></code><br />
	Make sure this folder writable by the webserver, or 777 permissions on this folder.
    </p>
<?php endif ?>

<?php echo Form::open( Route::get('install')->uri(array('action' => 'systemcheck')) ) ?>
<div align="center">
    <?php echo Form::submit('continue', __('Continue'), array('class' => 'btn btn-primary btn-large')) ?>
</div>
<?php echo Form::close() ?>

<br /><br />