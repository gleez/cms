<html>
	<head>
		<title><?php echo __('Maintenance Mode'); ?></title>
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
		<h1 class="text_2"><span><?php echo __('Sorry, we\'re down for maintenance!'); ?></span></h1>

		<p class="reasons_small">
			<small><?php echo __('Fortunately only for a short while. No need to scream, things are under control. Just doing a bit of maintenance.'); ?></small><br/>
      <small><?php echo __('Code:'); ?> <?php echo $code ?></small><br/>
      <small><?php echo __('Message:'); ?> <?php echo HTML::chars($message); ?></small>
		</p>

	</body>
</html>
