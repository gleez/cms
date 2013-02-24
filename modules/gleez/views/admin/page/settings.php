<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="help">
  <?php echo __('Page specific settings, default status, tags, comments etc.'); ?>
</div>

  <?php
    $use_captcha       = (isset($post['use_captcha']) AND $post['use_captcha'] == 1) ? TRUE : FALSE;
    $use_authors       = (isset($post['use_authors']) AND $post['use_authors'] == 1) ? TRUE : FALSE;
    $use_comment       = (isset($post['use_comment']) AND $post['use_comment'] == 1) ? TRUE : FALSE;
    $use_category      = (isset($post['use_category']) AND $post['use_category'] == 1) ? TRUE : FALSE;
    $use_excerpt       = (isset($post['use_excerpt']) AND $post['use_excerpt'] == 1) ? TRUE : FALSE;
    $use_tags          = (isset($post['use_tags'])    AND $post['use_tags'] == 1) ? TRUE : FALSE;
    $use_book          = (isset($post['use_book'])    AND $post['use_book'] == 1) ? TRUE : FALSE;
    $use_submitted     = (isset($post['use_submitted']) AND $post['use_submitted'] == 1) ? TRUE : FALSE;
    $comment_anonymous = (isset($post['comment_anonymous']) AND $post['comment_anonymous'] == 1) ? TRUE : FALSE;
  ?>

  <?php if (! empty($errors)): ?>
    <div id="formerrors" class="errorbox">
      <h3><?php echo __('Ooops!'); ?></h3>
      <ol>
        <?php foreach($errors as $field => $message): ?>
          <li>
            <?php echo $message ?>
          </li>
        <?php endforeach ?>
      </ol>
    </div>
  <?php endif ?>

<?php echo Form::open( Route::url('admin/page', array('action' =>'settings')), array('class'=>'page-settings-form form')) ?>

<div class="control-group <?php echo isset($errors['items_per_page']) ? 'error': ''; ?>">
  <?php echo Form::label('title', __('Page entries per page'), array('class' => 'wrap left')) ?>
  <?php echo Form::select('items_per_page', array(5 => 5, 10 => 10, 15 => 15, 20 => 20, 25 => 25, 30 => 30, 35 => 35, 50 =>50), $post['items_per_page'], array('class' => 'list tiny')); ?>
</div>

<div class="control-group <?php echo isset($errors['default_status']) ? 'error': ''; ?>">
    <?php echo Form::label('default_status', __('Default Page Status:'), array('class' => 'wrap left')) ?>
    <?php echo Form::select('default_status', Post::status(), isset($post['default_status']) ? $post['default_status'] : NULL, array('class' => 'list tiny')); ?>
</div>

<div class="control-group <?php echo isset($errors['use_captcha']) ? 'error': ''; ?>">
  <?php echo Form::hidden('use_captcha', 0 ) ?> <?php //@important the hidden filed should be before checkbox ?>
  <?php echo Form::label('use_captcha', Form::checkbox('use_captcha', TRUE, $use_captcha).__('Enable captcha'), array('class' => 'checkbox')) ?>
</div>

<div class="control-group <?php echo isset($errors['use_authors']) ? 'error': ''; ?>">
  <?php echo Form::hidden('use_authors', 0 ) ?> <?php //@important the hidden filed should be before checkbox ?>
  <?php echo Form::label('use_authors', Form::checkbox('use_authors', TRUE, $use_authors).__('Enable authors'), array('class' => 'checkbox')) ?>
</div>

<div class="control-group <?php echo isset($errors['use_comment']) ? 'error': ''; ?>">
    <?php echo Form::hidden('use_comment', 0 ) ?>  <?php //@important the hidden filed should be before checkbox ?>
  <?php echo Form::label('use_comment', Form::checkbox('use_comment', TRUE, $use_comment).__('Enable comments'), array('class' => 'checkbox')) ?>
</div>

<div class="control-group <?php echo isset($errors['use_excerpt']) ? 'error': ''; ?>">
  <?php echo Form::hidden('use_excerpt', 0 ) ?> <?php //@important the hidden filed should be before checkbox ?>
  <?php echo Form::label('use_excerpt', Form::checkbox('use_excerpt', TRUE, $use_excerpt).__('Enable excerpt'), array('class' => 'checkbox')) ?>
</div>

<div class="control-group <?php echo isset($errors['use_category']) ? 'error': ''; ?>">
  <?php echo Form::hidden('use_category', 0 ) ?> <?php //@important the hidden filed should be before checkbox ?>
  <?php echo Form::label('use_category', Form::checkbox('use_category', TRUE, $use_category).__('Enable Category'), array('class' => 'checkbox')) ?>
</div>

<div class="control-group <?php echo isset($errors['use_tags']) ? 'error': ''; ?>">
  <?php echo Form::hidden('use_tags', 0 ) ?> <?php //@important the hidden filed should be before checkbox ?>
  <?php echo Form::label('use_tags', Form::checkbox('use_tags', TRUE, $use_tags).__('Enable tag cloud'), array('class' => 'checkbox')) ?>
</div>

