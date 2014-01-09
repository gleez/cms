<div id="content-container">
	<div class="row vcard">
	    <div class="list-group list-all panel panel-default">
		
		<div class="panel-heading">
			<div class="col-md-6">
				<h3 class="panel-title">
				<?php echo __('Pending (%total)', array('%total' => $total)); ?>
				</h3>
			</div>
			<div class="col-md-6">
				<?php echo HTML::anchor("buddy/sent/".$id, __('Sent'), array('class' => 'buddy btn btn-default pull-right', 'title'=> __('View sent list'))) ?>
				<?php echo HTML::anchor("buddy/".$id, __('Friends'), array('class' => 'buddy btn btn-default pull-right', 'title'=> __('View Friends list'))) ?>
			</div>
			<div class="clearfix"></div>
		</div>
		
		<?php foreach($pendings as $pending): ?>
			<div class="list-group-item allusers panel-body col-md-12">
				<?php $accept = User::lookup($pending['request_from']); ?>
				<?php if($accept): ?>
					<div class="col-md-2">
					    <?php echo HTML::anchor("user/view/".$accept->id , User::getAvatar($accept), array('class' => 'action-view', 'size' => 80, 'title'=> __('view profile'))) ?>
					</div>
					<div class="col-md-5">
					    <?php echo HTML::anchor("user/view/".$accept->id , $accept->nick, array('class' => 'action-view', 'title'=> __('view profile'))) ?></br>
					    <?php echo HTML::anchor("#", $accept->mail, array('title'=> __('mail'))) ?></br>
						<?php echo ($accept->dob != 0)? $accept->dob : '__'; ?></br>
						<?php echo HTML::anchor("$accept->homepage", $accept->homepage) ?></br>
					</div>
					<?php if($is_owner): ?>
					<div class="col-md-4">
						<?php echo HTML::anchor("buddy/accept/".$accept->id , __('Accept'), array('class' => 'btn-sm btn-success')) ?>
						<?php echo HTML::anchor("buddy/reject/".$accept->id , __('Reject'), array('class' => 'btn-sm btn-danger')) ?>
					</div>
					<?php endif ;?>
				<?php endif; ?>

			</div>
		<?php endforeach ;?>
	    </div>
	    
	</div>
</div>
<?php echo $pagination; ?>