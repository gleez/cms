<?php defined("SYSPATH") or die("No direct script access.") ?>

<div id="post-<?php echo $post->id; ?>" class="post<?php if ($post->sticky) { echo ' sticky'; } ?><?php echo ' post-'. $post->status;  ?>">

    <?php if ( !$page_title ): ?>
	<h2 id="post-title"><?php echo $post->title ?></h2>
    <?php endif; ?>

    <span class="submitted"><?php echo Date::date_time($post->created); ?></span>

    <div class="post-content">
	<?php echo $teaser ? $post->teaser : $post->body ?>
    </div>

</div>
