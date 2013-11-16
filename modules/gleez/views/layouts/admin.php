<!DOCTYPE html>
<html id="backend" lang="<?php echo $lang; ?>">
<head>
	<title><?php echo $head_title ?></title>
	<?php echo Meta::tags(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<?php echo Meta::links(); ?>
	<!-- HTML5 shim and Respond.js, for IE6-8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="/media/js/html5.js"></script>
		<script src="/media/js/respond.min.js"></script>
	<![endif]-->
	<?php echo Assets::css(); ?>
</head>
<body id="<?php echo $page_id; ?>" class="<?php echo $page_class; ?>">


	<!-- ########## Navbar start ########## -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      	<div class="admin-nav container">
	        <div class="navbar-header">
	          	<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
		            <span class="sr-only">Toggle navigation</span>
		            <span class="icon-bar"></span>
		            <span class="icon-bar"></span>
		            <span class="icon-bar"></span>
	          	</button>
	          	<?php echo HTML::anchor('/', $site_name, array('class' => 'navbar-brand', 'title' => $site_name)) ?>
	        </div>
        	<div class="navbar-collapse collapse">

          			
          		<ul class="nav navbar-nav navbar-right">
						<?php if (User::is_guest()): ?>
							<?php if (Kohana::$config->load('auth')->get('register')): ?>
								<li><a href="<?php echo URL::site('/user/register'); ?>"><?php echo __('Sign Up')?></a></li>
							<?php endif; ?>
							<li><a href="<?php echo URL::site('/user/login'); ?>"><i class="icon-white icon-chevron-left"></i><?php echo __('Sign In') ?></a></li>
						<?php else:  ?>
						<li class="dropdown">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="icon-user"></i><?php echo $_user->nick; ?><b class="caret"></b>
							</a>

							<ul class="dropdown-menu">
								<?php if (User::is_admin()): ?>
									<li><a href="<?php echo URL::site('/admin') ?>"><i class="icon-dashboard"></i> <?php echo __('Dashboard') ?></a></li>
									<li class="divider"></li>
								<?php endif; ?>
								<li><a href="<?php echo URL::site('/user/profile') ?>"><i class="icon-cog"></i> <?php echo __('Profile') ?></a></li>
								<li><a href="<?php echo URL::site("/user/edit") ?>"><i class="icon-pencil"></i> <?php echo __('Account') ?></a></li>
								<li class="divider"></li>
								<li><a href="<?php echo URL::site('/user/logout'); ?>"><i class="icon-off"></i> <?php echo __('Sign Out') ?></a></li>
							</ul>
						</li>
						<?php endif; ?>
          		</ul>
        	</div><!--/.nav-collapse -->
      	</div>
    </div>
	<!-- ########## Navbar end ########## -->

	<!-- ########## admin / container start ########## -->
	<div class="admin-container container" itemscope itemtype="http://schema.org/WebPage">
		<?php include Kohana::find_file('views', 'admin.tpl'); ?>
	</div>
	<!-- ########## template / container end ########## -->

	<?php echo Assets::js(FALSE); ?>
	<?php echo Assets::codes(FALSE); ?>
	<?php echo $profiler; ?>
</body>
</html>