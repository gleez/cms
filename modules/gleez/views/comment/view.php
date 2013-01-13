<?php defined("SYSPATH") or die("No direct script access.") ?>

        <div class="comment <?php echo $comment->status; ?>" id="comment-<?php echo $comment->id; ?>" >
                <div class="author"><?php echo __(':user says:', array(':user' => $comment->user->nick)) ?></div>
                <div class="submitted"><?php echo Date::date($comment->created) ?></div>

                <div class="clearfix">&nbsp;</div>
                <div class="content">
                        <?php echo $comment->body ?>
                </div>
        </div>
