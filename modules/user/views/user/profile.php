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
					<div class="col-md-7">
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
					<div class="col-md-5">
						<?php if($request AND ($request == $user->id )):?>
							<?php echo HTML::anchor("buddy/accept/".$user->id , __('Accept'), array('class' => 'btn btn-success')); ?>
							<?php echo HTML::anchor("buddy/reject/".$user->id , __('Reject'), array('class' => 'btn btn-danger')); ?>
						<?php elseif($request AND ! $isfriend AND ! $is_owner) : ?>
							<div class= 'btn btn-info'><?php echo __('Pending Request'); ?></div>
						<?php elseif($isfriend AND ! $is_owner): ?>
							<div class= 'btn btn-info'><?php echo __('Friend'); ?></div>
						<?php elseif(! $request AND ! $isfriend AND ! $is_owner): ?>
							<?php echo HTML::anchor("buddy/add/".$user->id , __('Send Request'), array('class' => 'btn btn-success')); ?>
						<?php endif; ?>
					</div>
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
				<h4 class="list-group-item-heading"><?php echo Date::date_format($user->created, 'M d, Y'); ?></h4>
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

		<div class="list-group list-all panel panel-info">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo __('Friends'); ?></h3>
			</div>
			
			<?php foreach($friends as $id): ?>
				<div class="list-group-item friends panel-body">
					<?php $accept = User::lookup($id); ?>
					<?php echo HTML::anchor("user/view/".$accept->id , User::getAvatar($accept), array('class' => 'action-view', 'rel'=>"popover", 'data-placement'=>"right", 'rel1'=>"tooltip", 'data-html'=>"true", 'data-original-title'=>"<strong>$accept->nick</strong>" )) ?>
					<?php echo HTML::anchor("user/view/".$accept->id , $accept->nick, array('class' => 'action-view', 'title'=> __('view profile'))) ?>
		
					<?php if($is_owner): ?>
						<?php echo HTML::anchor("buddy/delete/".$accept->id , '<i class="fa fa-trash-o"></i>', array('class'=>'action-delete pull-right', 'title'=> __('Delete'))); ?>
					<?php endif; ?>
				</div>
			<?php endforeach ;?>
			
			<?php if( !empty($friends) ): ?>
				<div class="panel-footer">
					<div class="row">
						<?php echo HTML::anchor("buddy/list/".$user->id , __('All'),  array('class' => 'all-view pull-right', 'title'=> __('All'))); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>