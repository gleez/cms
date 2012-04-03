<?php defined("SYSPATH") or die("No direct script access.") ?>
<?php echo $doctype; ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang; ?>" lang="<?php echo $lang; ?>"/>
    <head profile="http://gmpg.org/xfn/11">
	<title><?php echo $head_title ?></title>
	<?php echo Meta::tags(); ?>
	<?php echo Meta::links(); ?>
        <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
        <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
	<?php echo Assets::css(); ?>
    </head>
    
    <body id="<?php echo $page_id; ?>" class="<?php echo $page_class; ?>">
	
	<div class="navbar navbar-fixed-top" data-scrollspy="scrollspy">
	    <div class="navbar-inner">
		<div class="container">
		    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		    </a>
		
		    <a href="/" title="<?php echo $site_name ?>" class="brand">
		    <img class="logo" title="Gleez CMS Light, Simple, Flexible Content Management System" alt="Gleez CMS Light, Simple, Flexible Content Management System" src="/media/logo.png"></a>
		
		    <div class="nav-collapse">
			<?php echo $primary_menu; ?>
			<ul class="menus nav level-1">
			<?php
			    if (Auth::instance()->logged_in())
			    {
				echo '<li id="user-profile" class="first">'.Html::anchor('user/profile', 'My profile').'</li>';
				echo '<li id="user-logout" class="last">'.Html::anchor('user/logout', 'Log out').'</li>';
			    }
			    else
			    {
				echo '<li id="user-register" class="first">'.Html::anchor('user/register', 'Register').'</li>';
				echo '<li id="user-login" class="last">'.Html::anchor('user/login', 'Log in').'</li>';
			    }
			?>
			</ul>
		    </div>
		</div>
	    </div>
	</div>
	
	<div class="container">
	    <?php include Kohana::find_file('views', 'default.tpl'); ?>
	    
	    <footer class="footer">
		<em><?php echo __('We hate reinventing the wheel. But when the wheel doesn\'t exist, or is square, we\'re not afraid to invent a round one.'); ?></em>
		<small><?php echo __('Rendered in {execution_time}, using {memory_usage} of memory.')?></small>
		<div id="credits">
		    <div id="copyright" class="pull-left">
			<?php echo __('Copyright &copy; :year :site, All rights reserved.', array(':year' => date('Y'), ':site' => HTML::anchor(URL::site(false, true), $site_name)));?>
		    </div>
		    <div id="powerdby" class="pull-right">
			<?php echo __(':powerdby v{gleez_version}', array(':powerdby' => HTML::anchor('http://gleezcms.org/', 'Gleez CMS')))?>
		    </div>
		</div>
	    </footer>
	    
	</div>
	
	<?php echo Assets::js(FALSE); ?>
	<?php echo Assets::codes(FALSE); ?>
	<?php echo $profiler; ?>
    </body>
</html>