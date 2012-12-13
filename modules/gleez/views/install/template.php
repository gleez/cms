<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $l = substr(I18n::$lang, 0, 2) ?>" lang="<?php echo $l ?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo $title ?> | Gleez CMS</title>
	<?php foreach ($styles as $style => $media) echo HTML::style($style, array('media' => $media), TRUE), "\n" ?>
	<?php echo "<link rel=\"shortcut icon\" href=\"/$link\" type=\"image/x-icon\" />"  ?>
	<style>
		body {
			padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
		}
	</style>

	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>

<body class="<?php echo $l ?>">

	<div class="navbar navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<a class="brand" href="#"><?php echo __('Gleez Installer'); ?></a>
			</div>
		</div>
	</div>

	<div class="container">
		
		<h1><?php echo HTML::chars($title) ?></h1>

		<?php if ( ! empty($error)): ?>
			<div class="alert alert-error">
				<p><?php echo HTML::chars($error) ?></p>
			</div>
		<?php endif; ?>
		
		<div style="margin-bottom: 9px;" class="progress progress-warning progress-striped">
			<div style="width:<?php echo $_activity; ?>%" class="bar"></div>
		</div>
		
		<div class="row">
			<div class="span8"><?php echo $content ?></div>

			<div id="menu" class="span3 well last install-<?php echo $_activity; ?>">
				<ol>
					<?php foreach ($menu as $item): ?>
					<li><?php echo $item; ?> </li>
					<?php endforeach ?>
				</ol>
			</div>
		</div>
		

		<footer class="footer">
			<blockquote> Did something go wrong? Try the <a href="https://github.com/gleez/cms/issues">Issues</a> or ask in the <a href="https://github.com/gleez/cms/issues">Github Issues</a>. </blockquote>
		
			<p class="powered pull-right">Powered by <?php echo HTML::anchor('http://gleezcms.org/', 'Gleez') ?> v<?php echo GLEEZ::VERSION ?></p>
			<p class="copyright pull-left">&copy; <?php echo date('Y') ?> Gleez Technologies</p>
			
		</footer>
	</div>

</body>
</html>
