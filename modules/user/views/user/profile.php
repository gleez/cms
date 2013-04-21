<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<div class="row-fluid" itemscope itemtype="http://schema.org/Person">

	<div id="Panel" class="span4">
		<div id="photo" class="well">
			<div class="Photo">
				<?php if ($is_owner): ?>
					<?php echo ( ! empty($user->picture)) ? HTML::resize($user->picture, array('alt' => $user->nick, 'height' => 150, 'width' => 150, 'type' => 'resize', 'itemprop' => 'image')) : '<div class="empty-photo"><i class="icon-camera-retro icon-4x"></i></div>'; ?>
				<?php else: ?>
					<?php echo ( ! empty($user->picture)) ? HTML::resize($user->picture, array('alt' => $user->nick, 'height' => 150, 'width' => 150, 'type' => 'resize', 'itemprop' => 'image')) : '<div class="empty-photo"><i class="icon-camera-retro icon-4x"></i></div>'; ?>
				<?php endif; ?>
			</div>

			<?php if ($is_owner): ?>
				<ul class="nav nav-list">
					<li><?php echo HTML::anchor('user/photo', '<i class="icon-upload"></i>'.__('Change Avatar'), array('id' => 'add-pic', 'title' => __('Change your avatar'))) ?></li>
					<li><?php echo HTML::anchor('user/edit', '<i class="icon-pencil"></i>'.__('Edit Account')) ?></li>
					<li><?php echo HTML::anchor('user/password', '<i class="icon-cog"></i>'.__('Change Password')) ?></li>
				</ul>
			<?php endif;?>
		</div>

		<div class="well about">
			<h4><i class="icon-user"></i> <?php echo __('About'); ?></h4>
			<dl>
				<dt><?php echo __('Name'); ?></dt>
				<dd itemprop="name"><?php echo $user->nick; ?></dd>
				<dt><?php echo __('Birthday'); ?></dt>
				<dd itemprop="birthDate"><?php echo date('M d, Y', $user->dob); ?></dd>
				<dt><?php echo __('Joined on'); ?></dt>
				<dd><?php echo date('M d, Y', $user->created); ?></dd>
				<?php if ($is_owner OR User::is_admin()): ?>
					<dt><?php echo __('Email'); ?></dt>
					<dd><?php echo Text::auto_link_emails($user->mail); ?></dd>
				<?php endif; ?>
				<?php if ($user->homepage): ?>
					<dt><?php echo __('Home Page'); ?></dt>
					<dd><?php echo Text::auto_link($user->homepage); ?></dd>
				<?php endif; ?>
				<dt><?php echo __('Visits'); ?></dt>
				<dd><?php echo $user->logins; ?></dd>
				<dt><?php echo __('Last Active'); ?></dt>
				<dd><?php echo date('M d, Y', $user->login); ?> @ <?php echo date('h:i a', $user->login); ?></dd>
				<?php if (User::is_admin()): ?>
				<dt><?php echo __('Roles'); ?></dt>
				<dd>
					<ul class="user-roles">
						<?php foreach ($user->roles() as $role): ?>
							<li><?php echo Text::plain(ucfirst($role)); ?></li>
						<?php endforeach; ?>
					</ul>
				</dd>
				<?php endif; ?>
			</dl>
		</div>
	</div>

	<div id="Profile" class="span8">
		<h3><?php echo __('Activity'); ?></h3>
		<ul class="nav nav-list">
			<li class="Item activity " id="activity_1">
				<div class="ItemContent Activity">
					<div class="Title"><?php echo __(':nick joined.', array(':nick' => $user->nick)); ?></div>
					<div class="Excerpt"><?php echo __('Welcome to Gleez!') ?></div>
					<div class="Meta"><span class="DateCreated"><?php echo __(Date::fuzzy_span($user->created)); ?></span></div>
				</div>
			</li>
		</ul>
		<h3><?php echo __('Bio'); ?></h3>
		<div class="ItemContent">
			<?php echo Text::plain($user->bio); ?>
		</div>
	</div>

</div>

<div class="modal hide fade in" id="upload-photo" role="dialog" tabindex="-1" aria-hidden="true">
	<div class="modal-header">
		<?php echo Form::button('close_window', '&times;', array('class' => 'close', 'data-dismiss' => 'modal', 'aria-hidden' => 'true')); ?>
		<h3><?php echo __('Uploading Photos'); ?></h3>
	</div>
	<div class="modal-data"></div>
</div>

<?php Assets::js('user', 'media/js/user.js', array('jquery'), FALSE, array('weight' => 15)); ?>
<?php Assets::js('user/form', 'media/js/jquery.form-3.27.js', array('jquery'), FALSE, array('weight' => 10)); ?>