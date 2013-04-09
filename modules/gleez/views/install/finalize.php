<?php defined("SYSPATH") OR die("No direct script access.") ?>

<p><?php echo __('Your Gleez install is complete!'); ?></p>

<h3><?php echo __('Before you start using it...'); ?></h3>

<p>
  <?php echo __('We\'ve created an account for you to use:'); ?>
  <br/>
  <?php echo __('username: :username', array(':username' => '<strong>admin</strong>')); ?>
  <br/>
  <?php echo __('password: :password', array(':password' => '<strong>'.$password.'</strong>')); ?>
  <br/>
  <br/>
  <?php echo __('Save this information in a safe place, or change your :profile right away!', array(':profile' => HTML::anchor($admin_url, __('admin password')) )); ?>
</p>

<p>
<h4><?php echo HTML::anchor(Route::get('default')->uri(), __('Start using Gleez')) ?></h4>
</p>

