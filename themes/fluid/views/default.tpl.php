<?php defined("SYSPATH") or die("No direct script access.") ?>
    
    <div class="row">
        
        <?php if ($sidebar_left): echo '<div id="left" class="span3">'. $sidebar_left .'</div>'; endif; ?>
        
        <div class="span<?php echo $main_column; ?>">
            
            <?php if ($messages): echo '<div id="messages">'. $messages .'</div>'; endif; ?>
            
            <?php if ($title): ?>
                <header id="overview" class="jumbotron subhead">
                    <div class="page-header <?php echo ($tabs ? ' with-tabs' : ''); ?>">
                        <h1><?php echo $title;?></h1>
                        <?php if ($tabs): echo '<div id="tabs" >' . $tabs .'</div>'; endif; ?>
                    </div>
                </header>
            <?php endif; ?>
            
            <div id="content"><?php echo $content; ?></div>
        </div>
        
        <?php if ($sidebar_right): echo '<div id="right" class="span3">'. $sidebar_right .'</div>'; endif; ?>
    </div>