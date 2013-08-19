<?php
	$elements = array();

	for($i = 0; $i < $items_count; $i++)
	{
        if($i == ($items_count - 1) && $last_linkable == false) {
            $elements[] = ucfirst(__($items[$i]['label']));
        }
        else {
            $elements[] = HTML::anchor($items[$i]['url'], ucfirst(__($items[$i]['label'])));
        }
	}

	echo join($separator, $elements);