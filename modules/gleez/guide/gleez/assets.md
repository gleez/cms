# Asset Manager

## About

Allows assets (CSS, Javascript, etc.) to be included throughout the application, and then outputted later based on dependencies.
This makes sure all assets will be included in the correct order, no matter what order they are defined in.

## Usage

Basic usage would be to include assets throughout your application, in controllers most likely. Perhaps you have a base controller that the
rest of your controllers extend where you include your base or default CSS and Javascript files. Then in other controllers you can add or remove
assets according to what is needed for the respective action.

**/application/controllers/base.php**

	abstract class Controller_Base extends Template {

		public function action_before()
		{
			// Setup default styles, javascript, and groups
			Assets::css('global', 'assets/css/global.css', array('grid', 'reset'), array('media' => 'screen'));
			Assets::css('reset', 'assets/css/reset.css');
			Assets::css('grid', 'assets/css/grid.css', 'reset');

			Assets::js('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js');
			Assets::js('global', 'assets/js/global.js', array('jquery'));
			Assets::js('stats', 'assets/js/stats.js', NULL, TRUE);
		}

	}

**/application/controllers/blog.php**

	class Controller_Blog extends Template {

		public function action_index()
		{
			// Add CSS for the blog index
			Assets::css('blog', 'assets/css/blog.css', array('global'));

			// We don't need 'global.js' on this page, so don't load it.
			Assets::remove_js('global');
		}

	}

Then you would output the assets in your template somewhere

**/application/views/template.php**

	<html>
		<head>
			<title>Gleez Assets</title>
			<?php echo Assets::group('head') ?>
			<?php echo Assets::css() ?>
			<?php echo Assets::js() ?>
		</head>
		<body>
			<!-- Content -->
			<?php echo Assets::js(TRUE) ?>
			<?php echo Assets::group('footer') ?>
		</body>
	</html>

You could even include the CSS and Javascript into a group if you want

	Assets::group('head', 'css', Assets::css());
	Assets::group('head', 'js', Assets::js());

### Dependencies

To provide maximum flexibility over assets you don't necessarily have direct control over, you can define assets that other assets
depend on to function correctly.
For instance, jQuery UI is dependant on jQuery to work. If jQuery is included somewhere else in your application, you wouldn't want to include
jQuery UI and have it show up before jQuery in the source. So by providing dependencies, we can make sure that never happens.

	Assets::js('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.10/jquery-ui.min.js', array('jquery'));

### Other Functions

#### Getting a single asset

	$global = Assets::js('global');
	// returns <script src="/assets/js/global.js"></script>

#### Removing assets

	Assets::remove_css('grid');

	// Remove all CSS
	Assets::remove_css();

	Assets::remove_group('head');