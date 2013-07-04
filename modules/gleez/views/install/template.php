<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?> | Gleez CMS</title>
	<?php
		foreach ($styles as $style => $media)
		{
			echo HTML::style($style, array('media' => $media), TRUE).PHP_EOL;
		}
	?>
	<link rel="shortcut icon" href="<?php echo URL::site($link); ?>" type="image/x-icon">
	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>

<body class="<?php echo $page_class; ?>">
	<div id="wrap">
		<div class="navbar navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container">
					<a class="brand" href="/"><?php echo __('Gleez Installer'); ?></a>
				</div>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<div class="span12">
					<?php include Kohana::find_file('views', 'errors/ielt7'); ?>
					<div class="row">
						<div class="span9">
							<?php if ( ! empty($error)): ?>
								<div class="alert alert-error">
									<p><?php echo HTML::chars($error) ?></p>
								</div>
							<?php endif; ?>
							<div class="page-header">
								<h1><?php echo HTML::chars($title) ?></h1>
							</div>
							<?php echo $content; ?>
							<div class="progress progress-info progress-striped active">
								<div style="width:<?php echo $_activity; ?>%" class="bar"></div>
							</div>
						</div>
						<div class="span3 menu">
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
				<p class="muted"><?php echo __('Powered by :gleez', array(':gleez' => HTML::anchor('http://gleezcms.org/', 'Gleez CMS') )); ?>&nbsp;<?php echo Gleez::getVersion() ?></p>
				<p class="muted">&copy; 2011-<?php echo date('Y') ?> Gleez Technologies</p>
			</div>
		</div>
	</div>
</body>
</html>
