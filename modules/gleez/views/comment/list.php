<?php Assets::css('comment', "media/css/comment.css", array('default')); ?>

<h4 class="title"><?php echo __('Comments'); ?></h4>

<ol class="comments" START=<?php echo $pagination->offset + 1; ?>>
	<?php foreach($comments as $i => $comment) : ?>
		<li class="comment <?php echo $comment->status; ?>" id="comment-<?php echo $comment->id; ?>" >
			<div class="row">
				<div class="col-sm-1 col-md-1">
					<span class="author thumbnail">
						<?php echo HTML::anchor($comment->user->url, User::getAvatar($comment->user, array('size' => 50))); ?>
					</span>
				</div>

				<div class="col-sm-11 col-md-11">
					<div class="data">
						<?php echo HTML::anchor($comment->user->url, $comment->user->nick, array('title' => $comment->user->nick)); ?>
						<span class="date-created">
							<?php echo Date::date_format($comment->created, 'd M Y'); ?>
						</span>

						<?php if ($comment->user_can('edit') ): ?>
							<span class="edit">
								<?php echo HTML::icon($comment->edit_url, 'fa-edit', array('class'=>'action-edit', 'title'=> __('Edit Comment'))); ?>
							</span>
						<?php endif;?>

						<?php if ($comment->user_can('delete') ): ?>
							<span class="delete">
								<?php echo HTML::icon($comment->delete_url, 'fa-trash-o', array('class'=>'action-delete', 'title'=> __('Delete Comment'))); ?>
							</span>
						<?php endif;?>
					</div>
					<div class="comment-content">
						<?php echo Text::markup($comment->body); ?>
					</div>
				</div>

			</div>
		</li>

	<?php endforeach; ?>
</ol>

<?php echo $pagination; ?>
