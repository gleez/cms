<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php 
    $elements    = array();
?>

<?php for($i = 0; $i < $items_count; $i++) : ?>
    <?php
        if($i == ($items_count - 1) && $last_linkable == false) {
            $elements[] = ucfirst(__($items[$i]['label']));
        }
        else {
            $elements[] = HTML::anchor($items[$i]['url'], ucfirst(__($items[$i]['label'])));
        }
   ?>
<?php endfor; ?>

<?php echo join($separator, $elements); ?>