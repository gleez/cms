<?php defined("SYSPATH") or die("No direct script access.") ?>

<h4 class="title"><?php echo __('(:count) Comments', array(':count' => $count) ) ?></h4>

<ol class="MessageList Discussions" START=<?php echo $pagination->offset + 1; ?>>
<?php foreach($comments as $i => $comment) : ?>
        <li class="Comment Item <?php echo $comment->status; ?>" id="Comment_<?php echo $comment->id; ?>" >
                <div class="Comment">
                        <div class="Meta">
                                <span class="Author">
                                        <?php
                                                $nick = $comment->user->nick;
                                                $url  = $comment->user->url;
                                                $img = HTML::image('media/images/commentor.jpg', array('title' => $nick) );
                                                echo HTML::anchor($url, $img);
                                                echo HTML::anchor($url, $nick, array('title' => $nick));
                                                unset($nick, $img, $url);
                                        ?>
                                </span>
                                <span class="DateCreated">
                                        <?php echo Gleez::date($comment->created) ?>
                                </span>
                                <span class="Permalink">
                                        <?php //echo HTML::anchor($comment->url, _('Permalink'), array('class' => 'Permalink')) ?>
                                </span>
                                
                                <?php if ($comment->user_can('edit') ): ?>
                                        <span class="edit">
                                                <?php echo HTML::anchor($comment->edit_url, _('edit'), array('class' => 'Edit')) ?>
                                        </span>
                                <?php endif;?>

                                
                                <?php if ($comment->user_can('delete') ): ?>
                                        <span class="edit">
                                                <?php echo HTML::anchor($comment->delete_url, _('delete'), array('class' => 'Delete')) ?>
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