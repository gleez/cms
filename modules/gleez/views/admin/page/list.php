<?php defined('SYSPATH') or die('No direct script access.'); ?>

    <div class="help">
	<?php echo __('View, edit, and delete your site\'s pages.'); ?>
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
	<?php echo Form::open(Route::get('admin/page')->uri(array('action' => 'list')).URL::query($destination),
                               array('id'=>'admin-page-form', 'class'=>'no-form')); ?>

	<fieldset>
	    <legend><?php echo __('Bulk Actions'); ?></legend>
	    <div class="control-group edit-operation <?php echo isset($errors['operation']) ? 'error': ''; ?>">
		<?php echo Form::select('operation', Post::bulk_actions(TRUE, 'page'), '', array('class' => 'input-xlarge')); ?>
		<?php echo Form::submit('page-bulk-actions', 'Apply', array('class'=>'btn btn-danger btn-small')); ?>
	    </div>
	</fieldset>
    
	<table class="table table-striped table-bordered" id="posts-admin-list">
	
	    <thead>
		<tr>
		    <th> # </th>
		    <th> <?php echo __('Title'); ?>&nbsp;<?php echo URL::sortAnchor('title'); ?> </th>
		    <th> <?php echo __('Author'); ?>&nbsp;<?php echo URL::sortAnchor('author'); ?>  </th>
		    <th> <?php echo __('Status'); ?>&nbsp;<?php echo URL::sortAnchor('status'); ?>  </th>
		    <th> <?php echo __('Updated'); ?>&nbsp;<?php echo URL::sortAnchor('created'); ?>  </th>
		    <th> <?php echo __('Actions'); ?></th>
		</tr>
	    </thead>
	
	    <?php foreach ($posts as $post):  ?>
	    <tr id="post-row-<?php echo $post->id ?>" class="<?php echo Text::alternate("odd", "even"); ?>">
	    
		<td><?php echo Form::checkbox('posts['.$post->id.']', $post->id, isset($_POST['posts'][$post->id]) ); ?> </td>

		<td> <?php echo HTML::anchor($post->url, $post->title, array('class'=>'action-view')) ?> </td>
		<td> <?php echo HTML::anchor(Route::get('user')->uri(array('action' => 'profile',
			'id' => $post->author)), $post->user->nick, array()) ?>
		
		</td>
		<td class="status"> <span class="label label-<?php echo $post->status; ?>"><?php echo $post->status; ?></span> </td>
		<td> <?php echo Gleez::date($post->updated); ?> </td>
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