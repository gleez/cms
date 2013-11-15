<div class="help">
	<p><?php _e('The main parameters of the site set up here. The following are all of the settings that define your site as a whole: settings which determine how your site behaves, how you interact with your site, and how the rest of the world interacts with your site.')?></p>
</div>

<ul class="nav nav-tabs">
    <li class="active"><a href="#general" data-toggle="tab"><?php _e('General')?></a></li>
    <li><a href="#theme" data-toggle="tab"><?php _e('Appearance')?></a></li>
    <li><a href="#timezone" data-toggle="tab"><?php _e('Date & Time')?></a></li>
    <li><a href="#seo" data-toggle="tab"><?php _e('SEO & Tracking')?></a></li>
    <li><a href="#offline" data-toggle="tab"><?php _e('Maintenance')?></a></li>
</ul>

<?php echo Form::open($action, array('id'=>'settings-form', 'class'=>'form-horizontal', 'role' => 'form'))?>

	<?php include Kohana::find_file('views', 'errors/partial')?>

	<?php // @todo Move to controller ?>
	<?php $post['site_url'] = ($post['site_url'] === 'www.example.com') ? URL::site(NULL, TRUE) : $post['site_url']; ?>

<div class="tab-content col-sm-12">

	<div class="tab-pane active" id="general">
		<div class="form-group <?php echo isset($errors['site_name']) ? 'has-error': ''; ?>">
			<?php echo Form::label('site_name', __('Site Title'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::input('site_name', $post['site_name'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['site_slogan']) ? 'has-error': ''; ?>">
			<?php echo Form::label('site_slogan', __('Tagline'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::input('site_slogan', $post['site_slogan'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['site_email']) ? 'has-error': ''; ?>">
			<?php echo Form::label('site_email', __('E-mail address'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::input('site_email', $post['site_email'], array('class' => 'form-control', 'type' => 'email'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['site_url']) ? 'has-error': ''; ?>">
			<?php echo Form::label('site_url', __('Website address'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-sm-8">
					<?php echo Form::input('site_url', $post['site_url'], array('class' => 'form-control', 'type' => 'url'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['site_mission']) ? 'has-error': ''; ?>">
			<?php echo Form::label('site_mission', __('Mission'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::textarea('site_mission', $post['site_mission'], array('class' => 'form-control', 'rows' => 6))?>
				</div>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="theme">
		<div class="form-group <?php echo isset($errors['site_logo']) ? 'has-error': ''; ?>">
			<?php echo Form::label('site_logo', __('Logo'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::input('site_logo', $post['site_logo'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['site_favicon']) ? 'has-error': ''; ?>">
			<?php echo Form::label('site_favicon', __('Favicon'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::input('site_favicon', $post['site_favicon'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['theme']) ? 'has-error': ''; ?>">
			<?php echo Form::label('theme', __('Site Theme'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::select('theme', Theme::available(), $post['theme'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['admin_theme']) ? 'has-error': ''; ?>">
			<?php echo Form::label('admin_theme', __('Admin Theme'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::select('admin_theme', Theme::available(), $post['admin_theme'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['use_gravatars']) ? 'has-error': ''; ?>">
			<?php echo Form::label('use_gravatars', __('Use Gravatars'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<div class="checkbox">
						<?php
							// @important the hidden filed should be before checkbox
							echo Form::hidden('use_gravatars', 0);
							echo Form::label('use_gravatars', Form::checkbox('use_gravatars', TRUE, (isset($post['use_gravatars']) AND $post['use_gravatars'] == 1) ? TRUE : FALSE));
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="timezone">
		<div class="form-group <?php echo isset($errors['timezone']) ? 'has-error': ''; ?>">
			<?php echo Form::label('time_zone', __('Time Zone'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::select('timezone', $timezones, $post['timezone'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['date_time_format']) ? 'has-error': ''; ?>">
			<?php echo Form::label('date_time_format', __('Date Time Format'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::select('date_time_format', $date_time_formats, $post['date_time_format'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['date_format']) ? 'has-error': ''; ?>">
			<?php echo Form::label('date_format', __('Date Format'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::select('date_format', $date_formats, $post['date_format'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['time_format']) ? 'has-error': ''; ?>">
			<?php echo Form::label('time_format', __('Time Format'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::select('time_format', $time_formats, $post['time_format'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['date_first_day']) ? 'has-error': ''; ?>">
			<?php echo Form::label('date_first_day', __('Week Starts On'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::select('date_first_day', $date_weekdays, $post['date_first_day'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['feed_ttl']) ? 'has-error': ''; ?>">
			<?php echo Form::label('feed_ttl', __('Feed lifetime (min.)'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-4">
					<?php echo Form::select('feed_ttl', Date::amounts_min(), $post['feed_ttl'], array('class' => 'form-control'))?>
				</div>
				<span class="help-block"><?php _e("It's a number of minutes that indicates how long a channel can be cached before refreshing from the source.")?></span>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="seo">
		<div class="form-group <?php echo isset($errors['keywords']) ? 'has-error': ''; ?>">
			<?php echo Form::label('keywords', __('Keywords for search engines'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::textarea('keywords', $post['keywords'], array('class' => 'form-control', 'rows' => 4))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['description']) ? 'has-error': ''; ?>">
			<?php echo Form::label('description', __('Description for search engines'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::textarea('description', $post['description'], array('class' => 'form-control', 'rows' => 4))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['front_page']) ? 'has-error': ''; ?>">
			<?php echo Form::label('front_page', __('Front page'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::input('front_page', $post['front_page'], array('class' => 'form-control'))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['google_ua']) ? 'has-error': ''; ?>">
			<?php echo Form::label('google_ua', __('Google Analytics'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::input('google_ua', $post['google_ua'], array('class' => 'form-control', 'placeholder' => 'UA-12345678-9'))?>
				</div>
			</div>
		</div>
	</div>

	<div class="tab-pane" id="offline">
		<div class="form-group <?php echo isset($errors['maintenance_mode']) ? 'has-error': ''; ?>">
			<?php $offline0 = (isset($post['maintenance_mode']) && $post['maintenance_mode'] == 0) ? TRUE : FALSE; ?>
			<?php $offline1 = (isset($post['maintenance_mode']) && $post['maintenance_mode'] == 1) ? TRUE : FALSE; ?>

			<?php echo Form::label('maintenance_mode', __('Maintenance Mode'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<div class="radio">
						<?php echo Form::label('maintenance_mode', Form::radio('maintenance_mode', 0, $offline0).__('Off'))?>
					</div>
					<div class="radio">
						<?php echo Form::label('maintenance_mode', Form::radio('maintenance_mode', 1, $offline1).__('On'))?>
					</div>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['offline_message']) ? 'has-error': ''; ?>">
			<?php echo Form::label('offline_message', __('Offline Message'), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::textarea('offline_message', $post['offline_message'], array('class' => 'form-control', 'rows' => 3))?>
				</div>
			</div>
		</div>

		<div class="form-group <?php echo isset($errors['blocked_ips']) ? 'has-error': ''; ?>">
			<?php echo Form::label('blocked_ips', __("Blocked IP addresses"), array('class' => 'col-sm-3 control-label'))?>
			<div class="col-sm-9">
				<div class="col-xs-8">
					<?php echo Form::textarea('blocked_ips', $post['blocked_ips'], array('class' => 'form-control', 'rows' => 3))?>
					<span class="help-block"><?php echo __("Comma separated ip's for multiple addresses")?></span>
				</div>
			</div>
		</div>
	</div>

</div>

<div class="form-group ab-wrapper">
	<div class="col-sm-12">
		<?php echo Form::button('settings', __('Save Changes'), array('class' => 'btn btn-success pull-right', 'type' => 'submit'))?>
	</div>
</div>

<?php echo Form::close()?>
