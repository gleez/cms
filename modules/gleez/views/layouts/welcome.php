<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
	<title><?php echo $head_title ?></title>
	<?php echo Meta::tags(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php echo Meta::links(); ?>
	<?php echo Assets::css(); ?>
	<link href='//fonts.googleapis.com/css?family=Kaushan+Script' rel='stylesheet' type='text/css'>
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<?php echo HTML::script('media/js/html5shiv.js', NULL, TRUE); ?>
		<?php echo HTML::script('media/js/respond.min.js', NULL, TRUE); ?>
    <![endif]-->
</head>
<body id="<?php echo $page_id; ?>" class="<?php echo $page_class; ?>" <?php echo $schemaType ? 'itemscope itemtype="http://schema.org/'.$schemaType.'"' : ''?>>

	<!-- ########## Navbar start ########## -->
	<header class="navbar navbar-inverse navbar-fixed-top" role="banner">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only"><?php echo __('Toggle navigation'); ?></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<?php echo HTML::anchor('/', HTML::image($site_logo, array('alt' => $site_slogan, 'class' => 'logo')), array('class' => 'navbar-brand', 'title' => $site_name)) ?>
			</div>
			<nav class="navbar-collapse collapse" role="navigation">
				<?php echo $primary_menu; ?>

				<ul class="nav navbar-nav navbar-right">
						<?php if (User::is_guest()): ?>
							<?php if (Kohana::$config->load('auth')->get('register')): ?>
								<li><a href="<?php echo URL::site('/user/register'); ?>"><?php echo __('Sign Up')?></a></li>
							<?php endif; ?>
							<li><a href="<?php echo URL::site('/user/login'); ?>"><i class="fa fa-fw fa-chevron-left"></i><?php echo __('Sign In') ?></a></li>
						<?php else:  ?>
						<li class="dropdown">
							<?php echo HTML::anchor('#', User::getAvatar($_user, array('size' => 20)).' '.$_user->name.'<b class="caret"></b>', array('data-toggle' => 'dropdown', 'class' => 'dropdown-toggle')); ?>

							<ul class="dropdown-menu">
								<li class="dropdown-header"><strong><?php echo $_user->nick ?></strong></li>
								<li class="dropdown-header"><?php echo $_user->mail ?></li>
								<li class="divider"></li>
								<li class="dropdown-header"><?php _e('Profile') ?></li>
								<li><a href="<?php echo URL::site('/user/profile') ?>"><i class="fa fa-fw fa-cog"></i> <?php echo __('Profile') ?></a></li>
								<li><a href="<?php echo URL::site('/message/inbox') ?>"><i class="fa fa-fw fa-envelope"></i> <?php echo __('Messages') ?></a></li>
								<li class="dropdown-header"><?php _e('Settings') ?></li>
								<li><a href="<?php echo URL::site('/user/edit') ?>"><i class="fa fa-fw fa-pencil"></i> <?php echo __('Profile Settings') ?></a></li>
								<li><a href="<?php echo URL::site('/user/password') ?>"><i class="fa fa-fw fa-lock"></i> <?php echo __('Change Password') ?></a></li>
								<li class="divider"></li>
								<?php if (User::is_admin()): ?>
									<li><a href="<?php echo URL::site('/admin') ?>"><i class="fa fa-fw fa-dashboard"></i> <?php echo __('Dashboard') ?></a></li>
								<?php endif; ?>
								<li><a href="<?php echo URL::site('/user/logout'); ?>"><i class="fa fa-fw fa-power-off"></i> <?php echo __('Sign Out') ?></a></li>
							</ul>
						</li>
						<?php endif; ?>
				</ul>
			</nav>
		</div>
	</header>
	<!-- ########## Navbar end ########## -->

	<!-- ########## template / container start ########## -->
	<main id="content" class="welcome-main" role="main">
		<?php include Kohana::find_file('views', 'welcome'); ?>
	</main>
	<!-- ########## template / container end ########## -->

	<!-- ########## Footer start ########## -->
	<footer class="footer" role="contentinfo">
		<?php $footer = Widgets::instance()->render('footer', 'footer'); ?>
		<?php if ($footer): ?>
			<div class="extra">
				<div class="container">
					<div class="row">
						<?php echo $footer; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="footer-terms">
			<div class="container text-muted">
				<div class="row">
					<div class="col-xs-6 col-md-6">
						<p class="pull-left"><?php echo __('&copy; :year :site', array(':year' => date('Y'), ':site' => HTML::anchor(URL::site(false, true), $site_name)));?></p>
					</div>
					<div class="col-xs-6 col-md-6">
						<p class="pull-right"><?php echo __(':powerdby v{gleez_version}', array(':powerdby' => HTML::anchor('http://gleezcms.org/', 'Gleez CMS')))?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 text-center" id="footer-system-info">
						<small><?php echo __('Rendered in {execution_time}, using {memory_usage} of memory.')?></small>
					</div>
				</div>
			</div>
		</div>
	</footer>
	<!-- ########## Footer end ########## -->

	<?php echo Assets::js(FALSE); ?>
	<?php echo Assets::codes(FALSE); ?>
	<?php echo $profiler; ?>
</body>
</html>
