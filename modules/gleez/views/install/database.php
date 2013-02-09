<?php defined("SYSPATH") or die("No direct script access.") ?>

<?php echo Form::open(Route::get('install')->uri(array('action' => 'database')), array('class' => 'form form-horizontal')); ?>
  <div class="box">
    <div class="inside">
      <div class="control-group <?php echo isset($errors['database']) ? 'error': ''; ?>">
        <?php echo Form::label('database', __('Database Name'), array('class' => 'control-label')) ?>
        <div class="controls">
          <?php echo Form::input('database', $form['database'], array('class' => 'input-large')); ?>
        </div>
      </div>

      <div class="control-group <?php echo isset($errors['user']) ? 'error': ''; ?>">
        <?php echo Form::label('user', __('User'), array('class' => 'control-label')) ?>
        <div class="controls">
          <?php echo Form::input('user', $form['user'], array('class' => 'input-large')); ?>
        </div>
      </div>

      <div class="control-group <?php echo isset($errors['pass']) ? 'error': ''; ?>">
        <?php echo Form::label('pass', __('Password'), array('class' => 'control-label')) ?>
        <div class="controls">
          <?php echo Form::password('pass', $form['pass'], array('class' => 'input-large')); ?>
        </div>
      </div>

      <div class="control-group <?php echo isset($errors['hostname']) ? 'error': ''; ?>">
        <?php echo Form::label('hostname', __('Host'), array('class' => 'control-label')) ?>
        <div class="controls">
          <?php echo Form::input('hostname', $form['hostname'], array('class' => 'input-large')); ?>
        </div>
      </div>

      <div class="control-group <?php echo isset($errors['table_prefix']) ? 'error': ''; ?>">
        <?php echo Form::label('table_prefix', __('Table Prefix'), array('class' => 'control-label')) ?>
        <div class="controls">
          <?php echo Form::input('table_prefix', $form['table_prefix'], array('class' => 'input-large')); ?>
        </div>
      </div>
    </div>
  </div>

  <div align="center">
    <?php echo Form::submit('db', __('Next'), array('class' => 'btn btn-primary')) ?>
  </div>
<?php echo Form::close() ?>