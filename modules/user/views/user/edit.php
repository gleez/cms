<div class="row">
	<div class="col-md-3 col-sm-4">
		<?php include Kohana::find_file('views', 'user/edit_link'); ?>
	</div>

	<div class="col-md-9 col-sm-8">
		<?php include Kohana::find_file('views', 'errors/partial'); ?>
		<?php echo Form::open($action, array('class' => 'form form-horizontal')); ?>
		<div class="stacked-content">
			<div class="tab-pane fade in active" id="profile-tab">
				<div class="panel panel-default window-shadow">
					<div class="panel-body">
						<div class="form-group <?php echo isset($errors['nick']) ? 'has-error': ''; ?>">
							<?php echo Form::label('nick', __('Display Name'), array('class' => 'col-sm-3 control-label')) ?>
							<div class="col-sm-9">
								<div class="input-group col-sm-6">
									<span class="input-group-addon"><i class="fa fa-user"></i></span>
									<?php echo Form::input('nick', $user->nick, array('class' => 'form-control', 'rel' => 'tooltip', 'data-placement' => 'right', 'title' => __('Will be public'))); ?>
								</div>
							</div>
						</div>

						<div class="form-group <?php echo isset($errors['gender']) ? 'has-error': ''; ?>">
							<?php echo Form::label('gender', __('Gender'), array('class' => 'col-sm-3 control-label')) ?>
							<div class="col-sm-9">
								<div class="input-group col-sm-10">
									<div class="radio">
										<?php echo Form::label('gender1', Form::radio('gender', 1, $male).__('Male')) ?>
									</div>
									<div class="radio">
										<?php echo Form::label('gender2', Form::radio('gender', 2, $female).__('Female')) ?>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group <?php echo isset($errors['dob']) ? 'has-error': ''; ?>">
							<?php echo Form::label('dob', __('Birthday'), array('class' => 'col-sm-3 control-label')) ?>
							<div class="col-sm-9">
								<div class="col-sm-3">
									<?php echo Form::select('month', Date::months(Date::MONTHS_SHORT), date('n', $user->dob), array('class' => 'form-control input-sm')) ?>
								</div>
								<div class="col-sm-3">
									<?php echo Form::select('days',  Date::days(Date::DAY), date('j', $user->dob), array('class' => 'form-control input-sm')) ?>
								</div>
								<div class="col-sm-3">
									<?php echo Form::select('years', Date::years(date('Y') - 95,date('Y') - 5), date('Y', $user->dob), array('class' => 'form-control input-sm')) ?>
								</div>
							</div>
						</div>

						<div class="form-group <?php echo isset($errors['homepage']) ? 'has-error': ''; ?>">
							<?php echo Form::label('homepage', __('Home Page'), array('class' => 'col-sm-3 control-label')) ?>
							<div class="col-sm-9">
								<div class="input-group col-sm-10">
									<span class="input-group-addon"><i class="fa fa-link"></i></span>
									<?php echo Form::input('homepage', $user->homepage, array('class' => 'form-control', 'rel' => 'tooltip', 'data-placement' => 'right', 'title' => __('Will be public'))); ?>
								</div>
							</div>
						</div>

						<div class="form-group <?php echo isset($errors['mail']) ? 'has-error': ''; ?>">
							<?php echo Form::label('mail', __('E-Mail'), array('class' => 'col-sm-3 control-label')) ?>
							<div class="col-sm-9">
								<div class="input-group col-sm-10">
									<span class="input-group-addon"><i class="fa fa-envelope"></i></span>
									<?php echo Form::input('mail', $user->mail, array('class' => 'form-control', 'rel' => 'tooltip', 'data-placement' => 'right', 'title' => __('Will be private'))); ?>
								</div>
							</div>
						</div>

						<div class="form-group <?php echo isset($errors['bio']) ? 'has-error': ''; ?>">
							<?php echo Form::label('bio', __('Bio'), array('class' => 'col-sm-3 control-label')) ?>
							<div class="col-sm-9">
								<div class="input-group col-sm-10">
									<?php echo Form::textarea('bio', $user->bio, array('class' => 'form-control', 'rows' => 8)); ?>
									<span class="help-block"><?php _e('Public field. No more than 800 characters') ?></span>
								</div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<div class="col-sm-12 clearfix">
							<div class="col-xs-6">
								<?php echo HTML::anchor(Route::get('user')->uri(array('action' => 'profile')), '<i class="fa fa-arrow-left"></i> '.__('Profile'), array('class' => 'btn')); ?>
							</div>
							<div class="col-xs-6">
								<?php echo Form::button('user_edit', __('Save'), array('class' => 'btn btn-success pull-right', 'type' => 'submit'))?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php echo Form::close() ?>
	</div>
</div>
