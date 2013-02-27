<?php defined("SYSPATH") OR die("No direct script access.") ?>

<div class="help">
	<?php echo __('The Menu wizard provides an interface for managing menus. A menu is a hierarchical collection of links, which can be within or external to the site, generally used for navigation.', array(':menus' => 'admin/menu')); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/menu')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add Menu'), array('class' => 'btn btn-danger pull-right')) ?>
<div class='clearfix'></div><br/>

<table id="menu1-admin-list" class="table table-striped table-bordered">
      <thead>
        <tr>
          <th><?php echo __('Title') ?></th>
          <th colspan="4"><?php echo __('Actions') ?></th>
        </tr>
      </thead>
      
        <?php foreach ($menus as $i => $menu): ?>
        
          <tr id="menu1-row-<?php echo $menu->id ?>" >
          
            <td id="menu-<?php echo $menu->name ?>">
              <?php echo HTML::chars($menu->title) ?>
	      
	       <div class="description">
			<?php echo HTML::chars($menu->descp) ?>
	       </div>
            </td>
            
            <td class="action1">
		 <?php echo HTML::anchor(Route::get('admin/menu/item')->uri(array('id' => $menu->id)), '<i class="icon-th-list"></i>', array('class'=>'action-list', 'title'=>__('List Links'))) ?>
	    </td>
	    <td class="action">
		 <?php echo HTML::anchor(Route::get('admin/menu/item')->uri(array('action' => 'add', 'id' => $menu->id)), '<i class="icon-plus"></i>', array('class'=>'action-add', 'title'=>__('Add Link'))) ?>
	    </td>
	    <td class="action">
               <?php echo HTML::anchor(Route::get('admin/menu')->uri(array('action' => 'edit', 'id' => $menu->id)), '<i class="icon-edit"></i>', array('class'=>'action-edit', 'title'=>__('Edit Menu'))) ?>
	    </td>
	    <td class="action">
               <?php echo HTML::anchor(Route::get('admin/menu')->uri(array('action' => 'delete', 'id' => $menu->id)), '<i class="icon-trash"></i>', array('class'=>'action-delete', 'title'=>__('Delete Menu'))) ?>
            </td>
          </tr>
          <?php endforeach ?>
</table>
    
  <?php echo $pagination ?>