<div class="col-sm-6 col-sm-offset-3">
	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="panel panel-default window-shadow">
		<div class="panel-heading">
			<h3 class="panel-title">
				<?php _e('Enter email specified at registration for password reset')?>
			</h3>
		</div>
		<?php echo Form::open($action, array('class' => 'form form-horizontal')) ?>
			<div class="panel-body">
				<div class="form-group <?php echo isset($errors['mail']) ? 'has-error': ''; ?>">
					<div class="col-md-12">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-envelope-o fa-fw"></i></span>
							<?php echo Form::input('mail', $post['mail'], array('class' => 'form-control input-lg')); ?>
						</div>
					</div>
				</div>
			</div>
			<div class="panel-footer">
				<div class="col-md-12 clearfix">
					<?php echo Form::button('reset_pass', __('Reset'), array('class' => 'btn btn-danger pull-right', 'type' => 'submit'))?>
				</div>
			</div>
		<?php echo Form::close() ?>
	</div>
</div>
