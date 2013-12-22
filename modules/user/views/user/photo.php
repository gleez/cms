<div class="row">

	<div class="col-md-3 col-sm-4">
		<?php include Kohana::find_file('views', 'user/edit_link'); ?>
	</div>

	<div class="col-md-9 col-sm-8">
		<?php include Kohana::find_file('views', 'errors/partial'); ?>
		<?php echo Form::open(Route::get('user')->uri(array('id' => $user->id, 'action' => 'photo')), array('class' => 'form form-horizontal', 'enctype' => 'multipart/form-data')); ?>
			<div class="stacked-content">
				<div class="tab-pane" id="photo-tab">
					<div class="panel panel-default window-shadow">
						<div class="panel-body">
							<div class="form-group <?php echo isset($errors['picture']) ? 'has-error': ''; ?>">
								<?php echo Form::label('photo', __('Photo'), array('class' => 'col-sm-3 control-label')) ?>
								<div class="col-sm-9">
									<?php echo Form::file('picture', array('class' => 'form-control')); ?>
								</div>
							</div>

							<div class="form-group">
								<div class="col-sm-12">
									<blockquote>
										<small class="muted">
											<?php echo __('Your picture will be changed proportionally to the size of :w&times;:h', array(':w' => 210, ':h' => 210)); ?>
										</small>
										<small class="muted">
											<?php echo __('Allowed image formats: :formats', array(':formats' => '<strong>'.implode('</strong>, <strong>', $allowed_types).'</strong>')); ?>
										</small>
									</blockquote>
								</div>
							</div>
						</div>
						<div class="panel-footer">
							<div class="col-sm-12 clearfix">
								<div class="col-xs-6">
									<?php echo HTML::anchor(Route::get('user')->uri(array('action' => 'profile')), '<i class="fa fa-arrow-left"></i> '.__('Profile'), array('class' => 'btn')); ?>
								</div>
								<div class="col-xs-6">
									<?php echo Form::button('user_edit', __('Upload'), array('class' => 'btn btn-success pull-right', 'type' => 'submit')); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php echo Form::close(); ?>
	</div>
</div>
