<?php
	$parms = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
	$destination = isset($GET['destination']) ? $GET['destination']: '';

	echo Form::open( Route::get('admin/user')->uri($parms).URL::query(array($destination)), array('id'=>'user-form ', 'class'=>'user-form form'));
	include Kohana::find_file('views', 'errors/partial');
?>

<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
	<?php echo Form::label('username', __('User name')) ?>
	<?php print Form::input('name', $post->name, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
	<?php echo Form::label('password', __('Password')) ?>
	<?php echo Form::Password('pass', null, array('class' => 'text small')) ?>
</div>

<div class="control-group <?php echo isset($errors['nick']) ? 'error': ''; ?>">
	<?php echo Form::label('nick', __('Nick name')) ?>
	<?php print Form::input('nick', $post->nick, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['mail']) ? 'error': ''; ?>">
	<?php echo Form::label('mail', __('Email')) ?>
	<?php print Form::input('mail', $post->mail, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['status']) ? 'error': ''; ?>">
	<?php echo Form::label('active', __('Status')) ?>
	<?php print Form::select('status', array(0 => __('Blocked'), 1 => __('Active')), $post->status, array('class' => 'list small')); ?>
</div>

<table class="table table-striped table-bordered table-highlight">
	<thead>
		<tr>
			<th>#</th>
			<th><?php echo __('Role') ?></th>
			<th><?php echo __('Description') ?></th>
		</tr>
	</thead>
	<?php foreach($all_roles as $role => $description): ?>
		<tr class ="<?php echo Text::alternate('odd', 'even') ?>">
			<td><?php echo Form::checkbox('roles['.$role.']', $description, in_array($role, $user_roles)) ?></td>
			<td><?php echo ucfirst(__($role)) ?></td>
			<td><?php echo $description ?></td>
		</tr>
	<?php endforeach; ?>
</table>

<?php echo Form::hidden('site_url', URL::site(NULL, TRUE), array('id'=>'site_url')) ?>
<?php echo Form::submit('user', __('Save'), array('class' => 'btn btn-success pull-right')) ?>

<?php echo Form::close(); ?>