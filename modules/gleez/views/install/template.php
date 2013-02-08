<!DOCTYPE html>
<html lang="<?php echo substr(I18n::$lang, 0, 2) ?>">
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

<body class="<?php echo substr(I18n::$lang, 0, 2) ?>">

<div class="navbar navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand" href="#"><?php echo __('Gleez Installer'); ?></a>
    </div>
  </div>
</div>

<div class="container">
  <div class="row">
    <div class="span12">
      <h1><?php echo HTML::chars($title) ?></h1>

      <?php if ( ! empty($error)): ?>
      <div class="alert alert-error">
        <p><?php echo HTML::chars($error) ?></p>
      </div>
      <?php endif; ?>

      <div style="margin-bottom: 2em;" class="progress progress-warning progress-striped">
        <div style="width:<?php echo $_activity; ?>%" class="bar"></div>
      </div>

      <div class="row">
        <div class="span8">
          <?php echo $content; ?>
        </div>
        <div id="menu" class="span3 well">
          <ol>
            <?php foreach ($menu as $item): ?>
            <li><?php echo $item; ?> </li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="span12">
      <footer class="footer">
        <blockquote>
          <?php
          echo __('Did something go wrong? Try the ').HTML::anchor('https://github.com/gleez/cms/issues', 'Github Issues');
          ?>
        </blockquote>

        <p class="powered pull-right"><?php echo __('Powered by ').HTML::anchor('http://gleezcms.org/', 'Gleez') ?> v<?php echo Gleez::VERSION ?></p>
        <p class="copyright pull-left">&copy; <?php echo date('Y') ?> Gleez Technologies</p>

      </footer>
    </div>
  </div>
</div>

</body>
</html>
