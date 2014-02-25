<div class="col-md-6">
	<p> 
		<?php echo __('You have been sent here by <strong>%client</strong>. %client would like to access the following data:', array('%client' => ucfirst($client->title))); ?>
	</p>

	<div class="col-md-6">
		<?php echo Form::open($action); ?>
			<input type="hidden" name="authorize" value="1" />
			<?php echo Form::submit('oauth2', __('Yes, I Authorize This Request'), array('class' => 'btn btn-success')); ?>
		<?php echo Form::close() ?>
	</div>

	<div class="col-md-6">
		<?php echo Form::open($action); ?>
			<input type="hidden" name="authorize" value="0" />
			<?php echo Form::submit('oauth2', __('Cancel'), array('class' => 'btn btn-default')); ?>
		<?php echo Form::close() ?>
	</div>
</div>