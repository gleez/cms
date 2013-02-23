<html>
	<head>
		<title><?php echo __('404 - Page not found'); ?></title>
		<style>
			* {
				margin:  0;
				padding: 0;
			}
			.text_2 {
				font-size: 60px;
				letter-spacing: 1px;
				line-height: 1.3em;
				text-align: center;
				color: #4E8622;
			}
			.reasons_big {
				margin-top: 15px;
				font-size: 30px;
			}
			.reasons_small {
				font-size: 24px;
			}
			.reasons_big, .reasons_small {
				line-height: 1.2em;
				text-align: center;
			}
			.reasons_small span {
				font-size: 20px;
			}
			.solutions_text {
				margin-top: 30px;
				font-size: 16px;
				text-align: center;
			}
			.error_type {
				color: #4E8622;
			}
		</style>
	</head>
	<body>
		<h1 class="text_2"><span><?php echo __('Dang...  Page not found!'); ?></span></h1>
		<h1 class="text_2"><span><?php echo __('Whops, I think we are lost...!'); ?></span></h1>
		<p class="reasons_big"><?php echo __('There\'s a lot of reasons why this page is <span class="error_type">404</span>.'); ?></p>
		
		<p class="reasons_small">
			<?php echo __('The requested URL <span>(:url)</span> could not be found!', array(':url' => Text::plain($url))) ?>
		</p>
		
		<p class="solutions_text">
			<?php echo __("Maybe the page exists, but is only visible to authorized users.
				      <br>If you think this is an error, talk to the Site administrator!"); ?>
		</p>
	</body>
</html>
