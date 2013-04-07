<?php defined('SYSPATH') OR die('No direct script access.');

	echo Form::open($action, array('id'=>'page-form', 'class'=>'post-form form'));

	include Kohana::find_file('views', 'errors/partial');
?>

	<div class="row-fluid">

		<div id="post-body" class="span9">

			<div class="control-group <?php echo isset($errors['title']) ? 'error': ''; ?>">
				<div class="controls">
					<?php echo Form::input('title', $post->rawtitle, array('class' => 'span6', 'placeholder' => __('Enter title here'))); ?>
				</div>
			</div>

			<?php if (ACL::check('administer content') OR ACL::check('administer page')) : ?>
				<div class="control-group <?php echo isset($errors['slug']) ? 'error': ''; ?>">
					<?php echo Form::label('path', __('Permalink: %slug', array('%slug' => $site_url )), array('class' => 'control-label')) ?>
					<div class="controls">
						<?php echo Form::input('path', $path, array('class' => 'span6 slug')); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($config->use_tags) : ?>
				<div class="control-group <?php echo isset($errors['ftags']) ? 'error': ''; ?>">
					<?php echo Form::label('ftags', __('Tags'), array('class' => 'control-label') ) ?>
					<div class="controls">
						<?php echo Form::input('ftags', $tags, array('class' => 'span6'), 'autocomplete/tag/page'); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($config->use_excerpt): ?>
				<div class="control-group <?php echo isset($errors['teaser']) ? 'error': ''; ?>">
					<?php echo Form::label('excerpt', __('Excerpt'), array('class' => 'control-label') ) ?>
					<div class="controls">
						<?php echo Form::textarea('excerpt', $post->rawteaser, array('class' => 'textarea medium excerpt', 'rows' => 5)) ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="control-group <?php echo isset($errors['body']) ? 'error': ''; ?>">
				<?php echo Form::label('content', __(''), array('class' => 'control-label') ) ?>
				<div class="controls">
					<?php echo Form::textarea('body', $post->rawbody, array('class' => 'textarea', 'autofocus', 'placeholder' => __('Enter text...'))) ?>
				</div>
			</div>

			<?php if (ACL::check('administer content') OR ACL::check('administer page')): ?>

				<div class="control-group format-wrapper <?php echo isset($errors['format']) ? 'error': ''; ?>">
					<div class="controls">
						<div class="input-prepend">
							<span class="add-on"><?php echo __('Text format') ?></span>
							<?php echo Form::select('format', Filter::formats(), $post->format, array('class' => 'input-large')); ?>
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
								<?php echo Form::select('status', Post::status(), $post->status, array('class' => 'span11')); ?>
							</div>

							<div class="control-group <?php echo isset($errors['sticky']) ? 'error': ''; ?>">
								<?php
									$sticky  = (isset($post->sticky) AND $post->sticky == 1) ? TRUE : FALSE;
									$promote = (isset($post->promote) AND $post->promote == 1) ? TRUE : FALSE;
								?>
								<div class="controls">
									<?php echo Form::label('sticky', Form::checkbox('sticky', 1, $sticky).__('Sticky this Post'), array('class' => 'checkbox')) ?>
								</div>
								<div class="controls">
									<?php echo Form::label('promote', Form::checkbox('promote', 1, $promote).__('Promote this Post'), array('class' => 'checkbox')) ?>
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
							<?php if ($post->loaded() AND ACL::post('delete', $post)): ?>
								<div id="delete-action" class="pull-left">
									<i class="icon-trash"></i>
									<?php echo HTML::anchor($post->delete_url.URL::query($destination), __('Move to Trash'), array('class' => 'submitdelete')) ?>
								</div>
							<?php endif; ?>

							<div id="publishing-action">
								<?php echo Form::submit('page', __('Save'), array('class' => 'btn btn-primary pull-right')) ?>
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
							<?php echo Form::select('categories[1]', $terms, $post->terms_form, array('class' => 'span11')); ?>
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
								if ( ! isset($post->comment))
								{
									$post->comment = $config->comment;
								}

								$comment1 = (isset($post->comment) AND $post->comment == 0) ? TRUE : FALSE;
								$comment2 = (isset($post->comment) AND $post->comment == 1) ? TRUE : FALSE;
								$comment3 = (isset($post->comment) AND $post->comment == 2) ? TRUE : FALSE;
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

	<?php echo Form::submit('page', __('Save'), array('class' => 'btn btn-primary btn-large')); ?>

<?php echo Form::close() ?>