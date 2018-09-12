<!DOCTYPE html>
<html id="backend" lang="<?php echo $lang; ?>">
<head>
	<title><?php echo $head_title ?></title>
	<?php echo Meta::tags(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php echo Meta::links(); ?>
	<?php echo Assets::css(); ?>
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<?php echo HTML::script('media/js/html5shiv.js', NULL, TRUE); ?>
		<?php echo HTML::script('media/js/respond.min.js', NULL, TRUE); ?>
    <![endif]-->
	<!--[if gt IE 9]>
		<?php echo HTML::script('media/css/ie-gte-9.css', NULL, TRUE); ?>
	<![endif]-->
</head>
<body id="<?php echo $page_id; ?>" class="<?php echo $page_class; ?>">

	<!-- ########## Navbar start ########## -->
	<header class="navbar navbar-inverse" role="banner">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only"><?php echo __('Toggle navigation'); ?></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<?php echo HTML::anchor($site_url, $site_name, array('class' => 'navbar-brand', 'title' => $site_name)) ?>
			</div>
			<nav class="navbar-collapse collapse" role="navigation">
				<ul class="nav navbar-nav navbar-right">
					<li>
						<?php echo HTML::anchor(Route::get('user')->uri(array('action' => 'profile')), User::getAvatar($_user, array('size' => 20)).' '.$_user->name); ?>
					</li>
					<li>
						<a href="<?php echo URL::site('/user/logout'); ?>" title="<?php echo __('Sign Out') ?>"><i class="fa fa-fw fa-power-off"></i></a>
					</li>
				</ul>
			</nav><!--/.nav-collapse -->
		</div>
	</header>
	<nav class="navbar-sub">
	</nav>
	<!-- ########## Navbar end ########## -->

	<!-- ########## admin / container start ########## -->
	<main id="content" class="backend-main" role="main">
		<?php include Kohana::find_file('views', 'admin.tpl'); ?>
	</main>
	<!-- ########## template / container end ########## -->

	<!-- ########## Footer start ########## -->
	<footer class="footer navbar">
		<div class="container-fluid text-muted">
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
	</footer>
	<!-- ########## Footer end ########## -->

	<?php echo Assets::js(FALSE); ?>
	<?php echo Assets::codes(FALSE, $getNonce); ?>
	<?php echo $profiler; ?>
</body>
</html>
