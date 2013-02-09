<?php
/*
	First Previous 1 2 3 ... 22 23 24 25 26 [27] 28 29 30 31 32 ... 48 49 50 Next Last
*/

// Number of page links in the begin and end of whole range
$count_out = ( ! empty($config['count_out'])) ? (int) $config['count_out'] : 1;
// Number of page links on each side of current page
$count_in = ( ! empty($config['count_in'])) ? (int) $config['count_in'] : 5;

// Beginning group of pages: $n1...$n2
$n1 = 1;
$n2 = min($count_out, $total_pages);

// Ending group of pages: $n7...$n8
$n7 = max(1, $total_pages - $count_out + 1);
$n8 = $total_pages;

// Middle group of pages: $n4...$n5
$n4 = max($n2 + 1, $current_page - $count_in);
$n5 = min($n7 - 1, $current_page + $count_in);
$use_middle = ($n5 >= $n4);

// Point $n3 between $n2 and $n4
$n3 = (int) (($n2 + $n4) / 2);
$use_n3 = ($use_middle AND (($n4 - $n2) > 1));

// Point $n6 between $n5 and $n7
$n6 = (int) (($n5 + $n7) / 2);
$use_n6 = ($use_middle AND (($n7 - $n5) > 1));

// Links to display as array(page => content)
$links = array();

// Generate links data in accordance with calculated numbers
for ($i = $n1; $i <= $n2; $i++)
{
	$links[$i] = $i;
}
if ($use_n3)
{
	$links[$n3] = '&hellip;';
}
for ($i = $n4; $i <= $n5; $i++)
{
	$links[$i] = $i;
}
if ($use_n6)
{
	$links[$n6] = '&hellip;';
}
for ($i = $n7; $i <= $n8; $i++)
{
	$links[$i] = $i;
}

?>

<div class="pagination pagination-centered">
	<ul>
	<?php if ($previous_page !== FALSE): ?>
		<li><a href="<?php echo HTML::chars($page->url($previous_page)) ?>" class="prev" rel="prev">&larr;</a></li>
	<?php else: ?>
		<li class="disabled"><a href="" class="disabled">&larr;</a></li>
	<?php endif ?>

	<?php foreach ($links as $number => $content): ?>
		<?php if ($number === $current_page): ?>
			<li class="active"><a href="<?php echo HTML::chars($page->url($number)) ?>" class="active"><?php echo $content ?></a></li>
		<?php else: ?>
			<li><a href="<?php echo HTML::chars($page->url($number)) ?>"><?php echo $content ?></a></li>
		<?php endif ?>
	<?php endforeach ?>

	<?php if ($next_page !== FALSE): ?>
		<li><a href="<?php echo HTML::chars($page->url($next_page)) ?>" class="next" rel="next">&rarr;</a></li>
	<?php else: ?>
		<li class="disabled"><a href="" class="disabled">&rarr;</a></li>
	<?php endif ?>
	</ul>
</div><!-- .pagination -->