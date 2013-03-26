<?php defined('SYSPATH') or die('No direct script access.') ?>

<div class="help">
	<?php echo __('An alias defines a different name for an existing URL path - for example, the alias \'about\' for the URL path \'page/1\'. A URL path can have multiple aliases.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/path')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add Alias'), array('class' => 'btn btn-danger pull-right')) ?>

    <table id="path-admin-list" class="table table-striped">
      <thead>
        <tr>
          <th><?php echo __('Path') ?></th>
          <th><?php echo __('Alias') ?></th>
          <th><?php echo __('Actions') ?></th>
        </tr>
      </thead>
         <?php foreach ($paths as $i => $path): ?>
          <tr id="path-row-<?php echo $path->id ?>" class="<?php echo text::alternate("odd", "even") ?>">
        
            <td id="path-<?php echo $path->id ?>">
              <?php echo Text::plain($path->source) ?>
            </td>
        
            <td id="path-alias-<?php echo $path->id ?>">
              <?php echo Text::plain($path->alias) ?>
            </td>
        
            <td class="action">
                 <?php echo HTML::anchor(Route::get('admin/path')->uri(array('action' => 'edit', 'id' => $path->id)), '<i class="icon-edit"></i>', array('class'=>'action-edit', 'title'=> __('Edit Alias'))) ?>
                 <?php echo HTML::anchor(Route::get('admin/path')->uri(array('action' => 'delete', 'id' => $path->id)), '<i class="icon-trash"></i>', array('class'=>'action-delete', 'title'=> __('Delete Alias'))) ?>
		</td>
          </tr>
          <?php endforeach ?>
    </table>
    
  <?php echo $pagination ?>