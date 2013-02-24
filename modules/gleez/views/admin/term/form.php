<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php
	if ( isset($post->id) AND Valid::digit($post->id) )
	{
		$parms = array('id' => $post->id, 'action' => 'edit');
		$terms = $post->select_list('id', 'name', '--');
		$path = $post->url;
	}
	else
	{
		$parms = array('action' => 'add', 'id' => $vocab->id);
		$terms = $vocab->select_list('id', 'name', '--');
		$path = FALSE;
	}
	
	echo Form::open(Route::get('admin/term')->uri($parms), array('id'=>'term-form', 'class'=>'form')) ?>

	<?php if ( ! empty($errors)): ?>
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

<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
	<?php echo Form::label('name', 'Name:') ?>
   	<?php echo Form::input('name', $post->rawname, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['parent']) ? 'error': ''; ?>">
	<?php echo Form::label('parent', 'Parent:', array('class' => 'nowrap')) ?>
	<?php echo Form::select('parent', $terms, $post->pid, array('class' => 'list small')); ?> 
</div>

<div class="control-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
	<?php echo Form::label('path', __('Slug: %slug', array('%slug' => $site_url )),
								array('class' => 'nowrap')) ?>
	<?php echo Form::input('path', $path, array('class' => 'text small slug')); ?>
</div>

<div class="control-group <?php echo isset($errors['description']) ? 'error': ''; ?>">
 	<?php echo Form::label('description', 'Description:') ?>
 	<?php echo Form::textarea('description', $post->description, array('class' => 'input-large', 'rows' => 5)) ?>
</div>
<?php //Message::debug( Debug::vars($post->parent->id));?>
<?php echo Form::submit('term', __('Submit'), array('class' => 'btn btn-primary btn-large')) ?>
<?php echo Form::close() ?>
