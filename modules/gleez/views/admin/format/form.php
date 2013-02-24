<?php defined('SYSPATH') or die('No direct script access.'); ?>

<div class="help">
        <?php echo __('A text format contains filters that change the user input, for example stripping out malicious HTML or making URLs clickable. Filters are executed from top to bottom and the order is important, since one filter may prevent another filter from doing its job. <em>For example, when URLs are converted into links before disallowed HTML tags are removed, all links may be removed. When this happens, the order of filters may need to be re-arranged.</em>') ?>
</div>
    
<?php echo Form::open(Route::get('admin/format')->uri(array('id' => $format['id'], 'action' => 'configure')), array('id'=>'filter-admin-format-form ', 'class'=>'format-form form')) ?>
    
<?php if ( ! empty($errors)): ?>
	<div id="formerrors" class="errorbox">
		<h3><?php echo __('Ooops!'); ?></h3>
		<ol>
			<?php foreach($errors as $field => $message): ?>
				<li> <?php echo $message ?> </li>
			<?php endforeach ?>
		</ol>
	</div>
<?php endif ?>

<div class="row">
   <div class="span4">
	<div class="control-group <?php echo isset($errors['name']) ? 'error': ''; ?>">
		<div class="controls">
			<?php echo Form::label('name', 'Title:', array('class' => 'control-label')) ?>
			<?php echo Form::input('name', $format['name'], array('class' => 'input-xlarge')); ?>
		</div>
	</div>

        <div class="control-group <?php echo isset($errors['roles']) ? 'error': ''; ?>">
                <?php echo Form::label('roles', 'Roles:', array('class' => 'control-label')) ?>
              
                <?php foreach($roles as $role => $name): ?>
                   <div class="form-wrap1">
                        <?php echo Form::label('roles', Form::checkbox('roles['.$role.']', $role, FALSE).ucfirst($name), array('class' => 'checkbox')) ?>
                        
                   </div>
                <?php endforeach ?>
        </div>
   </div>

   <div class="span5">
        <div class="control-group">
                <?php echo Form::label('order', 'Filters :', array('class' => 'control-label')) ?>
        
                <table id="filter-order" class="table table-striped table-bordered table-condensed">
                        <?php foreach($filters as $name => $filter): ?>
                        <tr id="filter-row-<?php echo $name ?>" class="draggable <?php echo Text::alternate("odd", "even") ?>">
                                <td ><?php echo HTML::chars($filter['title']) ?></td>
                                
                                <td class="tabledrag-hide" >
			                <?php echo Form::weight('filters['.$name.'][weight]', 0,
						array('class' => 'filter-order-weight')) ?>
			        </td>
                                
                                <td >
					<?php $n_status = (in_array($name, array_keys($enabled_filters)) ? TRUE : FALSE);
					 echo Form::checkbox('filters['.$name.'][status]', $n_status, $n_status); ?>
                                </td>
				<?php  echo Form::hidden('filters['.$name.'][name]', $name); ?>
                        </tr>
                        <?php endforeach ?>
                </table>
        </div>
   </div>

</div>
<div class="clearfix"></div>

<div id="settings-filter">
        <?php echo Form::label('settings', 'Filter Settings :', array('class' => 'control-label')) ?>
	
	<div class="tabbable tabs-left table-bordered">
		<ul class="nav nav-tabs">
			<?php foreach($filters as $name => $filter): ?>
				<?php if( !empty($filter['settings']) ):?><?php //echo Debug::vars($filter); ?>
					<li><a href="#<?php echo URL::title($filter['title'])?>" data-toggle="tab"><?php echo $filter['title']; ?></a></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
		
		<div class="tab-content">
			<?php foreach($filters as $name => $filter): ?>
				<?php if( !empty($filter['settings']) ):?>
					<div class="tab-pane"  id="<?php echo URL::title($filter['title'])?>">
						<?php foreach($filter['settings'] as $key => $value): ?>
						   <div class="control-group">
							<?php echo Form::label('edit-filters', str_replace('_', ' ', ucfirst($key)), array('class' => 'control-label')) ?>
							<?php echo Form::input('filters['.$name.'][settings]['.$key.']', $value, array('class' => 'span5')) ?>
							<div class="description"><?php //echo Text::plain($filter['description']) ?></div>
						   </div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<div class="clearfix"></div>
<?php echo Form::submit('filter', __('Save Filters'), array('class' => 'btn btn-primary btn-large')) ?>
<?php echo Form::close() ?>