<?php
	echo Form::open($action, array('id'=>'page-form', 'class'=>'post-form form', 'enctype' => 'multipart/form-data'));

	include Kohana::find_file('views', 'errors/partial');
?>

	<div class="row">

		<div id="post-body" class="col-md-9">

			<div class="form-group <?php echo isset($errors['title']) ? 'has-error': ''; ?>">
				<div class="controls">
					<?php echo Form::input('title', $post->rawtitle, array('class' => 'form-control', 'placeholder' => __('Enter title here'))); ?>
				</div>
			</div>

			<?php if (ACL::check('administer content') OR ACL::check('administer page')) : ?>
				<div class="form-group <?php echo isset($errors['slug']) ? 'has-error': ''; ?>">
					<?php echo Form::label('path', __('Permalink: %slug', array('%slug' => $site_url )), array('class' => 'control-label')) ?>
					<div class="controls">
						<?php echo Form::input('path', $path, array('class' => 'form-control slug')); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($config->use_tags) : ?>
				<div class="form-group <?php echo isset($errors['ftags']) ? 'has-error': ''; ?>">
					<?php echo Form::label('ftags', __('Tags'), array('class' => 'control-label') ) ?>
					<div class="controls">
						<?php echo Form::input('ftags', $tags, array('class' => 'form-control'), 'autocomplete/tag/page'); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($config->primary_image): ?>
				<div class="form-group <?php echo isset($errors['image']) ? 'has-error': ''; ?>">
					<?php echo Form::label('image', __('Primary Image'), array('class' => 'control-label') ) ?>
					<div class="controls page-img">
						<?php echo Form::file('image', array('class' => 'form-control')); ?>
					</div>
				</div>
			<?php endif; ?>
	
			<?php if ($config->use_excerpt): ?>
				<div class="form-group <?php echo isset($errors['teaser']) ? 'has-error': ''; ?>">
					<?php echo Form::label('excerpt', __('Excerpt'), array('class' => 'control-label') ) ?>
					<div class="controls">
						<?php echo Form::textarea('excerpt', $post->rawteaser, array('class' => 'form-control', 'rows' => 3)) ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="form-group <?php echo isset($errors['body']) ? 'has-error': ''; ?>">
				<?php echo Form::label('body', __('Content'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::textarea('body', $post->rawbody, array('class' => 'textarea form-control', 'autofocus', 'placeholder' => __('Enter text...'))) ?>
				</div>
			</div>

			<?php if (ACL::check('administer content') OR ACL::check('administer page')): ?>

				<div class="form-group format-wrapper <?php echo isset($errors['format']) ? 'has-error': ''; ?>">
					<div class="controls">
						<div class="input-group">
							<span class="input-group-addon"><?php echo __('Text format') ?></span>
							<?php echo Form::select('format', Filter::formats(), $post->format, array('class' => 'form-control')); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
			
			<?php if ($config->use_captcha  AND ! $captcha->promoted()): ?>
				<div class="form-group <?php echo isset($errors['captcha']) ? 'has-error': ''; ?>">
					<?php echo Form::label('_captcha', __('Security'), array('class' => 'wrap')) ?>
					<?php echo Form::input('_captcha', '', array('class' => 'form-control')); ?><br>
					<?php echo $captcha; ?>
				</div>
	<?php endif; ?>

		</div>

		<div id="side-info-column" class="col-md-3">
			<?php if (ACL::check('administer content') OR ACL::check('administer page')): ?>
				<div id="submitdiv" class="panel panel-info">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo __('Publication') ?></h3>
				</div>
					<div class='panel-body' id="submitpost">
						<div id="minor-publishing">
						
							<div class="form-group <?php echo isset($errors['status']) ? 'has-error': ''; ?>">
								<?php echo Form::label('status', __('Status'), array('class' => 'control-label')) ?>
								<?php echo Form::select('status', Post::status(), $post->status, array('class' => 'form-control input-sm')); ?>
							</div>
							<div class="form-group <?php echo isset($errors['sticky']) ? 'has-error': ''; ?>">
								<?php
									$sticky  = (isset($post->sticky) AND $post->sticky == 1) ? TRUE : FALSE;
									$promote = (isset($post->promote) AND $post->promote == 1) ? TRUE : FALSE;
									echo Form::hidden('sticky', 0);
									echo Form::hidden('promote', 0);
								?>
								<div class="controls checkbox">
									<?php echo Form::label('sticky', Form::checkbox('sticky', TRUE, $sticky).__('Sticky this Post')) ?>
								</div>
								<div class="controls checkbox">
									<?php echo Form::label('promote', Form::checkbox('promote', TRUE, $promote).__('Promote this Post')) ?>
								</div>
							</div>
								<div class="clearfix"></div>

							<div class="form-group <?php echo isset($errors['author_date']) ? 'has-error': ''; ?>">
								<?php echo Form::label('author_date', __('Date'), array('class' => 'control-label') ) ?>
								<div class="controls">
									<?php echo Form::input('author_date', $created, array('class' => 'form-control')); ?>
								</div>
							</div>

							<?php if ($config->use_authors): ?>
								<div class="form-group <?php echo isset($errors['author_name']) ? 'has-error': ''; ?>">
									<?php echo Form::label('author_name', __('Author'), array('class' => 'control-label') ) ?>
									<div class="controls">
										<?php echo Form::input('author_name', $author,array('class' => 'form-control', 'data-items' => 10), 'autocomplete/user'); ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="panel-footer">
						<div id="major-publishing-actions" class="row">
							<?php if ($post->loaded() AND ACL::post('delete', $post)): ?>
								<div id="delete-action" class="btn btn-default pull-left">
									<i class="fa fa-trash-o"></i>
									<?php echo HTML::anchor($post->delete_url.URL::query($destination), __('Move to Trash'), array('class' => 'submitdelete')) ?>
								</div>
							<?php endif; ?>

							<div id="publishing-action">
								<?php echo Form::submit('page', __('Save'), array('class' => 'btn btn-success pull-right')) ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($config->use_category) : ?>
				<div id="categorydiv" class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?php echo __('Category'); ?></h3>
				</div>
					<div class='panel-body'>
						<div class="form-group <?php echo isset($errors['categories']) ? 'has-error': ''; ?>">
							<?php echo Form::select('categories[1]', $terms, $post->terms_form, array('class' => 'form-control')); ?>
						</div>
					</div>
				</div>
				
			<?php endif; ?>

			<?php if( $config->use_comment) : ?>
				<div id="commentdiv" class="panel panel-default">
					<div class="panel-heading">
					<h3 class="panel-title"><?php echo  __('Comments'); ?></h3>
					</div>

					<div class='panel-body'>
						<div class="form-group <?php echo isset($errors['comment']) ? 'has-error': ''; ?>">
							<?php
								if ( ! isset($post->comment))
								{
									$post->comment = $config->comment;
								}

								$comment1 = (isset($post->comment) AND $post->comment == 0) ? TRUE : FALSE;
								$comment2 = (isset($post->comment) AND $post->comment == 1) ? TRUE : FALSE;
								$comment3 = (isset($post->comment) AND $post->comment == 2) ? TRUE : FALSE;
							?>

							<?php echo Form::label('comment', __('Discussion') ) ?>
							<div class="controls radio">
								<?php echo Form::label('comment', Form::radio('comment', 0, $comment1).__('Disabled')) ?>
							</div>

							<div class="controls radio">
								<?php echo Form::label('comment', Form::radio('comment', 1, $comment2).__('Read only')) ?>
							</div>

							<div class="controls radio">
								<?php echo Form::label('comment', Form::radio('comment', 2, $comment3).__('Read/Write')) ?>
							</div>

						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="clearfix"></div>


	<?php echo Form::submit('page', __('Save'), array('class' => 'btn btn-success bth-lg')); ?>

<?php echo Form::close() ?>
