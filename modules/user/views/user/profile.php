<?php defined('SYSPATH') OR die('No direct script access.'); ?>

<div class="row">

	<div id="Panel" class="span3">
		<div id="photo" class="well">
			<div class="Photo">
				<?php echo ( ! empty($account->picture)) ? HTML::resize($account->picture, array('alt' => $account->nick, 'height' => 150, 'width' => 150, 'type' => 'ratio')) : ''; ?>
			</div>

			<?php if( $user->id === $account->id ): ?>
				<ul class="nav nav-list">
					<li><?php echo HTML::anchor('user/photo', '<i class="icon-upload"></i>'.__('Change Picture'), array('id' => 'add-pic')) ?></li>
					<li><?php echo HTML::anchor('user/edit', '<i class="icon-pencil"></i>'.__('Edit Account')) ?></li>
					<li><?php echo HTML::anchor('user/password', '<i class="icon-cog"></i>'.__('Change Password')) ?></li>
				</ul>
			<?php endif;?>
		</div>

		<div class="well about">
			<h4><i class="icon-user"></i> <?php echo __('About'); ?></h4>
			<dl class="dl-horizontallll">
				<dt><?php echo __('Name'); ?></dt>
				<dd><?php echo $account->nick; ?></dd>
				<dt><?php echo __('Joined'); ?></dt>
				<dd><?php echo date('F Y', $account->created); ?></dd>
				<dt><?php echo __('Visits'); ?></dt>
				<dd><?php echo $account->logins; ?></dd>
				<dt><?php echo __('Last Active'); ?></dt>
				<dd><?php echo date('M jS, Y', $account->login); ?> @ <?php echo date('h:i a', $account->login); ?></dd>
				<dt><?php echo __('Roles'); ?></dt>
				<dd>
					<ul class="user-roles">
						<?php foreach ($user->roles() as $role): ?>
							<li><?php echo Text::plain(ucfirst($role->name) ); ?></li>
						<?php endforeach; ?>
					</ul>
				</dd>
				<dt><?php echo __('Age'); ?></dt>
				<dd><?php echo date('y', abs(time()-$user->dob))-70; ?></dd>
			</dl>
		</div>
	</div>

	<div id="Profile" class="span6">
		<h3><?php echo __('Activity'); ?></h3>
		<ul class="nav nav-list">
			<li class="Item activity " id="activity_1">
				<div class="ItemContent Activity">
					<div class="Title"><?php echo __(':nick joined.', array(':nick' => $account->nick) ); ?></div>
					<div class="Excerpt">Welcome to Gleez!</div>
					<div class="Meta"><span class="DateCreated"><?php echo Date::fuzzy_span($account->created); ?></span></div>
				</div>
			</li>
		</ul>
	</div>

</div>

<div class="modal hide fade" id="upload-photo">
	<div class="modal-header">
		<a class="close" data-dismiss="modal">×</a>
		<h3><?php echo __('Upload Photo'); ?></h3>
	</div>
	<div class="modal-data"></div>
</div>

<?php Assets::js('user', 'media/js/user.js', array('jquery'), FALSE, array('weight' => 15)); ?>
<?php Assets::js('user/form', 'media/js/jquery.form-3.27.js', array('jquery'), FALSE, array('weight' => 10)); ?>