<?php defined("SYSPATH") or die("No direct script access.") ?>
<style type="text/css">
	#tests table th,
	#tests table td { padding: 0.2em 0.4em; text-align: left; vertical-align: top; }
	#tests table td.pass { color: #191; }
	#tests table td.fail { color: #911; }
	#tests #results { color: #fff; }
	#tests #results p { padding: 0.8em 0.4em; }
	#tests #results p.pass { background: #191; }
	#tests #results p.fail { background: #911; }
</style>

<div id="tests">
	<table class="table table-bordered table-striped">
		<tr>
			<th>PHP Version</th>
			<?php if ($php_version): ?>
			<td class="pass"><?php echo PHP_VERSION ?></td>
			<?php else: ?>
			<td class="fail">Gleez CMS requires PHP 5.2.3 or newer, this version is <?php echo PHP_VERSION ?>.</td>
			<?php endif ?>
		</tr>

		<tr>
			<th>Php Mysql</th>
			<?php if ($mysql): ?>
			<td class="pass"><?php echo __('Pass') ?></td>
			<?php else: ?>
			<td class="fail">Gleez CMS requires  a MySQL database, but PHP doesn't have either the
			<a href="http://php.net/mysql">MySQL</a> or the  <a href="http://php.net/mysqli">MySQLi</a> extension.</td>
			<?php endif ?>
		</tr>

		<tr>
			<th>System Directory</th>
			<?php if ($system_directory): ?>
			<td class="pass"><code><?php echo SYSPATH ?></code></td>
			<?php else: ?>
			<td class="fail">The configured <code>system</code> directory does not exist or does not contain required files.</td>
			<?php endif ?>
		</tr>
	
		<tr>
			<th>Application Directory</th>
			<?php if ($application_directory): ?>
			<td class="pass"><code><?php echo APPPATH ?></code></td>
			<?php else: ?>
			<td class="fail">The configured <code>application</code> directory does not exist or does not contain required files.</td>
			<?php endif ?>
		</tr>
	
		<tr>
			<th>Modules Directory</th>
			<?php if ($modules_directory): ?>
			<td class="pass"><code><?php echo MODPATH ?></code></td>
			<?php else: ?>
			<td class="fail">The configured <code>modules</code> directory does not exist or does not contain required files.</td>
			<?php endif ?>
		</tr>
		
		<tr>
			<th>Config Directory</th>
			<?php if ($config_writable): ?>
			<td class="pass"><code><?php echo str_replace('\\', '/', realpath(APPPATH.'config')).'/' ?></code> is writable</td>
			<?php else: ?>
			<td class="fail">The directory <code><?php echo str_replace('\\', '/', realpath(APPPATH.'config')).'/' ?>
			</code> does not exist or is not writable. We're having trouble creating a place for your cms.  Can you
			help?  Please create a directory called "config" using <code>mkdir 'application/config'</code> in your gleez
			directory, then run <code>chmod 777 application/config</code>.  That should fix it.</td>
			<?php endif ?>
		</tr>
		
		<tr>
			<th>Cache Directory</th>
			<?php if ($cache_writable): ?>
			<td class="pass"><code><?php echo str_replace('\\', '/', realpath(APPPATH.'cache')).'/' ?></code> is writable</td>
			<?php else: ?>
			<td class="fail">The <code><?php echo str_replace('\\', '/', realpath(APPPATH.'cache')).'/' ?></code> directory is not writable.</td>
			<?php endif ?>
		</tr>
	
		<tr>
			<th>PCRE UTF-8</th>
			<?php if ( ! $pcre_utf8): ?>
			<td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.</td>
			<?php elseif ( ! $pcre_unicode ): ?>
			<td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with Unicode property support.</td>
			<?php else: ?>
			<td class="pass">Pass</td>
			<?php endif ?>
		</tr>
		
		<tr>
			<th>Reflection Enabled</th>
			<?php if ($reflection_enabled): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">PHP <a href="http://www.php.net/reflection">reflection</a> is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>

		<tr>
			<th>Standard PHP Library (SPL)</th>
			<?php if ($spl_autoload_register): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">The <a href="http://php.net/spl">Standard PHP Library (SPL)</a> extension is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>

		<tr>
			<th>Filters Enabled</th>
			<?php if ($filters_enabled): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">The <a href="http://www.php.net/filter">filter</a> extension is either not loaded or not compiled in.</td>
			<?php endif ?>
		</tr>
	
		<tr>
			<th>Iconv Extension Loaded</th>
			<?php if ($iconv_loaded): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">The <a href="http://php.net/iconv">iconv</a> extension is not loaded.</td>
			<?php endif ?>
		</tr>

		<tr>
			<th>SimpleXML Extension Loaded</th>
			<?php if ($simplexml): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">The <a href="http://php.net/simplexml">SimpleXML</a> extension is not loaded.</td>
			<?php endif ?>
		</tr>

		<tr>
			<th>JavaScript Object Notation (JSON)</th>
			<?php if ($json_encode): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">The <a href="http://php.net/manual/en/book.json.php">JavaScript
			Object Notation (JSON)</a> extension is not loaded.</td>
			<?php endif ?>
		</tr>

		<tr>
			<th>Mbstring Not Overloaded</th>
			<?php if ($mbstring): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">The <a href="http://php.net/mbstring">mbstring</a> extension is
			overloading PHP's native string functions.</td>
			<?php endif ?>
		</tr>

		<tr>
			<th>Character Type (CTYPE) Extension</th>
			<?php if ($ctype_digit): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">The <a href="http://php.net/ctype">ctype</a> extension is not enabled.</td>
			<?php endif ?>
		</tr>
	
		<tr>
			<th>URI Determination</th>
			<?php if ($uri_determination): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">Neither <code>$_SERVER['REQUEST_URI']</code>, <code>$_SERVER['PHP_SELF']</code>,
			or <code>$_SERVER['PATH_INFO']</code> is available</td>
			<?php endif ?>
		</tr>

		<tr>
			<th>GD Enabled</th>
			<?php if ($gd_info): ?>
			<td class="pass">Pass</td>
			<?php else: ?>
			<td class="fail">Gleez requires <a href="http://php.net/gd">GD</a> v2 for the Image class.</td>
			<?php endif ?>
		</tr>
	
	</table>
</div>