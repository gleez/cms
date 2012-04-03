<?php defined('SYSPATH') or die('No direct script access.'); ?>

    <div class="help">
	<?php echo __('View, edit, and delete your site\'s comments.'); ?>
    </div>
    
    	<?php if ( ! empty($errors)): ?>
		<div id="formerrors" class="errorbox">
			<h3>Ooops!</h3>
			<ol>
				<?php foreach($errors as $field => $message): ?>
					<li>	
						<?php echo $message ?>
					</li>
				<?php endforeach ?>
			</ol>
		</div>
	<?php endif ?>
    
    <div class="content">
	<?php echo Form::open(Route::get('admin/comment')->uri(array('action' => 'process')).URL::query($destination),
                               array('id'=>'admin-comment-form', 'class'=>'no-form')); ?>

	<fieldset>
	    <legend><?php echo __('Bulk Actions'); ?></legend>
	    <div class="control-group edit-operation <?php echo isset($errors['operation']) ? 'error': ''; ?>">
		<?php echo Form::select('operation', $bulk_actions, '', array('class' => 'list small')); ?>
	    </div>
	    <?php echo Form::submit('comment-bulk-actions', 'Apply', array('class'=>'submit')); ?>
	</fieldset>
    
	<table class="table table-striped table-bordered" id="comments-admin-list">
	
	    <thead>
		<tr>
		    <th> </th>
		    <th> <?php echo __('Subject'); ?>&nbsp;<?php echo URL::sortAnchor('title'); ?> </th>
		    <th> <?php echo __('Author'); ?>&nbsp;<?php echo URL::sortAnchor('author'); ?>  </th>
                    <th> <?php echo __('Posted In');?>  </th>
		    <th> <?php echo __('Created'); ?>&nbsp;<?php echo URL::sortAnchor('created'); ?>  </th>
		    <th> <?php echo __('Actions'); ?></th>
		</tr>
	    </thead>
	
	    <?php foreach ($posts as $post):  ?>
	    <tr id="comment-row-<?php echo $post->id ?>" class="<?php print text::alternate("odd", "even"); ?>">
	    
		<td><?php echo Form::checkbox('comments['.$post->id.']', $post->id, isset($_POST['comments'][$post->id]) ); ?> </td>

		<td> <?php echo HTML::anchor($post->url, $post->title, array('class'=>'action-view',
                                                'title' => Text::limit_words( $post->rawbody, 128, ' ...'))) ?> </td>
                
		<td>
		    <?php if ($post->author == 1 AND $post->guest_name != null):  ?>
			<?php echo HTML::anchor($post->guest_url, $post->guest_name, array()) . __(' (not verified)'); ?>
		    <?php else: ?>
			<?php echo HTML::anchor(Route::get('user')->uri(array('action' => 'profile',
			'id' => $post->author)), $post->user->nick, array()) ?>
		    <?php endif ?>
		    
		</td>
		
                <td> <?php echo HTML::anchor($post->post->url, $post->post->title, array('class'=>'action-view')) ?> </td>
		<td> <?php echo Gleez::date($post->created); ?> </td>
		<td class="action">
		    <?php echo HTML::anchor($post->edit_url.URL::query($destination), __('Edit'), array('class'=>'action-edit', 'title'=> __('Edit'))) ?>
		    <?php echo HTML::anchor($post->delete_url.URL::query($destination), __('Delete'), array('class'=>'action-delete', 'title'=> __('Delete'))) ?>
		</td>
	    
	    </tr>
	    <?php endforeach ?>
	
      </table>

	<?php echo Form::close(); ?>
    </div>
  
    <?php echo $pagination ?>