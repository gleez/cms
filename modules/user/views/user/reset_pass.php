<div class="account-container col-md-8">
	<div class="content clearfix">
		<?php echo Form::open($action, array('class' => 'form form-horizontal', 'role' => 'form')) ?>

		<?php include Kohana::find_file('views', 'errors/partial'); ?>

		<fieldset>
			<legend><?php _e('Enter email specified at registration for password reset')?></legend>
			<div class="form-group <?php echo isset($errors['mail']) ? 'has-error': ''; ?>">
				<?php echo Form::label('mail', __('Email'), array('class' => 'col-md-2  control-label')); ?>
				<div class="col-md-10">
					<div class="row">
						<div class="input-group col-sm-10">
							<span class="input-group-addon">@</span>
							<?php echo Form::input('mail', $post['mail'], array('class' => 'form-control input-lg')); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<div class="col-md-10">
					<?php echo Form::button('reset_pass', __('Reset'), array('class' => 'btn btn-danger pull-right', 'type' => 'submit'))?>
				</div>
			</div>
		</fieldset>
		<?php echo Form::close() ?>
	</div>
</div>