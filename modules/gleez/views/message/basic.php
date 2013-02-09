<?php if (isset($messages) AND !empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
                <div class="alert alert-<?php echo $message->type ?>">
                        <a class="close" data-dismiss="alert">×</a>
                        <?php echo $message->text ?>
                </div>
        <?php endforeach; ?>
<?php endif;?>