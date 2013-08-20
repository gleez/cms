<?php echo Form::open($action, array('id'=>'blog-form', 'class'=>'blog-form form', 'enctype' => 'multipart/form-data')); ?>

	<?php include Kohana::find_file('views', 'errors/partial'); ?>

	<div class="row-fluid">

		<div id="blog-body" class="span9">

			<div class="control-group <?php echo isset($errors['title']) ? 'error': ''; ?>">
				<div class="controls">
					<?php echo Form::input('title', $blog->rawtitle, array('class' => 'span12', 'placeholder' => __('Enter title here'))); ?>
				</div>
			</div>

			<?php if (ACL::check('administer content') OR ACL::check('administer page')) : ?>
				<div class="control-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
					<?php echo Form::label('path', __('Permalink: %slug', array('%slug' => $site_url )), array('class' => 'control-label')) ?>
					<div class="controls">
						<?php echo Form::input('path', $path, array('class' => 'span12 slug')); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($config->use_tags) : ?>
				<div class="control-group <?php echo isset($errors['ftags']) ? 'error': ''; ?>">
					<?php echo Form::label('ftags', __('Tags'), array('class' => 'control-label') ) ?>
					<div class="controls">
						<?php echo Form::input('ftags', $tags, array('class' => 'span12'), 'autocomplete/tag/page'); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($config->primary_image): ?>
				<div class="control-group <?php echo isset($errors['image']) ? 'error': ''; ?>">
					<?php echo Form::label('image', __('Primary Image'), array('class' => 'control-label') ) ?>
					<div class="controls">
						<?php echo Form::file('image', array('class' => 'span12')); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($config->use_excerpt): ?>
				<div class="control-group <?php echo isset($errors['teaser']) ? 'error': ''; ?>">
					<?php echo Form::label('excerpt', __('Excerpt'), array('class' => 'control-label') ) ?>
					<div class="controls">
						<?php echo Form::textarea('excerpt', $blog->rawteaser, array('class' => 'textarea span12 excerpt', 'rows' => 5)) ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="control-group <?php echo isset($errors['body']) ? 'error': ''; ?>">
				<?php echo Form::label('body', __('Content'), array('class' => 'control-label')) ?>
				<div class="controls">
					<?php echo Form::textarea('body', $blog->rawbody, array('class' => 'textarea span12', 'autofocus', 'placeholder' => __('Enter text...'))) ?>
				</div>
			</div>

			<?php if (ACL::check('administer content') OR ACL::check('administer page')): ?>

				<div class="control-group format-wrapper <?php echo isset($errors['format']) ? 'error': ''; ?>">
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on"><?php echo __('Text format') ?></span>
							<?php echo Form::select('format', Filter::formats(), $blog->format, array('class' => 'input-large')); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

		</div>

		<div id="side-info-column" class="span3 inner-sidebar">
			<?php if (ACL::check('administer content') OR ACL::check('administer page')): ?>
				<div id="submitdiv" class="postbox">
					<h3 class='hndle'><?php echo __('Publication') ?></h3>

					<div class='inside' id="submitpost">
						<div id="minor-publishing">
							<div class="control-group <?php echo isset($errors['status']) ? 'error': ''; ?>">
								<?php echo Form::label('status', __('Status'), array('class' => 'control-label')) ?>
								<?php echo Form::select('status', Post::status(), $blog->status, array('class' => 'span11')); ?>
							</div>

							<div class="control-group <?php echo isset($errors['sticky']) ? 'error': ''; ?>">
								<?php
									$sticky  = (isset($blog->sticky) AND $blog->sticky == 1) ? TRUE : FALSE;
									$promote = (isset($blog->promote) AND $blog->promote == 1) ? TRUE : FALSE;
									echo Form::hidden('sticky', 0);
									echo Form::hidden('promote', 0);
								?>
								<div class="controls">
									<?php echo Form::label('sticky', Form::checkbox('sticky', TRUE, $sticky).__('Sticky this Post'), array('class' => 'checkbox')) ?>
								</div>
								<div class="controls">
									<?php echo Form::label('promote', Form::checkbox('promote', TRUE, $promote).__('Promote this Post'), array('class' => 'checkbox')) ?>
								</div>
							</div>

							<div class="control-group <?php echo isset($errors['author_date']) ? 'error': ''; ?>">
								<?php echo Form::label('author_date', __('Date'), array('class' => 'control-label') ) ?>
								<div class="controls">
									<?php echo Form::input('author_date', $created, array('class' => 'span11')); ?>
								</div>
							</div>

							<?php if ($config->use_authors): ?>
								<div class="control-group <?php echo isset($errors['author_name']) ? 'error': ''; ?>">
									<?php echo Form::label('author_name', __('Author'), array('class' => 'control-label') ) ?>
									<div class="controls">
										<?php echo Form::input('author_name', $author,array('class' => 'span11', 'data-items' => 10), 'autocomplete/user'); ?>
									</div>
								</div>
							<?php endif; ?>
						</div>

						<div id="major-publishing-actions" class="row-fluid">
							<?php if ($blog->loaded() AND ACL::post('delete', $blog)): ?>
								<div id="delete-action" class="pull-left">
									<i class="icon-trash"></i>
									<?php echo HTML::anchor($blog->delete_url.URL::query($destination), __('Move to Trash'), array('class' => 'submitdelete')) ?>
								</div>
							<?php endif; ?>

							<div id="publishing-action">
								<?php echo Form::submit('blog', __('Save'), array('class' => 'btn btn-success pull-right')) ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($config->use_category) : ?>
				<div id="categorydiv" class="postbox">
					<h3 class='hndle'><?php echo __('Category'); ?></h3>
					<div class='inside'>
						<div class="control-group <?php echo isset($errors['categories']) ? 'error': ''; ?>">
							<?php echo Form::select('categories[1]', $terms, $blog->terms_form, array('class' => 'span11')); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if( $config->use_comment) : ?>
				<div id="commentdiv" class="postbox">
					<h3 class='hndle'><?php echo  __('Comments'); ?></h3>

					<div class='inside'>
						<div class="control-group <?php echo isset($errors['comment']) ? 'error': ''; ?>">
							<?php
								if ( ! isset($blog->comment))
								{
									$blog->comment = $config->comment;
								}

								$comment1 = (isset($blog->comment) AND $blog->comment == 0) ? TRUE : FALSE;
								$comment2 = (isset($blog->comment) AND $blog->comment == 1) ? TRUE : FALSE;
								$comment3 = (isset($blog->comment) AND $blog->comment == 2) ? TRUE : FALSE;
							?>

							<?php echo Form::label('comment', __('Discussion') ) ?>
							<div class="controls">
								<?php echo Form::label('comment', Form::radio('comment', 0, $comment1).__('Disabled'), array('class' => 'radio')) ?>
							</div>

							<div class="controls">
								<?php echo Form::label('comment', Form::radio('comment', 1, $comment2).__('Read only'), array('class' => 'radio')) ?>
							</div>

							<div class="controls">
								<?php echo Form::label('comment', Form::radio('comment', 2, $comment3).__('Read/Write'), array('class' => 'radio')) ?>
							</div>

						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="clearfix"></div>

	<?php if ($config->use_captcha  AND ! $captcha->promoted()): ?>
		<div class="control-group <?php echo isset($errors['captcha']) ? 'error': ''; ?>">
			<?php echo Form::label('_captcha', __('Security'), array('class' => 'wrap')) ?>
			<?php echo Form::input('_captcha', '', array('class' => 'text tiny')); ?><br>
			<?php echo $captcha; ?>
		</div>
	<?php endif; ?>

	<?php echo Form::submit('blog', __('Save'), array('class' => 'btn btn-success')); ?>

<?php echo Form::close() ?>