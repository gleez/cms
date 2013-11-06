<?php echo __('Hello :name!', array(':name' => ($nick ? $nick : $name))) . PHP_EOL ?>
<?php echo __('Thank you for registering at :site.', array(':site' => Config::get('site.site_name', 'Gleez CMS'))) . PHP_EOL ?>

<?php echo __('IMPORTANT:') . PHP_EOL ?>
<?php echo __('For full site access, you will need to click on this link or copy and paste it in your browser: :url', array(':url' => PHP_EOL . $url)) . PHP_EOL . PHP_EOL ?>
<?php echo __('This will verify your account. In the future you will be able to log in using the username and password that you created during registration.') . PHP_EOL ?>

--

<?php echo __('This mail is an automatic notification and requires you to answer.') . PHP_EOL ?>
<?php echo __('If you are were not going to register an account, please ignore this message.') . PHP_EOL . PHP_EOL ?>

<?php echo __('Best regards, :site team', array(':site' => Config::get('site.site_name', 'Gleez CMS'))) . PHP_EOL ?>
<?php echo URL::site('', TRUE) ?>