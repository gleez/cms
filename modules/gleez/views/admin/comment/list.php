<?php defined('SYSPATH') or die('No direct script access.'); ?>

    <div class="help">
	<?php echo __('View, edit, and delete your site\'s comments.'); ?>
    </div>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

    <div class="content">
	<?php echo Form::open(Route::get('admin/comment')->uri(array('action' => 'process')).URL::query($destination),
                               array('id'=>'admin-comment-form', 'class'=>'no-form')); ?>

	<fieldset>
	    <legend><?php echo __('Bulk Actions'); ?></legend>
	    <div class="control-group edit-operation <?php echo isset($errors['operation']) ? 'error': ''; ?>">
		<?php echo Form::select('operation', $bulk_actions, '', array('class' => 'list small')); ?>
	    </div>
	    <?php echo Form::submit('comment-bulk-actions', __('Apply'), array('class'=>'submit')); ?>
	</fieldset>

	<table class="table table-striped table-bordered" id="comments-admin-list">

	    <thead>
		<tr>
		    <th>#</th>
		    <th> <?php echo __('Subject'); ?>&nbsp;<?php echo URL::sortAnchor('title'); ?> </th>
		    <th> <?php echo __('Author'); ?>&nbsp;<?php echo URL::sortAnchor('author'); ?>  </th>
                    <th> <?php echo __('Posted In');?>  </th>
		    <th> <?php echo __('Created'); ?>&nbsp;<?php echo URL::sortAnchor('created'); ?>  </th>
		    <th> <?php echo __('Actions'); ?></th>
		</tr>
	    </thead>
		<tbody>
	    <?php foreach ($posts as $post):  ?>
	    <tr id="comment-row-<?php echo $post->id ?>" class="<?php print text::alternate("odd", "even"); ?>">

		<td><?php echo Form::checkbox('comments['.$post->id.']', $post->id, isset($_POST['comments'][$post->id]) ); ?> </td>

		<td> <?php echo HTML::anchor($post->url, $post->title, array('class'=>'action-view',
                                                'title' => Text::limit_words( $post->rawbody, 128, ' ...'))) ?> </td>

		<td>
		    <?php if ($post->author == 1 AND ! is_null($post->guest_name)): ?>
			<?php echo HTML::anchor($post->guest_url, $post->guest_name, array()) . __(' (not verified)'); ?>
		    <?php else: ?>
			<?php echo HTML::anchor(Route::get('user')->uri(array('action' => 'profile',
			'id' => $post->author)), $post->user->nick, array()) ?>
		    <?php endif ?>

		</td>

                <td> <?php echo HTML::anchor($post->post->url, $post->post->title, array('class'=>'action-view')) ?> </td>
		<td> <?php echo Date::date($post->created); ?> </td>
		<td class="action">
		    <?php echo HTML::anchor($post->edit_url.URL::query($destination), '<i class="icon-edit"></i>', array('class'=>'action-edit', 'title'=> __('Edit'))) ?>
		    <?php echo HTML::anchor($post->delete_url.URL::query($destination), '<i class="icon-trash"></i>', array('class'=>'action-delete', 'title'=> __('Delete'))) ?>
		</td>

	    </tr>
	    <?php endforeach ?>
		</tbody>
      </table>

	<?php echo Form::close(); ?>
    </div>

    <?php echo $pagination ?>
