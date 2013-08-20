    <div class="row">

        <?php if ($sidebar_left): echo '<div id="left" class="span3">'. $sidebar_left .'</div>'; endif; ?>

        <div id="row-content" class="span<?php echo $main_column; ?>">

            <div id="messages" class="messages span9 offset1"><?php echo $messages ?></div>

            <div id="pageContent" class="well">
                <?php if ($title): ?>
                    <header id="overview" class="jumbotron subhead">
                        <div class="page-header <?php echo ($tabs ? ' with-tabs' : ''); ?>">
                            <h1><?php echo $title;?></h1>
                            <?php if ($tabs): ?>
                                <div id="tabs-actions" class="row-fluid11">
                                    <?php if($tabs):?>
                                        <div id="tabs"> <?php echo $tabs; ?> </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif;?>
                        </div>
                    </header>
                <?php endif; ?>

                <div id="content"><?php echo $content; ?></div>
            </div>
        </div>

        <?php if ($sidebar_right): echo '<div id="right" class="span3">'. $sidebar_right .'</div>'; endif; ?>
    </div>