<div class="control-group <?php echo isset($errors['use_submitted']) ? 'error': ''; ?>">
  <?php echo Form::hidden('use_submitted', 0 ) ?> <?php //@important the hidden filed should be before checkbox ?>
  <?php echo Form::label('use_submitted', Form::checkbox('use_submitted', TRUE, $use_submitted).__('Show Submitted Info'), array('class' => 'checkbox')) ?>
</div>

<?php if( class_exists('book')) : ?>
  <div class="control-group <?php echo isset($errors['use_book']) ? 'error': ''; ?>">
    <?php echo Form::hidden('use_book', 0 ) ?> <?php //@important the hidden filed should be before checkbox ?>
    <?php echo Form::label('use_book', Form::checkbox('use_book', TRUE, $use_book).__('Enable book support'), array('class' => 'checkbox')) ?>
  </div>
<?php endif; ?>

<div class="control-group <?php echo isset($errors['comment']) ? 'error': ''; ?>">
  <?php
    $comment1 = (isset($post['comment']) && $post['comment'] == 0) ? TRUE : FALSE;
    $comment2 = (isset($post['comment']) && $post['comment'] == 1) ? TRUE : FALSE;
    $comment3 = (isset($post['comment']) && $post['comment'] == 2) ? TRUE : FALSE;
  ?>

  <?php echo Form::label('comment', 'Allow people to post Comment(s) (These settings may be overridden for individual posts.)', array('class' => 'wrap1')) ?>
  <?php echo Form::label('comment', Form::radio('comment', 0, $comment1).__('Disabled'), array('class' => 'radio')) ?>
  <?php echo Form::label('comment', Form::radio('comment', 1, $comment2).__('Read only'), array('class' => 'radio')) ?>
  <?php echo Form::label('comment', Form::radio('comment', 2, $comment3).__('Read/write'), array('class' => 'radio')) ?>
</div>

<div class="control-group <?php echo isset($errors['comment_default_mode']) ? 'error': ''; ?>">
  <?php
    $mode1 = (isset($post['comment_default_mode']) && $post['comment_default_mode'] == 1) ? TRUE : FALSE;
    $mode2 = (isset($post['comment_default_mode']) && $post['comment_default_mode'] == 2) ? TRUE : FALSE;
    $mode3 = (isset($post['comment_default_mode']) && $post['comment_default_mode'] == 3) ? TRUE : FALSE;
    $mode4 = (isset($post['comment_default_mode']) && $post['comment_default_mode'] == 4) ? TRUE : FALSE;
  ?>

  <?php echo Form::label('comment_default_mode', 'Comment display mode ', array('class' => 'control-label')) ?>

  <?php echo Form::label('comment_default_mode', Form::radio('comment_default_mode', 1, $mode1).__('Flat list - collapsed'), array('class' => 'radio')) ?>
  <?php echo Form::label('comment_default_mode', Form::radio('comment_default_mode', 2, $mode2).__('Flat list - expanded'), array('class' => 'radio')) ?>
  <?php echo Form::label('comment_default_mode', Form::radio('comment_default_mode', 3, $mode3).__('Threaded list - collapsed'), array('class' => 'radio')) ?>
  <?php echo Form::label('comment_default_mode', Form::radio('comment_default_mode', 4, $mode4).__('Threaded list - expanded'), array('class' => 'radio')) ?>
</div>

<div class="control-group <?php echo isset($errors['comment_anonymous']) ? 'error': ''; ?>">
  <?php echo Form::hidden('comment_anonymous', 0 ) ?> <?php //@important the hidden filed should be before checkbox ?>
  <?php echo Form::label('comment_anonymous', Form::checkbox('comment_anonymous', TRUE, $comment_anonymous).__('Allow anonymous commenting (with contact information)'), array('class' => 'checkbox')) ?>
</div>

<div class="control-group <?php echo isset($errors['comment_order']) ? 'error': ''; ?>">
  <?php echo Form::label('comment_order', __('Comment Order : '), array('class' => 'control-label')) ?>
  <?php echo Form::select('comment_order', array('asc'=> __('Older'), 'desc'=>__('Newer')), isset($post['comment_order']) ? $post['comment_order'] : 'asc', array('class' => 'select tiny')); ?><br />
  <?php echo Form::label('comment', __('Comments should be displayed with the older/new comments at the top of each page'), array('class' => 'control-label')) ?>
</div>

<div class="control-group <?php echo isset($errors['comments_per_page']) ? 'error': ''; ?>">
  <?php echo Form::label('comments_per_page', __('Comments per page : '), array('class' => 'control-label')) ?>
  <?php echo Form::select('comments_per_page', array(5 =>5, 10 => 10, 20 => 20, 30 => 30, 50 => 50, 70 => 70,  90 => 90, 150 => 150, 200 => 200, 250 => 250, 300 => 300), isset($post['comments_per_page']) ? $post['comments_per_page'] : 50, array('class' => 'select tiny'));
  ?>
</div>

<?php echo Form::submit('page_settings', __('Submit'), array('class' => 'btn btn-primary')) ?>
<?php echo Form::close() ?>
