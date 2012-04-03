<?php defined('SYSPATH') or die('No direct script access.') ?>

<div class="help">
	<?php echo __('Enter the path you wish to create the alias for, followed by the name of the new alias.'); ?>
</div>

<?php $parms = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
        echo Form::open( Route::get('admin/path')->uri($parms) ) ?>

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

<div class="control-group <?php echo isset($errors['source']) ? 'error': ''; ?>">
 	<?php echo Form::label('source', __('Existing path: %site_url', array('%site_url' => $site_url) ) ) ?>
 	<?php echo Form::input('source', $post->source, array('class' => 'text medium')); ?>
</div>

<div class="control-group <?php echo isset($errors['alias']) ? 'error': ''; ?>">
 	<?php echo Form::label('alias', __('Alias: %site_url', array('%site_url' => $site_url) )) ?>
 	<?php echo Form::input('alias', $post->alias, array('class' => 'text medium')); ?>
</div>

<?php echo form::button('path', __('Submit'), array('class' => 'btn btn-primary')) ?>
<?php echo form::close() ?>