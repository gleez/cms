<?php defined("SYSPATH") or die("No direct script access."); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang; ?>" lang="<?php echo $lang; ?>">
    <head profile="http://gmpg.org/xfn/11">
	<title><?php echo $head_title ?></title>
	<?php echo Meta::tags(); ?>
	<?php echo Meta::links(); ?>
        <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
        <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
	<?php echo Assets::css(); ?>
	<!--[if lt IE 8]>
	    <link type="text/css" href="/media/css/font-awesome-ie7.css" rel="stylesheet" media="all" />
	<![endif]-->
    </head>
    
    <body id="<?php echo $page_id; ?>" class="<?php echo $page_class; ?>">
	
	<div class="navbar navbar-fixed-top">
	    <div class="navbar-inner">
		<div class="container">
		    <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		    </a>
		
		    <a href="<?php echo $site_url ?>" title="<?php echo $site_name ?>" class="brand">
		    <img class="logo" title="Gleez CMS Light, Simple, Flexible Content Management System" alt="Gleez CMS Light, Simple, Flexible Content Management System" src="<?php echo URL::site($template->site_logo)?>"></a>
		
		    <div class="nav-collapse">
			<?php echo $primary_menu; ?>

			<ul class="nav pull-right">
			    <?php if (Auth::instance()->logged_in()): ?>
				<li class="dropdown">
					<a data-toggle="dropdown" class="dropdown-toggle" href="#">
						<i class="icon-user"></i> 
						<?php echo $_user->nick; ?>
						<b class="caret"></b>
					</a>
					
					<ul class="dropdown-menu">
						<li><a href="<?php echo URL::site('/user/profile') ?>"><i class="icon-cog"></i> Profile</a></li>
						<li><a href="<?php echo URL::site("/user/edit") ?>"><i class="icon-pencil"></i> Account</a></li>
						<li class="divider"></li>
						<li><a rel="tooltip" href="<?php echo URL::site('/user/logout'); ?>" data-original-title="Logout"><i class="icon-off"></i> Logout</a></li>
					</ul>
				</li>
			    <?php else:?>
				<li><a href="<?php echo URL::site('/user/register'); ?>">Create an Account</a></a></li>
				<li><a href="<?php echo URL::site('/user/login'); ?>"><i class="icon-white icon-chevron-left"></i>Log In</a></li>
			    <?php endif;?>
			</ul>
			
		    </div>
		</div>
	    </div>
	</div>
	
	<div class="container">
	    <?php if ($is_admin): ?>
		<?php include Kohana::find_file('views', 'admin.tpl'); ?>
	    <?php else:?>
		<?php include Kohana::find_file('views', 'default.tpl'); ?>
	    <?php endif;?>
	    
	    <footer class="footer well">
		<div class="text-centered"><em><?php echo __('We hate reinventing the wheel. But when the wheel doesn\'t exist, or is square, we\'re not afraid to invent a round one.'); ?></em><br>
		<small><?php echo __('Rendered in {execution_time}, using {memory_usage} of memory.')?></small></div>
	    </footer>
	    
	    <div id="credits">
		    <div id="copyright" class="pull-left">
			<?php echo __('Copyright &copy; :year :site', array(':year' => date('Y'), ':site' => HTML::anchor(URL::site(false, true), $site_name)));?>
		    </div>
		    <div id="powerdby" class="pull-right">
			<?php echo __(':powerdby v{gleez_version}', array(':powerdby' => HTML::anchor('http://gleezcms.org/', 'Gleez CMS')))?>
		    </div>
	    </div>
	</div>
	
	<?php echo Assets::js(FALSE); ?>
	<?php echo Assets::codes(FALSE); ?>
	<?php echo $profiler; ?>
    </body>
</html>
