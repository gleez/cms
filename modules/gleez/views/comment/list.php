<?php defined("SYSPATH") OR die("No direct script access.") ?>

<h4 class="title"><?php echo __('Comments'); ?></h4>

<ol class="MessageList Discussions" START=<?php echo $pagination->offset + 1; ?>>
	<?php foreach($comments as $i => $comment) : ?>
		<li class="Comment Item <?php echo $comment->status; ?>" id="Comment_<?php echo $comment->id; ?>" >
			<div class="Comment">
				<div class="Meta">
					<span class="Author">
						<?php
							// @todo Move to controller
							$nick = $comment->user->nick;
							$url  = $comment->user->url;
							$img  = ( ! empty($comment->user->picture))
								? HTML::resize($comment->user->picture, array('alt' => $comment->user->nick, 'height' => 24, 'width' => 24, 'type' => 'resize', 'class' => 'commentor_avatar'))
								: HTML::image('media/images/avatar-user-400.png', array('title' => $nick, 'height' => 24, 'width' => 24, 'class' => 'commentor_avatar'));

							echo HTML::anchor($url, $img);
							echo HTML::anchor($url, $nick, array('title' => $nick));
							unset($nick, $img, $url);
						?>
					</span>
					<span class="DateCreated">
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
