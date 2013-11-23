<div class="row">
<div class="col-md-4 vcard" itemscope itemtype="http://schema.org/Person">
	<div class="vcard-avatar">
		<?php echo User::getAvatar($user, array('size' => 220)); ?>
	</div>
	<h1 class="vcard-names">
		<span itemprop="name"><?php echo $user->nick; ?></span>
		<em itemprop="additionalName"><?php echo $user->name; ?></em>
	</h1>
	<div class="vcard-details">
		<?php if ($is_owner AND ( ! Config::get('site.use_gravatars', FALSE))): ?>
		<dl>
			<dt><i class="fa fa-upload"></i></dt>
			<dd><?php echo HTML::anchor('user/photo', __('Change Avatar'), array('id' => 'add-pic', 'title' => __('Change your avatar'), 'data-toggle' => 'popup')) ?></dd>
		</dl>
		<?php endif; ?>
		<dl>
			<dt><i class="fa fa-sign-in"></i></dt>
			<dd><span class="caption-label"><?php echo __('Joined on') ?></span><?php echo date('M d, Y', $user->created) ?></dd>
		</dl>
		<dl>
			<dt><i class="fa fa-off"></i></dt>
			<dd><span class="caption-label"><?php echo __('Visits') ?></span><?php echo $user->logins ?></dd>
		</dl>
		<dl title="<?php echo __('Last Active') ?>">
			<dt><i class="fa fa-fire"></i></dt>
			<dd><?php echo date('M d, Y', $user->login) . __(' @ ') .  date('h:i a', $user->login) ?></dd>
		</dl>
		<?php if ($is_owner OR User::is_admin()): ?>
			<dl title="<?php echo __('Email') ?>">
				<dt><i class="fa fa-envelope"></i></dt>
				<dd><a class="email" data-email="<?php echo $user->mail ?>" href="mailto:<?php echo $user->mail ?>"><?php echo $user->mail ?></a></dd>
			</dl>
		<?php endif; ?>
		<?php if ($user->homepage): ?>
			<dl title="<?php echo __('Home Page') ?>">
				<dt><i class="fa fa-link"></i></dt>
				<dd><?php echo HTML::anchor($user->homepage, $user->homepage, array('itemprop' => 'url')); ?></dd>
			</dl>
		<?php endif; ?>

		<dl title="<?php echo __('Birthday') ?>">
			<dt><i class="fa fa-calendar"></i></dt>
			<dd itemprop="birthDate"><?php echo date('M d, Y', $user->dob) ?></dd>
		</dl>
		<?php if (User::is_admin()): ?>
			<dl>
				<dt><i class="fa fa-group"></i></dt>
				<dd class="tagcloud">
					<?php foreach ($user->roles() as $role): ?>
						<span><?php echo Text::plain(ucfirst($role)); ?></span>
					<?php endforeach; ?>
				</dd>
			</dl>
		<?php endif; ?>
	</div>
</div>

<div class="col-md-8">
	<ul class="tabnav">
		<li>
			<?php echo HTML::anchor('user/edit', '<i class="fa fa-pencil"></i> '.__('Edit Account'), array('class' => 'btn btn-default')); ?>
		</li>
		<li>
			<?php echo HTML::anchor('user/password', '<i class="fa fa-cog"></i> '.__('Change Password'), array('class' => 'btn btn-default')); ?>
		</li>
	</ul>
	<div class="col-md-12">
		<?php if ($user->bio): ?>
			<div class="widget">
				<div class="widget-header">
					<h3><?php echo __('Bio') ?></h3>
				</div>
				<div class="widget-content">
					<?php echo Text::plain($user->bio); ?>
				</div>
			</div>
		<?php endif ?>
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

<?php
	Assets::js('user', 'media/js/user.js', array('jquery'), FALSE, array('weight' => 15));
	Assets::js('user/form', 'media/js/jquery.form.min.js', array('jquery'), FALSE, array('weight' => 10));
?>
