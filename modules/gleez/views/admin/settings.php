<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="help">
	<?php echo __('The main parameters of the site set up here.') ?>
</div>

<?php echo Form::open(Route::get('admin/setting')->uri(), array('id'=>'settings-form', 'class'=>'form')) ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

    <?php //make sure a valid sitename is set ?>
    <?php $post['site_url'] = ($post['site_url'] === 'www.example.com') ? URL::site(NULL, TRUE) : $post['site_url']; ?>

<div class="row-fluid">

    <div id="settings-left" class="span6">

	<div class="control-group <?php echo isset($errors['site_name']) ? 'error': ''; ?>">
	    <?php echo Form::label('site_name', __('Site Title'), array('class' => 'control-label')) ?>
	    <?php echo Form::input('site_name', $post['site_name'], array('class' => 'text medium wrap')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['site_slogan']) ? 'error': ''; ?>">
	    <?php echo Form::label('site_slogan', __('Tagline'), array('class' => 'control-label')) ?>
	    <?php echo Form::input('site_slogan', $post['site_slogan'], array('class' => 'text medium wrap')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['site_email']) ? 'error': ''; ?>">
	    <?php echo Form::label('site_email', __('E-mail address'), array('class' => 'control-label')) ?>
	    <?php echo Form::input('site_email', $post['site_email'], array('class' => 'text medium wrap')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['site_url']) ? 'error': ''; ?>">
	    <?php echo Form::label('site_url', __('Website address'), array('class' => 'control-label')) ?>
	    <?php echo Form::input('site_url', $post['site_url'], array('class' => 'text medium wrap')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['site_mission']) ? 'error': ''; ?>">
	    <?php echo Form::label('site_mission', __('Mission'), array('class' => 'control-label')) ?>
	    <?php echo Form::textarea('site_mission', $post['site_mission'], array('class' => 'textarea medium wrap', 'rows' => 5)) ?>
	</div>

        <div class="control-group <?php echo isset($errors['site_logo']) ? 'error': ''; ?>">
	    <?php echo Form::label('site_logo', __('Logo'), array('class' => 'control-label')) ?>
	    <?php echo Form::input('site_logo', $post['site_logo'], array('class' => 'input-large')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['site_favicon']) ? 'error': ''; ?>">
	    <?php echo Form::label('site_favicon', __('Favicon'), array('class' => 'control-label')) ?>
	    <?php echo Form::input('site_favicon', $post['site_favicon'], array('class' => 'input-large')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['theme']) ? 'error': ''; ?>">
	    <?php echo Form::label('theme', __('Site Theme'), array('class' => 'wrap')) ?>
	    <?php echo Form::select('theme', Theme::Avaliable(), $post['theme'], array('class' => 'input-large')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['admin_theme']) ? 'error': ''; ?>">
	    <?php echo Form::label('admin_theme', __('Admin Theme'), array('class' => 'control-label')) ?>
	    <?php echo Form::select('admin_theme', Theme::Avaliable(), $post['admin_theme'], array('class' => 'input-large')); ?>
	</div>

    </div>

    <div id="settings-right"  class="span6">

	<div class="control-group <?php echo isset($errors['maintenance_mode']) ? 'error': ''; ?>">
	    <?php $offline0 = (isset($post['maintenance_mode']) && $post['maintenance_mode'] == 0) ? TRUE : FALSE; ?>
	    <?php $offline1 = (isset($post['maintenance_mode']) && $post['maintenance_mode'] == 1) ? TRUE : FALSE; ?>

	    <?php echo Form::label('maintenance_mode', __('Maintenance Mode'), array('class' => 'control-label')) ?>
	    <div class="controls">
		<?php echo Form::label('maintenance_mode', Form::radio('maintenance_mode', 0, $offline0).__('Off'), array('class' => 'radio inline')) ?>
		<?php echo Form::label('maintenance_mode', Form::radio('maintenance_mode', 1, $offline1).__('On'), array('class' => 'radio inline')) ?>
	    </div>
	</div>

	<div class="control-group <?php echo isset($errors['offline_message']) ? 'error': ''; ?>">
	    <?php echo Form::label('offline_message', __('Offline Message'), array('class' => 'control-label')) ?>
	    <?php echo form::textarea('offline_message', $post['offline_message'], array('class' => 'textarea medium wrap', 'rows' => 5)) ?>
        </div>

	<div class="control-group <?php echo isset($errors['timezone']) ? 'error': ''; ?>">
	    <?php echo Form::label('time_zone', __('Time Zone'), array('class' => 'wrap')) ?>
	    <?php echo Form::select('timezone', $timezones, $post['timezone'], array('class' => 'input-large time-zone')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['date_time_format']) ? 'error': ''; ?>">
	    <?php echo Form::label('date_time_format', __('Date Time Format'), array('class' => 'control-label')) ?>
	    <?php echo Form::select('date_time_format', $date_time_formats, $post['date_time_format'], array('class' => 'input-large')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['date_format']) ? 'error': ''; ?>">
	    <?php echo Form::label('date_format', __('Date Format'), array('class' => 'control-label')) ?>
	    <?php echo Form::select('date_format', $date_formats, $post['date_format'], array('class' => 'input-large')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['time_format']) ? 'error': ''; ?>">
	    <?php echo Form::label('time_format', __('Time Format'), array('class' => 'control-label')) ?>
	    <?php echo Form::select('time_format', $time_formats, $post['time_format'], array('class' => 'input-large')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['date_first_day']) ? 'error': ''; ?>">
	    <?php echo Form::label('date_first_day', __('Week Starts On'), array('class' => 'control-label')) ?>
	    <?php echo Form::select('date_first_day', $date_weekdays, $post['date_first_day'], array('class' => 'input-large')); ?>
	</div>

	<div class="control-group <?php echo isset($errors['seo_url']) ? 'error': ''; ?>">
	    <?php $seo_url_off = (isset($post['seo_url']) && $post['seo_url'] == 0) ? TRUE : FALSE; ?>
	    <?php $seo_url_on = (isset($post['seo_url']) && $post['seo_url'] == 1) ? TRUE : FALSE; ?>

	    <?php echo Form::label('seo_url', __('SEO urls'), array('class' => 'control-label')) ?>

	    <div class="controls">
		<?php echo Form::label('seo_url', Form::radio('seo_url', 0, $seo_url_off).__('Off'), array('class' => 'radio inline')) ?>
		<?php echo Form::label('seo_url', Form::radio('seo_url', 1, $seo_url_on).__('On'), array('class' => 'radio inline')) ?>
	    </div>
	</div>

	<div class="control-group <?php echo isset($errors['front_page']) ? 'error': ''; ?>">
	    <?php echo Form::label('front_page', __('Front page'), array('class' => 'control-label')) ?>
	    <?php echo Form::input('front_page', $post['front_page'], array('class' => 'input-large')); ?>
	</div>

    </div>

</div>
<div class="clearfix"></div>
<?php echo Form::submit('settings', __('Save Changes'), array('class' => 'btn btn-primary btn-large')) ?>
<?php echo Form::close() ?>

