<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?php echo $title; ?> | Gleez CMS</title>
	<?php
		foreach ($styles as $style => $media)
		{
			echo HTML::style($style, array('media' => $media), TRUE).PHP_EOL;
		}
	?>
	<link rel="shortcut icon" href="<?php echo URL::site($link); ?>" type="image/x-icon">
	<!-- HTML5 shim and Respond.js, for IE6-8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="/media/js/html5.js"></script>
		<script src="/media/js/respond.min.js"></script>
	<![endif]-->
	<?php echo Assets::css(); ?>
</head>

<body class="<?php echo $page_class; ?>">
	<div id="wrap">
		<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container">
				<a class="navbar-brand" href="/"><?php echo __('Gleez Installer'); ?></a>
			</div>
		</nav>

		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<?php include Kohana::find_file('views', 'errors/ielt7'); ?>
					<div class="row">
						<div class="col-md-9">
							<?php if ( ! empty($error)): ?>
								<div class="alert alert-danger">
									<p><?php echo HTML::chars($error) ?></p>
								</div>
							<?php endif; ?>
							<div class="page-header">
								<h1><?php echo HTML::chars($title) ?></h1>
							</div>
							<?php echo $content; ?>
							<div class="progress progress-striped active">
								<div style="width:<?php echo $_activity; ?>%" class="progress-bar progress-bar-info" role="progressbar"  aria-valuenow="<?php echo $_activity; ?>" aria-valuemin="0" aria-valuemax="100">
									<span class="sr-only"><?php echo $_activity; ?>% Complete </span>
								</div>
							</div>
						</div>
						<div class="col-md-3 menu">
							<ol>
								<?php foreach ($menu as $item): ?>
									<li><?php echo $item; ?></li>
								<?php endforeach; ?>
							</ol>
							<hr>
							<blockquote>
								<?php
									echo __('Did something go wrong? Try the :github.', array(':github' => HTML::anchor('https://github.com/gleez/cms/issues', 'Github Issues') ));
								?>
							</blockquote>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="footer">
		<div class="container">
			<div class="credits">
				<p class="text-muted"><?php echo __('Powered by :gleez', array(':gleez' => HTML::anchor('http://gleezcms.org/', 'Gleez CMS') )); ?>&nbsp;<?php echo Gleez::getVersion() ?></p>
				<p class="text-muted">&copy; 2011-<?php echo date('Y') ?> Gleez Technologies</p>
			</div>
		</div>
	</div>
</body>
</html>
