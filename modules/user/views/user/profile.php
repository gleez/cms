<div class="row vcard">
	<div class="col-md-9" itemprop="about" itemscope itemtype="http://schema.org/Person">
		<div class="row">
			<div class="col-md-4 col-sm-5">
				<div class="thumbnail vcard-avatar">
					<?php echo User::getAvatar($user, array('size' => 150)); ?>
				</div>

				<div class="list-group">
					<a href="javascript:;" class="list-group-item">
						<i class="fa fa-fw fa-asterisk"></i> <?php echo __('Activity Feed'); ?>
						<i class="fa fa-fw fa-chevron-right list-group-chevron"></i>
					</a>
					<a href="javascript:;" class="list-group-item">
						<i class="fa fa-fw fa-group"></i> <?php echo __('Friends'); ?>
						<i class="fa fa-fw fa-chevron-right list-group-chevron"></i>
						<span class="badge">7</span>
					</a>
					<?php echo HTML::anchor('user/edit', '<i class="fa fa-fw fa-cog"></i> '.__('Settings') .'<i class="fa fa-chevron-right list-group-chevron"></i>' , array('class' => 'list-group-item')); ?>
				</div>
			</div>

			<div class="col-md-8 col-sm-7">
				<h2 class="col-md-12 vcard-names">
					<span itemprop="name"><?php echo $user->nick; ?></span>
					<em itemprop="additionalName"><?php echo $user->name; ?></em>
				</h2>

				<div class="col-md-12 vcard-details">
					<?php if ($is_owner OR User::is_admin()): ?>
						<dl title="<?php echo __('Email') ?>">
							<dt><i class="fa fa-fw fa-envelope"></i></dt>
							<dd><a class="email" data-email="<?php echo $user->mail ?>" href="mailto:<?php echo $user->mail ?>"><?php echo $user->mail ?></a></dd>
						</dl>
					<?php endif; ?>
					<?php if ($user->homepage): ?>
						<dl title="<?php echo __('Home Page') ?>">
							<dt><i class="fa fa-fw fa-globe"></i></dt>
							<dd><?php echo HTML::anchor($user->homepage, $user->homepage, array('itemprop' => 'url')); ?></dd>
						</dl>
					<?php endif; ?>

					<dl title="<?php echo __('Birthday') ?>">
						<dt><i class="fa fa-fw fa-calendar"></i></dt>
						<dd>
							<time itemprop="birthDate" content="<?php echo Date::date_format($user->dob, DateTime::ISO8601)?>" datetime="<?php echo Date::date_format($user->dob, DateTime::ISO8601)?>">
								<?php echo Date::date_format($user->dob); ?>
							</time>
						</dd>
					</dl>
					<?php if (User::is_admin()): ?>
						<dl title="<?php echo __('User Groups') ?>">
							<dt><i class="fa fa-fw fa-group"></i></dt>
							<dd class="tagcloud">
								<?php foreach ($user->roles() as $role): ?>
									<span><?php echo Text::plain(ucfirst($role)); ?></span>
								<?php endforeach; ?>
							</dd>
						</dl>
					<?php endif; ?>
				</div>
				<hr>
				<div class="col-md-12 bio">
					<?php if ($user->bio): ?>
						<div title="<?php _e('Bio')?>">
							<p><?php echo Text::plain($user->bio); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-3 col-sm-6 col-sidebar-right">
		<div class="list-group">
			<a href="#" class="list-group-item"><h3 class="pull-right"><i class="fa fa-sign-in"></i></h3>
				<h4 class="list-group-item-heading"><?php echo date('M d, Y', $user->created); ?></h4>
				<p class="list-group-item-text"><?php echo __('Joined on'); ?></p>
			</a>
			<a href="#" class="list-group-item"><h3 class="pull-right"><i class="fa fa-power-off"></i></h3>
				<h4 class="list-group-item-heading"><?php echo $user->logins; ?></h4>
				<p class="list-group-item-text"><?php echo __('Visits'); ?></p>
			</a>
			<a href="#" class="list-group-item"><h3 class="pull-right"><i class="fa fa-fire"></i></h3>
				<h4 class="list-group-item-heading"><?php echo Date::date_format($user->login, 'h:i a M d, Y'); ?></h4>
				<p class="list-group-item-text"><?php echo  __('Last Visit'); ?></p>
			</a>
		</div>
	</div>
</div>

<?php
	// @todo Should be moved to controller?
	Assets::js('user', 'media/js/user.js', array('jquery'), FALSE, array('weight' => 15));
	Assets::js('user/form', 'media/js/jquery.form.min.js', array('jquery'), FALSE, array('weight' => 10));
?>
