<ul id="tabnav" class="nav nav-pills nav-stacked">
	<li class="active">
		<a href="#profile-tab" data-toggle="tab">
			<i class="fa fa-fw fa-user"></i> <?php echo __('Profile Settings'); ?>
		</a>
	</li>
	<li>
		<?php echo HTML::anchor('user/password', '<i class="fa fa-fw fa-lock"></i> '.__('Change Password')); ?>
	</li>
	<?php if (! Config::get('site.use_gravatars', FALSE)): ?>
		<li>
			<?php echo HTML::anchor('user/photo', '<i class="fa fa-fw fa-upload"></i> ' . __('Change Avatar'), array('id' => 'add-pic1', 'title' => __('Change your avatar'))) ?>
		</li>
	<?php endif; ?>
</ul>
