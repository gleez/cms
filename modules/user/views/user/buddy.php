<div id="content-container">
	<div class="row vcard">
	    <div class="list-group list-all panel panel-default">
		
		<div class="panel-heading"><h3 class="panel-title">Friends</h3></div>
		
		<?php foreach($friends as $id): ?>
			<div class="list-group-item allusers panel-body">
				<?php $accept = User::lookup($id); ?>
				<div class="col-md-5">
					<?php echo HTML::anchor("user/view/".$accept->id , User::getAvatar($accept), array('class' => 'action-view', 'size' => 80, 'title'=> __('view profile'))) ?>
				</div>
				<div class="col-md-6">
					<?php echo HTML::anchor("user/view/".$accept->id , $accept->nick, array('class' => 'action-view', 'title'=> __('view profile'))) ?></br>
					<?php echo HTML::anchor("#", $accept->mail, array('title'=> __('mail'))) ?></br>
					<?php echo ($accept->dob != 0)? $accept->dob : '__'; ?></br>
					<?php echo HTML::anchor("$accept->homepage", $accept->homepage) ?></br>
				</div>
				
				<?php if($is_owner): ?>
					<?php echo HTML::anchor("buddy/delete/".$accept->id , '<i class="fa fa-trash-o"></i>', array('class'=>'action-delete col-md-1', 'title'=> __('Delete'))); ?>
				<?php endif ;?>
			</div>
		<?php endforeach ;?>
	    </div>
	    
	</div>
</div>
<?php echo $pagination; ?>