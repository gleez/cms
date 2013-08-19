<h4 class="title"><?php echo __('Comments'); ?></h4>

<ol class="MessageList Discussions" START=<?php echo $pagination->offset + 1; ?>>
	<?php foreach($comments as $i => $comment) : ?>
		<li class="Comment Item <?php echo $comment->status; ?>" id="Comment_<?php echo $comment->id; ?>" >
			<div class="Comment">
				<div class="meta">
					<span class="author">
						<?php echo HTML::anchor($comment->user->url, User::getAvatar($comment->user)); ?>
						<?php echo HTML::anchor($comment->user->url, $comment->user->nick, array('title' => $comment->user->nick)); ?>
					</span>
					<span class="date-created">
						<?php echo Date::date_format($comment->created) ?>
					</span>

					<?php if ($comment->user_can('edit') ): ?>
						<span class="edit">
							<?php echo HTML::icon($comment->edit_url, 'icon-edit', array('class'=>'action-edit', 'title'=> __('Edit Comment'))); ?>
						</span>
					<?php endif;?>

					<?php if ($comment->user_can('delete') ): ?>
						<span class="delete">
							<?php echo HTML::icon($comment->delete_url, 'icon-trash', array('class'=>'action-delete', 'title'=> __('Delete Comment'))); ?>
						</span>
					<?php endif;?>
				</div>
				<div class="Message">
					<?php echo $comment->body ?>
				</div>
			</div>
		</li>

	<?php endforeach; ?>
</ol>

<?php echo $pagination; ?>
