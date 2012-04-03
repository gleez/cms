<?php defined('SYSPATH') or die('No direct script access.') ?>

<?php 
	if ( isset($post->id) AND Valid::digit($post->id) )
	{
		$parms = array('id' => $post->id, 'action' => 'edit');
		$path = $post->url;
	}
	else
	{
		$parms = array('action' => 'add');
		$path = FALSE;
	}
        echo Form::open( Route::get('admin/tag')->uri($parms) ) ?>

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

<div class="control-group <?php echo isset($errors['tag']) ? 'error': ''; ?>">
 	<?php echo Form::label('name', __('Tag: ')) ?>
 	<?php print Form::input('name', $post->name, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['type']) ? 'error': ''; ?>">
	<?php echo Form::label('type', __('Type:'), array('class' => 'aboveconte')) ?>
	<?php echo Form::select('type', Gleez::types(), $post->type, array('class' => 'list small')); ?> 
</div>

<div class="control-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
	<?php echo Form::label('path', __('Slug: %slug', array('%slug' => $site_url )),
								array('class' => 'nowrap')) ?>
	<?php echo Form::input('path', $path, array('class' => 'text small slug')); ?>
</div>

<?php echo form::button('tag', __('Submit'), array('class' => 'btn btn-primary')) ?>

<?php echo form::close() ?>