<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Extend the Kohana Core
 *
 * @package    Gleez
 * @category   Core
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class Kohana extends Kohana_Core {}

if ( ! function_exists('__'))
{
	/**
	 * Translate strings to the page language or a given language.
	 *
	 * The PHP function [strtr](http://php.net/strtr) is used for
	 * replacing parameters.
	 *
	 * <code>
	 *  __('Welcome back, :user', array(':user' => $username));
	 * </code>
	 *
	 * The `$values` is an associative array of replacements to make after
	 * translation. Incidences of any key in this array are replaced with the
	 * corresponding value. Based on the first character of the key, the value is
	 * escaped and/or themed:
	 *  - !variable: inserted as is
	 *  - :variable: inserted as is
	 *  - $variable: uppercase the first character
	 *  - @variable: escape plain text to HTML (HTML::chars)
	 *  - %variable: escape text and theme as a placeholder for user-submitted
	 *               content (HTML::chars + theme_placeholder)
	 *
	 * [!!] The target language is defined by [I18n::$lang].
	 *
	 * @uses    I18n::get
	 * @uses    HTML::chars
	 *
	 * @param   string  $string Text to translate
	 * @param   array   $values Values to replace in the translated text [Optional]
	 * @param   string  $lang   Source language [Optional]
	 * @return  string  Translated string
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
						// Escaped only.
						$values[$key] = HTML::chars($value);
					break;

					case '%':
						// Escaped and placeholder.
						$values[$key] = '<em class="placeholder">' . HTML::chars($value) . '</em>';
					break;

					case '$':
						// Uppercase the first character
						$values[$key] = ucwords($value);
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