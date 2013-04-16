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
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body class="<?php echo substr(I18n::$lang, 0, 2) ?>">
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
                <!--[if lt IE 7]>
                    <div class="alert">
                        <strong><?php echo __('Warning!'); ?></strong>
                        <?php
                        echo __('You are using an :out browser. Please :url or :frame to improve your experience', array(
                                ':out' => '<strong>'.__('outdated').'</strong>',
                                ':url' => HTML::anchor('http://browsehappy.com/', __('upgrade your browser')),
                                ':frame' => HTML::anchor('http://www.google.com/chromeframe/?redirect=true&hl='.substr(I18n::$lang, 0, 2), __('activate Google Chrome Frame')),
                            )
                        )
                        ?>
                    </div>
                <![endif]-->
                <div class="row">
                    <div class="span9">
                        <?php if ( ! empty($error)): ?>
                            <div class="alert alert-error">
                                <p><?php echo HTML::chars($error) ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="hero-unit">
                            <h1><?php echo HTML::chars($title) ?></h1>
                            <?php echo $content; ?>
                        </div>
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
    <footer class="footer">
        <div class="container">
            <div class="footer-credits">
                <p><?php echo __('Powered by :gleez', array(':gleez' => HTML::anchor('http://gleezcms.org/', 'Gleez CMS') )); ?> v<?php echo Gleez::VERSION ?></p>
                <p>&copy; 2011-<?php echo date('Y') ?> Gleez Technologies</p>
            </div>
        </div>
    </footer>

</body>
</html>
