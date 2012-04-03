<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Documentation generator.
 *
 * @package    Kohana/Userguide
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_Kodoc {

	/**
	 * @var string  PCRE fragment for matching 'Class', 'Class::method', 'Class::method()' or 'Class::$property'
	 */
	public static $regex_class_member = '((\w++)(?:::(\$?\w++))?(?:\(\))?)';

	/**
	 * Make a class#member API link using an array of matches from [Kodoc::$regex_class_member]
	 *
	 * @param   array   $matches    array( 1 => link text, 2 => class name, [3 => member name] )
	 * @return  string
	 */
	public static function link_class_member($matches)
	{
		$link = $matches[1];
		$class = $matches[2];
		$member = NULL;

		if (isset($matches[3]))
		{
			// If the first char is a $ it is a property, e.g. Kohana::$base_url
			if ($matches[3][0] === '$')
			{
				$member = '#property:'.substr($matches[3], 1);
			}
			else
			{
				$member = '#'.$matches[3];
			}
		}

		return HTML::anchor(Route::get('docs/api')->uri(array('class' => $class)).$member, $link, NULL, NULL, TRUE);
	}

	public static function factory($class)
	{
		return new Kodoc_Class($class);
	}

	/**
	 * Creates an html list of all classes sorted by category (or package if no category)
	 *
	 * @return   string   the html for the menu
	 */
	public static function menu()
	{
		$classes = Kodoc::classes();

		foreach ($classes as $class)
		{
			if (isset($classes['kohana_'.$class]))
			{
				// Remove extended classes
				unset($classes['kohana_'.$class]);
			}
		}

		ksort($classes);

		$menu = array();

		$route = Route::get('docs/api');

		foreach ($classes as $class)
		{
			$class = Kodoc_Class::factory($class);

			// Test if we should show this class
			if ( ! Kodoc::show_class($class))
				continue;

			$link = HTML::anchor($route->uri(array('class' => $class->class->name)), $class->class->name);

			if (isset($class->tags['package']))
			{
				foreach ($class->tags['package'] as $package)
				{
					if (isset($class->tags['category']))
					{
						foreach ($class->tags['category'] as $category)
						{
							$menu[$package][$category][] = $link;
						}
					}
					else
					{
						$menu[$package]['Base'][] = $link;
					}
				}
			}
			else
			{
				$menu['[Unknown]']['Base'][] = $link;
			}
		}

		// Sort the packages
		ksort($menu);

		return View::factory('userguide/api/menu')
			->bind('menu', $menu);
	}

	/**
	 * Returns an array of all the classes available, built by listing all files in the classes folder and then trying to create that class.
	 *
	 * This means any empty class files (as in complety empty) will cause an exception
	 *
	 * @param   array   array of files, obtained using Kohana::list_files
	 * @return  array   an array of all the class names
	 */
	public static function classes(array $list = NULL)
	{
		if ($list === NULL)
		{
			$list = Kohana::list_files('classes');
		}

		$classes = array();

		foreach ($list as $name => $path)
		{
			if (is_array($path))
			{
				$classes += Kodoc::classes($path);
			}
			else
			{
				// Remove "classes/" and the extension
				$class = substr($name, 8, -(strlen(EXT)));

				// Convert slashes to underscores
				$class = str_replace(DIRECTORY_SEPARATOR, '_', strtolower($class));

				$classes[$class] = $class;
			}
		}

		return $classes;
	}

	/**
	 * Get all classes and methods of files in a list.
	 *
	 * >  I personally don't like this as it was used on the index page.  Way too much stuff on one page.  It has potential for a package index page though.
	 * >  For example:  class_methods( Kohana::list_files('classes/sprig') ) could make a nice index page for the sprig package in the api browser
	 * >     ~bluehawk
	 *
	 */
	public static function class_methods(array $list = NULL)
	{
		$list = Kodoc::classes($list);

		$classes = array();

		foreach ($list as $class)
		{
			$_class = new ReflectionClass($class);

			if (stripos($_class->name, 'Kohana_') === 0)
			{
				// Skip transparent extension classes
				continue;
			}

			$methods = array();

			foreach ($_class->getMethods() as $_method)
			{
				$declares = $_method->getDeclaringClass()->name;

				if (stripos($declares, 'Kohana_') === 0)
				{
					// Remove "Kohana_"
					$declares = substr($declares, 7);
				}

				if ($declares === $_class->name OR $declares === "Core")
				{
					$methods[] = $_method->name;
				}
			}

			sort($methods);

			$classes[$_class->name] = $methods;
		}

		return $classes;
	}

	/**
	 * Parse a comment to extract the description and the tags
	 *
	 * @param   string  the comment retreived using ReflectionClass->getDocComment()
	 * @return  array   array(string $description, array $tags)
	 */
	public static function parse($comment)
	{
		// Normalize all new lines to \n
		$comment = str_replace(array("\r\n", "\n"), "\n", $comment);

		// Remove the phpdoc open/close tags and split
		$comment = array_slice(explode("\n", $comment), 1, -1);

		// Tag content
		$tags = array();

		foreach ($comment as $i => $line)
		{
			// Remove all leading whitespace
			$line = preg_replace('/^\s*\* ?/m', '', $line);

			// Search this line for a tag
			if (preg_match('/^@(\S+)(?:\s*(.+))?$/', $line, $matches))
			{
				// This is a tag line
				unset($comment[$i]);

				$name = $matches[1];
				$text = isset($matches[2]) ? $matches[2] : '';

				switch ($name)
				{
					case 'license':
						if (strpos($text, '://') !== FALSE)
						{
							// Convert the lincense into a link
							$text = HTML::anchor($text);
						}
					break;
					case 'link':
						$text = preg_split('/\s+/', $text, 2);
						$text = HTML::anchor($text[0], isset($text[1]) ? $text[1] : $text[0]);
					break;
					case 'copyright':
						if (strpos($text, '(c)') !== FALSE)
						{
							// Convert the copyright sign
							$text = str_replace('(c)', '&copy;', $text);
						}
					break;
					case 'throws':
						if (preg_match('/^(\w+)\W(.*)$/', $text, $matches))
						{
							$text = HTML::anchor(Route::get('docs/api')->uri(array('class' => $matches[1])), $matches[1]).' '.$matches[2];
						}
						else
						{
							$text = HTML::anchor(Route::get('docs/api')->uri(array('class' => $text)), $text);
						}
					break;
					case 'uses':
						if (preg_match('/^'.Kodoc::$regex_class_member.'$/i', $text, $matches))
						{
							$text = Kodoc::link_class_member($matches);
						}
					break;
					// Don't show @access lines, they are shown elsewhere
					case 'access':
						continue 2;
				}

				// Add the tag
				$tags[$name][] = $text;
			}
			else
			{
				// Overwrite the comment line
				$comment[$i] = (string) $line;
			}
		}

		// Concat the comment lines back to a block of text
		if ($comment = trim(implode("\n", $comment)))
		{
			// Parse the comment with Markdown
			$comment = Markdown($comment);
		}

		return array($comment, $tags);
	}

	/**
	 * Get the source of a function
	 *
	 * @param  string   the filename
	 * @param  int      start line?
	 * @param  int      end line?
	 */
	public static function source($file, $start, $end)
	{
		if ( ! $file) return FALSE;

		$file = file($file, FILE_IGNORE_NEW_LINES);

		$file = array_slice($file, $start - 1, $end - $start + 1);

		if (preg_match('/^(\s+)/', $file[0], $matches))
		{
			$padding = strlen($matches[1]);

			foreach ($file as & $line)
			{
				$line = substr($line, $padding);
			}
		}

		return implode("\n", $file);
	}

	/**
	 * Test whether a class should be shown, based on the api_packages config option
	 *
	 * @param  Kodoc_Class  the class to test
	 * @return  bool  whether this class should be shown
	 */
	public static function show_class(Kodoc_Class $class)
	{
		$api_packages = Kohana::$config->load('userguide.api_packages');

		// If api_packages is true, all packages should be shown
		if ($api_packages === TRUE)
			return TRUE;

		// Get the package tags for this class (as an array)
		$packages = Arr::get($class->tags, 'package', array('None'));

		$show_this = FALSE;

		// Loop through each package tag
		foreach ($packages as $package)
		{
			// If this package is in the allowed packages, set show this to true
			if (in_array($package, explode(',', $api_packages)))
				$show_this = TRUE;
		}

		return $show_this;
	}


} // End Kodoc
