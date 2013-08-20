	<div class="row">

		<?php if ($sidebar_left): $main_column = 10; echo '<div id="left" class="span2">'. $sidebar_left .'</div>'; endif; ?>
		<?php if ($sidebar_right): $main_column = 10; echo '<div id="left" class="span2">'. $sidebar_right .'</div>'; endif; ?>

		<div id="row-content" class="span<?php echo $main_column; ?>">

			<?php echo '<div id="messages" class="messages span9">'. $messages .'</div>'; ?>

			<div id="pageContent" class="well">
				<?php if ($title): ?>
					<header id="overview" class="jumbotron subhead">
						<div class="page-header <?php echo ($tabs ? ' with-tabs' : ''); ?>">
							<h1><?php echo $title;?></h1>
							<?php if ($tabs): ?>
								<div id="tabs-actions" class="row-fluid11">
									<?php if($tabs):?>
										<div id="tabs"> <?php echo $tabs; ?> </div>
									<?php endif; ?>
								</div>
							<?php endif;?>
						</div>
					</header>
				<?php endif; ?>

				<div id="content"><?php echo $content; ?></div>
			</div>
		</div>

	</div>