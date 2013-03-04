<?php defined('SYSPATH') or die('No direct script access.') ?>

<div class="help">
	<?php echo __('The tags module allows you to create free-tagging for everything, to define the various properties of your content, for example \'Countries\' or \'Colors\'.'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/tag')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add New Tag'), array('class' => 'btn btn-danger pull-right')) ?>

    <table id="tag-admin-list" class="table table-striped">
      <thead>
        <tr>
          <th><?php echo __('Name') ?></th>
          <th><?php echo __('Slug') ?></th>
	  <th><?php echo __('Type') ?></th>
          <th><?php echo __('Actions') ?></th>
        </tr>
      </thead>
         <?php foreach ($tags as $i => $tag): ?>
          <tr id="tag-row-<?php echo $tag->id ?>" class="<?php echo Text::alternate("odd", "even") ?>">
        
            <td id="tag-<?php echo $tag->id ?>">
              <?php echo $tag->name ?>
            </td>

            <td id="tag-slug-<?php echo $tag->id ?>">
              <?php echo HTML::anchor($tag->url, $tag->url) ?>
            </td>
	
            <td id="tag-type-<?php echo $tag->id ?>">
              <?php echo HTML::chars($tag->type) ?>
            </td>
        
            <td class="action">
                 <?php echo HTML::anchor(Route::get('admin/tag')->uri(array('action' => 'edit', 'id' => $tag->id)), __('Edit'), array('class'=>'action-edit', 'title'=> __('Edit Tag'))) ?>
                 <?php echo HTML::anchor(Route::get('admin/tag')->uri(array('action' => 'delete', 'id' => $tag->id)), __('Delete'), array('class'=>'action-delete', 'title'=> __('Delete Tag'))) ?>
		</td>
          </tr>
          <?php endforeach ?>
    </table>
    
  <?php echo $pagination ?>