<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package    Gleez
 * @category   Core
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Kohana extends Kohana_Core {

	/**
	 * Provide a wrapper function for Kohana::find_file that checks themes
	 * folder for views, so that it can be included.
	 *
	 * // Returns an absolute path to views/template.php
	 *     Gleez::find_file('themes', 'template');
         *
	 * @param   string   directory name (themes, media, etc.)
	 * @param   string   filename with subdirectory
	 * @param   string   extension to search for
	 * @param   boolean  return an array of files?
	 * @return  array    a list of files when $array is TRUE
	 * @return  string   single file path
	 */
	public static function find_file_old_unused($dir, $file, $ext = NULL, $array = FALSE)
	{
		if($dir === 'views' OR $dir === 'media')
		{
			// Set the theme directory name in path
			$theme = Gleez::$theme;
			$old_paths = Kohana::include_paths();
			$paths = array();
			
			// Make theme relative to themes diretcory
			$path = realpath(THEMEPATH.$theme).DIRECTORY_SEPARATOR;
			if (is_dir($path) AND ( array_search($path, $old_paths, TRUE) === FALSE ) )
			{
				// Add the path to include paths
				$paths[] = $path;
				Kohana::$_paths = array_merge($paths, $old_paths);
			}
		}
        
                return parent::find_file($dir, $file, $ext, $array);
        }
        
}

if ( ! function_exists('__'))
{
	/**
	* Translate strings to the page language or a given language. The PHP function
	* [strtr](http://php.net/strtr) is used for replacing parameters.
	*
	*    __('Welcome back, :user', array(':user' => $username));
	*
	* [!!] The target language is defined by [I18n::$lang].
	*      
	* @uses    I18n::get
	* @param   string  text to translate
	* @param   array   values to replace in the translated text
	*                  An associative array of replacements to make after translation. Incidences
	*                  of any key in this array are replaced with the corresponding value. Based
	*                  on the first character of the key, the value is escaped and/or themed:
	*                  - !variable: inserted as is
	*                  - :variable: inserted as is
	*                  - @variable: escape plain text to HTML (HTML::entities)
	*                  - %variable: escape text and theme as a placeholder for user-submitted
	*                  content (HTML::entities + theme_placeholder)
	*      
	* @param   string  source language
	* @return  string
	*/
	function __($string, array $values = NULL, $lang = 'en-us')
	{
		if ($lang !== I18n::$lang)
		{
			// The message and target languages are different
			// Get the translation for this message
			$string = I18n::get($string);
		}

		if (empty($values))
		{
			return $string;
		}
		else
		{
			// Transform arguments before inserting them.
			foreach ($values as $key => $value)
			{
				switch ($key[0])
				{
					case '@':
						//case ':':
						// Escaped only.
						$values[$key] = HTML::chars($value);
					break;
                                
					case '%':
						// Escaped and placeholder.
						$values[$key] = '<em class="placeholder">' . HTML::chars($value) . '</em>';
					break;
                                
					case '!':
					case ':':
					default:
						// Pass-through.
				}
			}
		}
        
		return strtr($string, $values);
	}
}

function __n($count, $singular, $plural, array $values = array(), $lang = 'en-us')
{
	if ($lang !== I18n::$lang)
	{
		$string = $count === 1 ? I18n::get($singular) : I18n::get_plural($plural, $count);
	}
	else
		$string = $count === 1 ? $singular : $plural;
	
	return strtr($string, array_merge($values, array('%count' => $count)));
}