<?php
	$params = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
	$destination = isset($GET['destination']) ? $GET['destination']: '';

	echo Form::open(Route::get('admin/user')->uri($params).URL::query(array($destination)), array('id'=>'user-form', 'class'=>'user-form form form-horizontal'));
	include Kohana::find_file('views', 'errors/partial');
?>

<div class="form-group <?php echo isset($errors['name']) ? 'has-error': ''; ?>">
	<?php echo Form::label('username', __('User name'), array('class' => 'control-label col-sm-3')) ?>
	<div class="controls col-sm-5">
	<?php print Form::input('name', $post->name, array('class' => 'form-control')); ?>
	</div>
</div>

<div class="form-group <?php echo isset($errors['pass']) ? 'has-error': ''; ?>">
	<?php echo Form::label('password', __('Password'), array('class' => 'control-label col-sm-3')) ?>
	<div class="controls col-sm-5">
	<?php echo Form::Password('pass', null, array('class' => 'form-control')) ?>
	</div>
</div>

<div class="form-group <?php echo isset($errors['nick']) ? 'has-error': ''; ?>">
	<?php echo Form::label('nick', __('Nick name'), array('class' => 'control-label col-sm-3')) ?>
	<div class="controls col-sm-5">
	<?php print Form::input('nick', $post->nick, array('class' => 'form-control')); ?>
	</div>
</div>

<div class="form-group <?php echo isset($errors['mail']) ? 'has-error': ''; ?>">
	<?php echo Form::label('mail', __('Email'), array('class' => 'control-label col-sm-3')) ?>
	<div class="controls col-sm-5">
	<?php print Form::input('mail', $post->mail, array('class' => 'form-control')); ?>
	</div>
</div>

<div class="form-group <?php echo isset($errors['status']) ? 'has-error': ''; ?>">
	<?php echo Form::label('active', __('Status'), array('class' => 'control-label col-sm-3')) ?>
	<div class="controls col-sm-5">
	<?php print Form::select('status', array(0 => __('Blocked'), 1 => __('Active')), $post->status, array('class' => 'form-control')); ?>
	</div>
</div>

<table class="table table-striped table-bordered table-apparent">
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

<div class="form-group">
	<div class="col-sm-12 form-actions-right">
		<?php echo Form::button('user', __('Save'), array('class' => 'btn btn-success', 'type' => 'submit'))?>
	</div>
</div>

<?php echo Form::close(); ?>
