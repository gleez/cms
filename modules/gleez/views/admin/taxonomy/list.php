<?php defined('SYSPATH') or die('No direct script access.') ?>

<div class="help">
	<?php echo __('Taxonomy is for categorizing content. Terms are grouped into vocabularies. For example, a vocabulary called "Fruit" would contain the terms "Apple" and "Banana".'); ?>
</div>

<?php echo HTML::anchor(Route::get('admin/taxonomy')->uri(array('action' =>'add')), '<i class="icon-plus icon-white"></i>'.__('Add New Vocabulary'), array('class' => 'btn btn-danger pull-right')) ?>
	<div class="clearfix"></div>
    <table id="taxonomy-admin-list" class="table table-striped">
      <thead>
        <tr>
          <th><?php echo __('Name') ?></th>
          <th colspan="4"><?php echo __('Actions') ?></th>
        </tr>
      </thead>
         <?php foreach ($terms as $i => $term): ?>
          <tr id="taxonomy-row-<?php echo $term->id ?>" class="<?php echo text::alternate('odd', 'even') ?>">
	  
            <td id="taxonomy-<?php echo $term->id ?>">
                <?php echo $term->name ?>
                
	      	<div class="description">
			<?php echo HTML::chars($term->description) ?>
	        </div>
            </td>

            <td class="action">
		<?php echo HTML::anchor(Route::get('admin/term')->uri(array('action' => 'list', 'id' => $term->id)), __('List Terms'), array('class'=>'action-list', 'title'=>__('List Terms'))) ?>
	    </td>
	    
	    <td class="action">
		<?php echo HTML::anchor(Route::get('admin/term')->uri(array('action' => 'add', 'id' => $term->id )), __('Add Term'), array('class'=>'action-add', 'title'=>__('Add Term'))) ?>
	    </td>
	    
	    <td class="action">
	       <?php echo HTML::anchor(Route::get('admin/taxonomy')->uri(array('action' => 'edit', 'id' => $term->id )), __('Edit'), array('class'=>'action-edit', 'title'=>__('Edit Vocab'))) ?>
	    </td>
	    
	    <td class="action">
	       <?php echo HTML::anchor(Route::get('admin/taxonomy')->uri(array('action' => 'delete', 'id' => $term->id )), __('Delete'), array('class'=>'action-delete', 'title'=>__('Delete Vocab'))) ?>
            </td>
	    
          </tr>
          <?php endforeach ?>
    </table>
    
  <?php echo $pagination ?>