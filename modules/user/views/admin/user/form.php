<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?php $parms = isset($post->id) ? array('id' => $post->id, 'action' => 'edit') : array('action' => 'add');
	$destination = isset($GET['destination']) ? $GET['destination']: '';
        echo Form::open( Route::get('admin/user')->uri($parms).URL::query( array($destination) ) ) ?>

	<?php if ( ! empty($errors)): ?>
		<div id="formerrors" class="errorbox">
			<h3>Ooops!</h3>
			<ol>
				<?php foreach($errors as $field => $message): ?>
					<li>	
						<?php echo $field .': '.$message ?>
					</li>
				<?php endforeach ?>
			</ol>
		</div>
	<?php endif ?>


<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
    <?php echo Form::label('username', 'Username:') ?>
    <?php print Form::input('name', $post->name, array('class' => 'text small')); ?>
</div>

<?php //if( !$form_edit ) : ?>
<div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
    <?php echo Form::label('password', 'Password:') ?>
    <?php echo Form::Password('pass', null, array('class' => 'text small')) ?>
</div>
<?php //endif; ?>

<div class="control-group <?php echo isset($errors['nick']) ? 'error': ''; ?>">
 	<?php echo Form::label('nick', 'Name: ') ?>
 	<?php print Form::input('nick', $post->nick, array('class' => 'text small')); ?>
</div>

  
<div class="control-group <?php echo isset($errors['mail']) ? 'error': ''; ?>">
	<?php echo Form::label('mail', 'Mail:') ?>
 	<?php print Form::input('mail', $post->mail, array('class' => 'text small')); ?>
</div>

<div class="control-group <?php echo isset($errors['status']) ? 'error': ''; ?>">
    	<?php echo Form::label('active', 'Status: ') ?> 
    	<?php print Form::select('status', array(0 => 'Blocked', 1 => 'Active'), $post->status, array('class' => 'list small')); ?>
</div>

<table class="table table-striped table-bordered table-condensed">
    <thead>
	<tr>
	    <th>#</th>
	    <th><?php echo __('Role') ?></th>
	    <th><?php echo __('Description') ?></th>
	</tr>
    </thead>
    <?php
	foreach($all_roles as $role => $des)
	{
	    echo '<tr class ="'.Text::alternate("odd", "even").'">';
	    echo '<td>'.Form::checkbox('roles['.$role.']', $des, in_array($role, $user_roles)).'</td>';
	    echo '<td>'.ucfirst($role).'</td><td>'.$des.'</td>';
	    echo '</tr>';
	}
    ?>
</table>

<?php echo Form::hidden('site_url', URL::site(NULL, TRUE), array('id'=>'site_url')) ?>
<?php echo Form::submit('user', __('Submit'), array('class' => 'btn btn-primary')) ?>


<?php echo Form::close() ?>


