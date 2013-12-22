<!DOCTYPE html>
<html id="backend" lang="<?php echo $lang; ?>">
<head>
	<title><?php echo $head_title ?></title>
	<?php echo Meta::tags(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php echo Meta::links(); ?>
	<!-- HTML5 shiv and Respond.js, for IE6-8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<?php echo HTML::script('media/js/html5shiv.js', NULL, TRUE); ?>
		<?php echo HTML::script('media/js/respond.min.js', NULL, TRUE); ?>
	<![endif]-->
	<!--[if gte IE 9]>
		<?php echo HTML::script('media/css/ie-gte-9.css', NULL, TRUE); ?>
	<![endif]-->
	<?php echo Assets::css(); ?>
</head>
<body id="<?php echo $page_id; ?>" class="<?php echo $page_class; ?>">

	<!-- ########## Navbar start ########## -->
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="admin-nav container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only"><?php _e('Toggle navigation'); ?></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<?php echo HTML::anchor($site_url, $site_name, array('class' => 'navbar-brand', 'title' => $site_name)) ?>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav navbar-right">
					<li>
						<?php echo HTML::anchor(URL::site('/user/profile'), User::getAvatar($_user, array('size' => 20)).' '.$_user->name); ?>
					</li>
					<li>
						<a href="<?php echo URL::site('/user/logout'); ?>" title="<?php echo __('Sign Out') ?>"><i class="fa fa-fw fa-power-off"></i></a>
					</li>
				</ul>
			</div><!--/.nav-collapse -->
		</div>
	</div>
	<!-- ########## Navbar end ########## -->

	<!-- ########## admin / container start ########## -->
	<div class="admin-container container">
		<?php include Kohana::find_file('views', 'admin.tpl'); ?>
	</div>
	<!-- ########## template / container end ########## -->

	<?php echo Assets::js(FALSE); ?>
	<?php echo Assets::codes(FALSE); ?>
	<?php echo $profiler; ?>
</body>
</html>
