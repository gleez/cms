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
		</div>
	</header>
	
	<!-- ########## Navbar end ########## -->

	<!-- ########## admin / container start ########## -->
	<main id="content" class="backend-main" role="main">
		<div class="container-fluid">
			<div class="content1 row">
				<div class="col-sm-12 col-md-12">
					<div class="wrapper">
						<div class="content-wrapper">
							<?php if ($messages): ?>
								<!-- ########## Messages start ########## -->
								<div id="messages" class="messages">
									<?php echo $messages ?>
								</div>
								<!-- ########## Messages end ########## -->
							<?php endif; ?>
		
							<?php if ($tabs): ?>
								<div id="tabs-actions">
									<div id="tabs"><?php echo $tabs; ?></div>
								</div>
							<?php endif; ?>
		
							<div id="content-body" class="<?php echo $tabs ? 'with-tabs' : 'without-tabs'?>">
								<?php echo $content; ?>
							</div>
						</div>
					</div>
				</div>
		
			</div>
		</div>
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
	<?php echo Assets::codes(FALSE); ?>
	<?php echo $profiler; ?>
</body>
</html>