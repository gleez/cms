<div class="help">
	<p><?php echo __('The main parameters of the site set up here. The following are all of the settings that define your site as a whole: settings which determine how your site behaves, how you interact with your site, and how the rest of the world interacts with your site.') ?></p>
</div>

<ul class="nav nav-tabs">
    <li class="active"><a href="#general" data-toggle="tab"><?php echo __('General'); ?></a></li>
    <li><a href="#theme" data-toggle="tab"><?php echo __('Appearance'); ?></a></li>
    <li><a href="#timezone" data-toggle="tab"><?php echo __('Date & Time'); ?></a></li>
    <li><a href="#seo" data-toggle="tab"><?php echo __('SEO & Tracking'); ?></a></li>
    <li><a href="#offline" data-toggle="tab"><?php echo __('Maintenance'); ?></a></li>
</ul>
    
<?php echo Form::open($action, array('id'=>'settings-form', 'class'=>'form-horizontal')) ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<?php // @todo Move to controller ?>
	<?php $post['site_url'] = ($post['site_url'] === 'www.example.com') ? URL::site(NULL, TRUE) : $post['site_url']; ?>

<div class="tab-content">

	<div class="tab-pane active" id="general">
		<div class="control-group <?php echo isset($errors['site_name']) ? 'error': ''; ?>">
			<?php echo Form::label('site_name', __('Site Title'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::input('site_name', $post['site_name'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['site_slogan']) ? 'error': ''; ?>">
			<?php echo Form::label('site_slogan', __('Tagline'), array('class' => 'control-label')) ?>
			<div class="controls">
				<?php echo Form::input('site_slogan', $post['site_slogan'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['site_email']) ? 'error': ''; ?>">
			<?php echo Form::label('site_email', __('E-mail address'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::input('site_email', $post['site_email'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['site_url']) ? 'error': ''; ?>">
			<?php echo Form::label('site_url', __('Website address'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::input('site_url', $post['site_url'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['site_mission']) ? 'error': ''; ?>">
			<?php echo Form::label('site_mission', __('Mission'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::textarea('site_mission', $post['site_mission'], array('class' => 'span6', 'rows' => 6)) ?>
			</div>
		</div>
	</div>
	
	<div class="tab-pane" id="seo">
		<div class="control-group <?php echo isset($errors['keywords']) ? 'error': ''; ?>">
			<?php echo Form::label('keywords', __('Keywords for search engines'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::textarea('keywords', $post['keywords'], array('class' => 'span6', 'rows' => 3)); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['description']) ? 'error': ''; ?>">
			<?php echo Form::label('description', __('Description for search engines'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::textarea('description', $post['description'], array('class' => 'span6', 'rows' => 3)); ?>
			</div>
		</div>
		
		<div class="control-group <?php echo isset($errors['front_page']) ? 'error': ''; ?>">
			<?php echo Form::label('front_page', __('Front page'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::input('front_page', $post['front_page'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['google_ua']) ? 'error': ''; ?>">
			<?php echo Form::label('google_ua', __('Google Analytics'), array('class' => 'control-label')) ?>
			<div class="controls">
				<?php echo Form::input('google_ua', $post['google_ua'], array('class' => 'span6', 'placeholder' => 'UA-12345678-9')); ?>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="theme">
		<div class="control-group <?php echo isset($errors['site_logo']) ? 'error': ''; ?>">
			<?php echo Form::label('site_logo', __('Logo'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::input('site_logo', $post['site_logo'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['site_favicon']) ? 'error': ''; ?>">
			<?php echo Form::label('site_favicon', __('Favicon'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::input('site_favicon', $post['site_favicon'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['theme']) ? 'error': ''; ?>">
			<?php echo Form::label('theme', __('Site Theme'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::select('theme', Theme::available(), $post['theme'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['admin_theme']) ? 'error': ''; ?>">
			<?php echo Form::label('admin_theme', __('Admin Theme'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::select('admin_theme', Theme::available(), $post['admin_theme'], array('class' => 'span6')); ?>
			</div>
		</div>

		<div class="control-group <?php echo isset($errors['use_gravatars']) ? 'error': ''; ?>">
			<?php echo Form::label('use_gravatars', __('Use Gravatars'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php
					// @important the hidden filed should be before checkbox
					echo Form::hidden('use_gravatars', 0);
					echo Form::label('use_gravatars', Form::checkbox('use_gravatars', TRUE, (isset($post['use_gravatars']) AND $post['use_gravatars'] == 1) ? TRUE : FALSE));
				?>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="offline">
		<div class="control-group <?php echo isset($errors['maintenance_mode']) ? 'error': ''; ?>">
			<?php $offline0 = (isset($post['maintenance_mode']) && $post['maintenance_mode'] == 0) ? TRUE : FALSE; ?>
			<?php $offline1 = (isset($post['maintenance_mode']) && $post['maintenance_mode'] == 1) ? TRUE : FALSE; ?>
	
			<?php echo Form::label('maintenance_mode', __('Maintenance Mode'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::label('maintenance_mode', Form::radio('maintenance_mode', 0, $offline0).__('Off'), array('class' => 'radio inline')) ?>
				<?php echo Form::label('maintenance_mode', Form::radio('maintenance_mode', 1, $offline1).__('On'), array('class' => 'radio inline')) ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['offline_message']) ? 'error': ''; ?>">
			<?php echo Form::label('offline_message', __('Offline Message'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::textarea('offline_message', $post['offline_message'], array('class' => 'span6', 'rows' => 3)); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['blocked_ips']) ? 'error': ''; ?>">
			<?php echo Form::label('blocked_ips', __("Blocked IP addresses"), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::textarea('blocked_ips', $post['blocked_ips'], array('class' => 'span6', 'rows' => 3)); ?>
				<span class="help-block"><?php echo __("Comma seperated ip's for multiple addresses"); ?></span>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="timezone">
		<div class="control-group <?php echo isset($errors['timezone']) ? 'error': ''; ?>">
			<?php echo Form::label('time_zone', __('Time Zone'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::select('timezone', $timezones, $post['timezone'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['date_time_format']) ? 'error': ''; ?>">
			<?php echo Form::label('date_time_format', __('Date Time Format'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::select('date_time_format', $date_time_formats, $post['date_time_format'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['date_format']) ? 'error': ''; ?>">
			<?php echo Form::label('date_format', __('Date Format'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::select('date_format', $date_formats, $post['date_format'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['time_format']) ? 'error': ''; ?>">
			<?php echo Form::label('time_format', __('Time Format'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::select('time_format', $time_formats, $post['time_format'], array('class' => 'span6')); ?>
			</div>
		</div>
	
		<div class="control-group <?php echo isset($errors['date_first_day']) ? 'error': ''; ?>">
			<?php echo Form::label('date_first_day', __('Week Starts On'), array('class' => 'control-label')); ?>
			<div class="controls">
				<?php echo Form::select('date_first_day', $date_weekdays, $post['date_first_day'], array('class' => 'span6')); ?>
			</div>
		</div>
		
		<div class="control-group <?php echo isset($errors['feed_ttl']) ? 'error': ''; ?>">
			<?php echo Form::label('feed_ttl', __('Feed lifetime (min.)'), array('class' => 'control-label')) ?>
			<div class="controls">
				<?php echo Form::select('feed_ttl', Date::amounts_min(), $post['feed_ttl'], array('class' => 'span6')); ?>
				<span class="help-block"><?php _e("It's a number of minutes that indicates how long a channel can be cached before refreshing from the source."); ?></span>
			</div>
		</div>
	</div>

</div>
<div class="clearfix"></div>
<hr>
<?php echo Form::submit('settings', __('Save Changes'), array('class' => 'btn btn-success pull-right')) ?>
<?php echo Form::close() ?>

