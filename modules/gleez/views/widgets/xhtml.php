<?php defined('SYSPATH') or die('404 Not Found.');?>

<div id="widget-<?php echo $widget->module; ?>-<?php echo $widget->name; ?> <?php echo (isset($id)) ? 'widget-'.$id : '' ?>" class="widget well widget-<?php echo $widget->name; ?> <?php echo (isset($zebra)) ? 'widget-'.$zebra : '' ?>">
    
        <?php if ($widget->show_title): ?>
                <h2 class="title nav-header"><?php echo HTML::chars($title) ?></h2>
        <?php endif ?>
    
        <div class="content"> <?php echo $content ?> </div>
        
</div>