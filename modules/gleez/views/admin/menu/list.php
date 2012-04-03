<?php defined("SYSPATH") or die("No direct script access.") ?>

<div class="help">
	<?php echo __('The Menu wizard provides an interface for managing menus. A menu is a hierarchical collection of links, which can be within or external to the site, generally used for navigation.', array(':menus' => 'admin/menu')); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/menu')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add Menu'), array('class' => 'btn btn-danger pull-right')) ?>


<table id="menu-admin-list" class="table table-striped table-bordered">
      <thead>
        <tr>
          <th><?php echo __('Title') ?></th>
          <th colspan="4"><?php echo __('Actions') ?></th>
        </tr>
      </thead>
      
        <?php foreach ($menus as $i => $menu): ?>
        
          <tr id="menu-row-<?php echo $menu->id ?>" class="<?php echo Text::alternate("odd", "even") ?>">
          
            <td id="menu-<?php echo $menu->name ?>">
              <?php echo HTML::chars($menu->title) ?>
	      
	       <div class="description">
			<?php echo HTML::chars($menu->descp) ?>
	       </div>
            </td>
            
            <td class="action">
		 <?php echo Html::anchor(Route::get('admin/menu/item')->uri(array('id' => $menu->id)), __("List Links"), array('class'=>'action-list', 'title'=>__('List Links'))) ?>
	    </td>
	    <td class="action">
		 <?php echo Html::anchor(Route::get('admin/menu/item')->uri(array('action' => 'add', 'id' => $menu->id)), __("Add Link"), array('class'=>'action-add', 'title'=>__('Add Link'))) ?>
	    </td>
	    <td class="action">
               <?php echo Html::anchor(Route::get('admin/menu')->uri(array('action' => 'edit', 'id' => $menu->id)), __("Edit"), array('class'=>'action-edit', 'title'=>__('Edit Menu'))) ?>
	    </td>
	    <td class="action">
               <?php echo Html::anchor(Route::get('admin/menu')->uri(array('action' => 'delete', 'id' => $menu->id)), __("Delete"), array('class'=>'action-delete', 'title'=>__('Delete Menu'))) ?>
            </td>
          </tr>
          <?php endforeach ?>
</table>
    
  <?php echo $pagination ?>