<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $l = substr(I18n::$lang, 0, 2) ?>" lang="<?php echo $l ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<title><?php echo $title ?> | Kohana <?php echo __('User Guide'); ?></title>

<?php foreach ($styles as $style => $media) echo HTML::style($style, array('media' => $media), NULL, TRUE), "\n" ?>

<?php foreach ($scripts as $script) echo HTML::script($script, NULL, NULL, TRUE), "\n" ?>

<!--[if lt IE 9]>
<script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
<![endif]-->
</head>
<body>

	<div id="header">
		<div class="container">
			<a href="http://kohanaframework.org/" id="logo">
				<img src="<?php echo Route::url('docs/media', array('file' => 'img/kohana.png')) ?>" />
			</a>
			<div id="menu">
				<ul>
					<li class="guide first">
						<a href="<?php echo Route::url('docs/guide') ?>"><?php echo __('User Guide') ?></a>
					</li>
					<li class="api">
						<a href="<?php echo Route::url('docs/api') ?>"><?php echo __('API Browser') ?></a>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<div id="content">
		<div class="wrapper">
			<div class="container">
				<div class="span-22 prefix-1 suffix-1">
					<ul id="breadcrumb">
						<?php foreach ($breadcrumb as $link => $title): ?>
							<?php if (is_string($link)): ?>
							<li><?php echo HTML::anchor($link, $title, NULL, NULL, TRUE) ?></li>
							<?php else: ?>
							<li class="last"><?php echo $title ?></li>
							<?php endif ?>
						<?php endforeach ?>
					</ul>
				</div>
				<div class="span-6 prefix-1">
					<div id="topics">
						<?php echo $menu ?>
					</div>
				</div>
				<div id="body" class="span-16 suffix-1 last">
					<?php echo $content ?>

					<?php if (Kohana::$environment === Kohana::PRODUCTION AND empty($hide_disqus)): ?>
					<div id="disqus_thread" class="clear"></div>
					<script type="text/javascript">
						var disqus_identifier = '<?php echo HTML::chars(Request::current()->uri()) ?>';
						(function() {
							var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
							dsq.src = 'http://kohana.disqus.com/embed.js';
							(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
							})();
					</script>
					<noscript><?php echo __('Please enable JavaScript to view the :anchor_open comments powered by Disqus.:anchor_close', array(':anchor_open' => '<a href="http://disqus.com/?ref_noscript=kohana">', ':anchor_close' => '</a>')); ?></noscript>
					<a href="http://disqus.com" class="dsq-brlink">Documentation comments powered by <span class="logo-disqus">Disqus</span></a>
					<?php endif ?>
				</div>
			</div>
		</div>
	</div>

	<div id="footer">
		<div class="container">
			<div class="span-12">
			<?php if (isset($copyright)): ?>
				<p><?php echo $copyright ?></p>
			<?php else: ?>
				&nbsp;
			<?php endif ?>
			</div>
			<div class="span-12 last right">
			<p>Powered by <?php echo HTML::anchor('http://kohanaframework.org/', 'Kohana') ?> v<?php echo Kohana::VERSION ?></p>
			</div>
		</div>
	</div>

<?php if (Kohana::$environment === Kohana::PRODUCTION): ?>
<script type="text/javascript">
//<![CDATA[
(function() {
	var links = document.getElementsByTagName('a');
	var query = '?';
	for(var i = 0; i < links.length; i++) {
	if(links[i].href.indexOf('#disqus_thread') >= 0) {
		query += 'url' + i + '=' + encodeURIComponent(links[i].href) + '&';
	}
	}
	document.write('<script charset="utf-8" type="text/javascript" src="http://disqus.com/forums/kohana/get_num_replies.js' + query + '"></' + 'script>');
})();
//]]>
</script>
<?php endif ?>
</body>
</html>
