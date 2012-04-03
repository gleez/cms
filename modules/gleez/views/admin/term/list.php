<?php defined('SYSPATH') or die('No direct script access.') ?>

<?php echo HTML::anchor(Route::get('admin/term')->uri(array('action' => 'add', 'id' => $id)), '<i class="icon-plus icon-white"></i>'.__('Add New Term'), array('title'=>__('Add New Term'),'class' => 'btn btn-danger pull-right'));

    echo Form::open(Route::get('admin/term')->uri(array('action' => 'confirm', 'id' => $id)), array('id'=>'menu-form', 'class'=>'form')); ?>
    <div class="clearfix"></div>

    <table id="term-admin-list" class="table table-striped">
	
	<thead>
	    <tr>
		<th><?php echo __('Name') ?></th>
		<th class="tabledrag-hide"><?php echo __('Weight') ?></th>
		<th><?php echo __('Actions') ?></th>
	    </tr>
	</thead>
      
	<?php foreach ($terms as $item): ?>
	    
	  <tr id="term-row-<?php echo $item['id'] ?>" class="draggable <?php echo Text::alternate("odd", "even") ?>">
	  
	    <td id="term-<?php echo $item['id'] ?>">
              <?php
                $c = 2;
		while ($c < $item['lvl'])
		{
		  echo '<div class="indentation">&nbsp;</div>';
		  $c++;
		}
		echo HTML::chars($item['name'])
              ?>
	    </td>

	    <td class="tabledrag-hide">
	      <?php echo Form::weight('tid:'.$item['id'].'[weight]', 0, array('class' => 'term-weight')) ?>
	      <?php echo Form::hidden('tid:'.$item['id'].'[pid]', $item['pid'], array('class' => 'term-parent')) ?>
	      <?php echo Form::hidden('tid:'.$item['id'].'[tid]', $item['id'], array('class' => 'term-id')) ?>
	      <?php echo Form::hidden('tid:'.$item['id'].'[depth]', $item['lvl'], array('class' => 'term-depth')) ?>
            </td>
	    
            <td class="action">
               <?php echo HTML::anchor(Route::get('admin/term')->uri(array('action' => 'edit', 'id' => $item['id'])),
                                       __('Edit'), array('class' => 'action-edit', 'title' => __('Edit Term'))) ?>
               <?php echo HTML::anchor(Route::get('admin/term')->uri(array('action' => 'delete', 'id' => $item['id'])),
                                       __('Delete'), array('class' => 'action-delete', 'title' => __('Delete Term'))) ?>
            </td>
	    
          </tr>
	  
        <?php endforeach ?>
	
    </table>
    
    <?php echo Form::submit('term-list', 'Submit', array('class'=>'btn btn-primary btn-large')); ?>
    <?php echo Form::close(); ?>