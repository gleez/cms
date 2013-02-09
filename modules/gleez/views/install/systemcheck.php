<?php defined("SYSPATH") OR die("No direct script access.") ?>

<div id="tests">
	<table class="table table-bordered table-striped">
		<tr>
			<th><?php echo __('PHP Version'); ?></th>
			<?php if ($php_version): ?>
			  <td class="pass"><?php echo PHP_VERSION ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('Gleez CMS requires PHP 5.3 or newer, this version is :php_version.', array(':php_version' => PHP_VERSION) ); ?>.</td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('PHP MySQL'); ?></th>
			<?php if ($mysql): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail">
          <?php echo __('Gleez CMS requires a MySQL database, but PHP doesn\'t have either the :mysql or the :mysqli extension.', array(':mysql' => HTML::anchor('http://php.net/mysql', 'MySQL'), ':mysqli' => HTML::anchor('http://php.net/mysqli', 'MySQLi'))); ?>
        </td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('System Directory'); ?></th>
			<?php if ($system_directory): ?>
			  <td class="pass"><code><?php echo SYSPATH ?></code></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The configured :system directory does not exist or does not contain required files.', array(':system' => '<code>system</code>')); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Application Directory'); ?></th>
			<?php if ($application_directory): ?>
			  <td class="pass"><code><?php echo APPPATH ?></code></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The configured :application directory does not exist or does not contain required files.', array(':application' => '<code>application</code>')); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Modules Directory'); ?></th>
			<?php if ($modules_directory): ?>
			  <td class="pass"><code><?php echo MODPATH ?></code></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The configured :modules directory does not exist or does not contain required files.', array(':modules' => '<code>modules</code>')); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Config Directory'); ?></th>
			<?php if ($config_writable): ?>
			  <td class="pass"><?php echo __('The :config_dir directory is writable.', array(':config_dir' => '<code>'. str_replace('\\', '/', realpath(APPPATH.'config')).'/</code>'))?></td>
			<?php else: ?>
        <td class="fail">
          <?php echo __('The directory :config_dir does not exist or is not writable. We\'re having trouble creating a place for your CMS. Can you help? Please create a directory called "config" using :mkdir in your gleez directory, then run :chmod. That should fix it.', array(':config_dir' => '<code>'.str_replace('\\', '/', realpath(APPPATH.'config')).'/</code>', ':mkdir' => '<code>mkdir application/config</code>', ':chmod' => '<code>chmod a+rwx application/config</code>')) ?>
        </td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Cache Directory'); ?></th>
			<?php if ($cache_writable): ?>
        <td class="pass"><?php echo __('The :cache_dir directory is writable.', array(':cache_dir' => '<code>'. str_replace('\\', '/', realpath(APPPATH.'cache')).'/</code>'))?></td>
			<?php else: ?>
			  <td class="fail">
          <?php echo __('The :cache_dir directory is not writable.', array(':cache_dir' => '<code>'.str_replace('\\', '/', realpath(APPPATH.'cache')).'/</code>')) ?>
        </td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('PCRE UTF-8'); ?></th>
			<?php if ( ! $pcre_utf8): ?>
			  <td class="fail"><?php echo __(':pcre has not been compiled with UTF-8 support.', array(':pcre' => '<a href="http://php.net/pcre">PCRE</a>')) ?></td>
			<?php elseif ( ! $pcre_unicode ): ?>
			  <td class="fail"><?php echo __(':pcre has not been compiled with Unicode property support.', array(':pcre' => HTML::anchor('http://php.net/pcre', 'PCRE') )); ?></td>
			<?php else: ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Reflection API'); ?></th>
			<?php if ($reflection_enabled): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('PHP :reflection is either not loaded or not compiled in.', array(':reflection' => HTML::anchor('http://www.php.net/reflection', 'Reflection') )); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Standard PHP Library (SPL)'); ?></th>
			<?php if ($spl_autoload_register): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The :spl extension is either not loaded or not compiled in.', array(':spl' => HTML::anchor('http://php.net/spl', 'Standard PHP Library (SPL)') )); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Filters Enabled'); ?></th>
			<?php if ($filters_enabled): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The :filer extension is either not loaded or not compiled in.', array(':filter' => HTML::anchor('http://www.php.net/filter', 'filter') )); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Iconv Extension'); ?></th>
			<?php if ($iconv_loaded): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The :iconv extension is not loaded.', array(':iconv' => HTML::anchor('http://php.net/iconv', 'iconv') )); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('SimpleXML Extension'); ?></th>
			<?php if ($simplexml): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The :simlexml extension is not loaded.', array(':simplexml' => HTML::anchor('http://php.net/simplexml', 'SimpleXML') )); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('JavaScript Object Notation (JSON)'); ?></th>
			<?php if ($json_encode): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The :json extension is not loaded.', array(':json' => HTML::anchor('http://php.net/manual/en/book.json.php', 'JavaScript Object Notation (JSON)') )); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Mbstring Not Overloaded'); ?></th>
			<?php if ($mbstring): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The :mbstring extension is overloading PHP\'s native string functions.', array(':mbstring' => HTML::anchor('http://php.net/mbstring', 'mbstring') )); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('Character Type (CTYPE)'); ?></th>
			<?php if ($ctype_digit): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('The :ctype extension is not enabled.', array(':ctype' => HTML::anchor('http://php.net/ctype', 'ctype') )); ?></td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('URI Determination'); ?></th>
			<?php if ($uri_determination): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail">
          <?php echo __('Neither :request_uri, :php_self, or :path_info is available', array(':request_uri' => '<code>$_SERVER[\'REQUEST_URI\']</code>', ':php_self' => '<code>$_SERVER[\'PHP_SELF\']</code>', ':path_info' => '<code>$_SERVER[\'PATH_INFO\']</code>')); ?>
        </td>
			<?php endif ?>
		</tr>

		<tr>
			<th><?php echo __('GD Enabled'); ?></th>
			<?php if ($gd_info): ?>
			  <td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			  <td class="fail"><?php echo __('Gleez requires :gd v2 for the Image class.', array(':gd' => HTML::anchor('http://php.net/gd', 'GD') )); ?></td>
			<?php endif ?>
		</tr>

	</table>
</div>