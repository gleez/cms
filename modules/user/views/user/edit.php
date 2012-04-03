<?php defined('SYSPATH') or die('No direct script access.'); ?>

<?php echo Form::open( Route::get('user')->uri( array('id' => $user->id, 'action' => 'edit') ), array('class' => 'form-horizontal', 'enctype' => 'multipart/form-data') ) ?>

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
        
        <div class="control-group <?php echo isset($errors['nick']) ? 'error': ''; ?>">
                <?php echo Form::label('nick', 'Display Name: ', array('class' => 'control-label')) ?>
                <?php echo Form::input('nick', $user->nick, array('class' => 'input-large')); ?>
        </div>

  
        <div class="control-group <?php echo isset($errors['mail']) ? 'error': ''; ?>">
                <?php echo Form::label('mail', 'Mail:', array('class' => 'control-label')) ?>
                <?php echo Form::input('mail', $user->mail, array('class' => 'input-large')); ?>
        </div>
        
        <div class="control-group <?php echo isset($errors['gender']) ? 'error': ''; ?>">
		<?php $gender1 = (isset($user->gender) AND $user->gender == 1) ? TRUE : FALSE; ?>
		<?php $gender2 = (isset($user->gender) AND $user->gender == 2) ? TRUE : FALSE; ?>
		
		<?php echo Form::label('gender', __('Gender: '), array('class' => 'control-label')) ?> 
		<div class="controls">
			<?php echo Form::label('gender1', Form::radio('gender', 1, $gender1).__('Male'), array('class' => 'radio inline')) ?> 
			<?php echo Form::label('gender2', Form::radio('gender', 2, $gender2).__('Female'), array('class' => 'radio inline')) ?>
		</div>
	</div>

	<div class="control-group <?php echo isset($errors['dob']) ? 'error': ''; ?>">
		  <?php echo Form::label('dob', 'Birthday:', array('class' => 'control-label')) ?>
		  <?php echo Form::select('month', Date::months(Date::MONTHS_SHORT), '', array('class' => 'input-small')); ?>
		  <?php echo Form::select('days',  Date::days(Date::DAY), '', array('class' => 'input-small')); ?>
		  <?php echo Form::select('years', Date::years(date('Y') - 95,date('Y') - 5), 2000, array('class' => 'input-small')); ?>
	</div>

        <div class="control-group <?php echo isset($errors['picture']) ? 'error': ''; ?>">
                <?php //echo Form::label('mail', 'Photo:', array('class' => 'control-label')) ?>
                <?php //echo Form::file('picture', array('class' => 'input-file')); ?>
        </div>
	
<?php echo Form::submit('user_edit', __('Update Profile'), array('class' => 'btn btn-primary')) ?>
<?php echo Form::close() ?>