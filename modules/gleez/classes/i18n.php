<?php
/**
 * Internationalization (i18n) class with plural support to I18n
 *
 * Provides language loading and translation methods without dependencies on [gettext](http://php.net/gettext).
 *
 * Typically this class would never be used directly, but used via the __()
 * function, which loads the message and replaces parameters:
 *
 *     // Display a translated message
 *     echo __('Hello, world');
 *     _e('Hello, world');
 *
 *     // With parameter replacement
 *     echo __('Hello, :user', array(':user' => $username));
 *     _e('Hello, :user', array(':user' => $username));
 *
 * @package    Gleez\Internationalization
 * @author     Kohana Team
 * @author     Sandeep Sangamreddi - Gleez
 * @author     Sergey Yakovlev - Gleez
 * @version    1.0.1
 * @copyright  (c) 2008-2012 Kohana Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://kohanaframework.org/license
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class I18n {
	/**
	 * @var  string   target language: en-us, es-es, zh-cn, etc
	 */
	public static $lang = 'en-us';

	/**
	 * @var  string  source language: en-us, es-es, zh-cn, etc
	 */
	public static $source = 'en-us';

	/**
	 * @var  array  cache of loaded languages
	 */
	protected static $_cache = array();

	public static function initialize()
	{
		//Installed Locales
		$installed_locales = Config::get('site.installed_locales', array());
		$default_locale    = Config::get('site.locale', 'en_US');
		
		//allow the user or browser to override the default locale
		$locale_override   = Config::get('site.locale_override', FALSE);

		// 1. Check the session specific preference (cookie)
		$locale = I18n::cookie_locale($installed_locales);
	
		// 2. Check the user's preference
		if(!$locale AND ($locale_override == 'ALL' OR $locale_override == 'USER'))
		{
			$locale   = I18n::user_locale($installed_locales);
		}
	
		// 3. Check the request client/browser's preference
		if(!$locale AND ($locale_override == 'ALL' OR $locale_override == 'CLIENT'))
		{
			$locale = I18n::request_locale($installed_locales);
		}
	
		// 4. Default locale preference
		if(!$locale)
		{
			$locale = $default_locale;
		}
	
		//set the locale
		I18n::lang($locale);
		setlocale(LC_ALL, $locale.'.utf-8');
	}

	public static function request_locale( array $installed_locales )
	{
		$requested_locales	= Request::accept_lang();
		//@todo score comparison
		foreach($requested_locales as $locale => $score)
		{
			if (isset($installed_locales[$locale])) 
			{
				return $locale;
			}
		}
		
		return FALSE;
	}

	public static function user_locale( array $installed_locales )
	{
		//Can't set guest users locale, default's to site locale
		if(User::is_guest()) 
		{
			$locale = Config::get('site.locale', 'en_US');
		}
		else
		{
			$locale	= User::active_user()->language;
		}
		
		if (isset($installed_locales[$locale])) 
		{
			return $locale;
		}
		
		return FALSE;
	}
	
	public static function cookie_locale( array $installed_locales )
	{
		$cookie_data = Cookie::get('user_language');
		
		//double check cookie data
		if ($cookie_data AND preg_match("/^([a-z]{2,3}(?:_[A-Z]{2})?)$/", trim($cookie_data), $matches))
		{
			$requested_locale = $matches[1];
			if (isset($installed_locales[$requested_locale])) 
			{
				return $requested_locale;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Get and set the target language.
	 *
	 *     // Get the current language
	 *     $lang = I18n::lang();
	 *
	 *     // Change the current language to Spanish
	 *     I18n::lang('es-es');
	 *
	 * @param   string  $lang   new language setting
	 * @return  string
	 * @since   3.0.2
	 */
	public static function lang($lang = NULL)
	{
		if ($lang)
		{
			// Normalize the language
			I18n::$lang = strtolower(str_replace(array(' ', '_'), '-', $lang));
		}

		return I18n::$lang;
	}

	/**
	 * Returns translation of a string. If no translation exists, the original
	 * string will be returned. No parameters are replaced.
	 *
	 *     $hello = I18n::get('Hello friends, my name is :name');
	 *
	 * @param   string  $string text to translate
	 * @param   string  $lang   target language
	 * @return  string
	 */
	public static function get($string, $lang = NULL)
	{
		if ( ! $lang)
		{
			// Use the global target language
			$lang = I18n::$lang;
		}

		// Load the translation table for this language
		$table = I18n::load($lang);

		// Return the translated string if it exists
		return isset($table[$string]) ? $table[$string] : $string;
	}

	/**
	 * Returns the translation table for a given language.
	 *
	 *     // Get all defined Spanish messages
	 *     $messages = I18n::load('es-es');
	 *
	 * @param   string  $lang   language to load
	 * @return  array
	 */
	public static function load($lang)
	{
		if (isset(self::$_cache[$lang]))
		{
			return self::$_cache[$lang];
		}

		// New translation table
		$table = array();

		// Split the language: language, region, locale, etc
		$parts = explode('-', $lang);

		do
		{
			// Create a path for this set of parts
			$path = implode(DS, $parts);

			if ($files = Kohana::find_file('i18n', $path, NULL, TRUE))
			{
				$t = array();
				foreach ($files as $file)
				{
					// Merge the language strings into the sub table
					$t = array_merge($t, Kohana::load($file));
				}

				// Append the sub table, preventing less specific language
				// files from overloading more specific files
				$table += $t;
			}

			// Remove the last part
			array_pop($parts);
		}
		while ($parts);

		// Cache the translation table locally
		return self::$_cache[$lang] = $table;
	}

	/**
	 * This method is borrowed from the s7ncms code:
	 */
	public static function get_plural($string, $count)
	{
		// Load the translation table
		$table = I18n::load(I18n::$lang);

		$key = I18n::get_plural_key(I18n::$lang, $count);

		// Return the translated string if it exists
		return isset($table[$string][$key]) ? $table[$string][$key]: $table[$string];
	}

	/**
	 * This method is borrowed from the Gallery3 code:
	 * http://apps.sourceforge.net/trac/gallery/browser/gallery3/trunk/core/libraries/I18n.php?rev=20787#L217
	 *
	 * Gallery - a web based photo album viewer and editor
	 * Copyright (C) 2000-2009 Bharat Mediratta
	 *
	 * This program is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or (at
	 * your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful, but
	 * WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	 * General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with this program; if not, write to the Free Software
	 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
	 */
	private static function get_plural_key($lang, $count)
	{

		// Data from CLDR 1.6 (http://unicode.org/cldr/data/common/supplemental/plurals.xml).
		// Docs: http://www.unicode.org/cldr/data/charts/supplemental/language_plural_rules.html
		switch ($lang) {
			case 'az':
			case 'fa':
			case 'hu':
			case 'ja':
			case 'ko':
			case 'my':
			case 'to':
			case 'tr':
			case 'vi':
			case 'yo':
			case 'zh':
			case 'bo':
			case 'dz':
			case 'id':
			case 'jv':
			case 'ka':
			case 'km':
			case 'kn':
			case 'ms':
			case 'th':
				return 'other';

			case 'ar':
				if ($count == 0) {
					return 'zero';
				} else if ($count == 1) {
					return 'one';
				} else if ($count == 2) {
					return 'two';
				} else if (is_int($count) AND ($i = $count % 100) >= 3 AND $i <= 10) {
					return 'few';
				} else if (is_int($count) AND ($i = $count % 100) >= 11 AND $i <= 99) {
					return 'many';
				} else {
					return 'other';
				}

			case 'pt':
			case 'am':
			case 'bh':
			case 'fil':
			case 'tl':
			case 'guw':
			case 'hi':
			case 'ln':
			case 'mg':
			case 'nso':
			case 'ti':
			case 'wa':
				if ($count == 0 OR $count == 1) {
					return 'one';
				} else {
					return 'other';
				}

			case 'fr':
				if ($count >= 0 and $count < 2) {
					return 'one';
				} else {
					return 'other';
				}

			case 'lv':
				if ($count == 0) {
					return 'zero';
				} else if ($count % 10 == 1 AND $count % 100 != 11) {
					return 'one';
				} else {
					return 'other';
				}

			case 'ga':
			case 'se':
			case 'sma':
			case 'smi':
			case 'smj':
			case 'smn':
			case 'sms':
				if ($count == 1) {
					return 'one';
				} else if ($count == 2) {
					return 'two';
				} else {
					return 'other';
				}

			case 'ro':
			case 'mo':
				if ($count == 1) {
					return 'one';
				} else if (is_int($count) AND $count == 0 AND ($i = $count % 100) >= 1 AND $i <= 19) {
					return 'few';
				} else {
					return 'other';
				}

			case 'lt':
				if (is_int($count) AND $count % 10 == 1 AND $count % 100 != 11) {
					return 'one';
				} else if (is_int($count) AND ($i = $count % 10) >= 2 AND $i <= 9 AND ($i = $count % 100) < 11 AND $i > 19) {
					return 'few';
				} else {
					return 'other';
				}

			case 'hr':
			case 'ru':
			case 'sr':
			case 'uk':
			case 'be':
			case 'bs':
			case 'sh':
				if (is_int($count) AND $count % 10 == 1 AND $count % 100 != 11) {
					return 'one';
				} else if (is_int($count) AND ($i = $count % 10) >= 2 AND $i <= 4 AND ($i = $count % 100) < 12 AND $i > 14) {
					return 'few';
				} else if (is_int($count) AND ($count % 10 == 0 OR (($i = $count % 10) >= 5 AND $i <= 9) OR (($i = $count % 100) >= 11 AND $i <= 14))) {
					return 'many';
				} else {
					return 'other';
				}

			case 'cs':
			case 'sk':
				if ($count == 1) {
					return 'one';
				} else if (is_int($count) AND $count >= 2 AND $count <= 4) {
					return 'few';
				} else {
					return 'other';
				}

			case 'pl':
				if ($count == 1) {
					return 'one';
				} else if (is_int($count) AND ($i = $count % 10) >= 2 AND $i <= 4 &&
					($i = $count % 100) < 12 AND $i > 14 AND ($i = $count % 100) < 22 AND $i > 24) {
					return 'few';
				} else {
					return 'other';
				}

			case 'sl':
				if ($count % 100 == 1) {
					return 'one';
				} else if ($count % 100 == 2) {
					return 'two';
				} else if (is_int($count) AND ($i = $count % 100) >= 3 AND $i <= 4) {
					return 'few';
				} else {
					return 'other';
				}

			case 'mt':
				if ($count == 1) {
					return 'one';
				} else if ($count == 0 OR is_int($count) AND ($i = $count % 100) >= 2 AND $i <= 10) {
					return 'few';
				} else if (is_int($count) AND ($i = $count % 100) >= 11 AND $i <= 19) {
					return 'many';
				} else {
					return 'other';
				}

			case 'mk':
				if ($count % 10 == 1) {
					return 'one';
				} else {
					return 'other';
				}

			case 'cy':
				if ($count == 1) {
					return 'one';
				} else if ($count == 2) {
					return 'two';
				} else if ($count == 8 OR $count == 11) {
					return 'many';
				} else {
					return 'other';
				}

			default: // en, de, etc.
				return $count == 1 ? 'one' : 'other';
		}
	}
}

if ( ! function_exists('__'))
{
	/**
	 * Translate strings to the page language or a given language
	 *
	 * The PHP function [strtr](http://php.net/strtr) is used for replacing parameters.
	 * <code>
	 *  __('Welcome back, :user', array(':user' => $username));
	 * </code>
	 *
	 * [!!] The target language is defined by [I18n::$lang].
	 *
	 * @param   string  $string Text to translate
	 * @param   array   $values Values to replace in the translated text. [Optional]
	 *                          An associative array of replacements to make after translation.
	 *                          Incidences of any key in this array are replaced with the corresponding value.
	 *                          Based on the first character of the key, the value is escaped and/or themed:
	 *                          - !variable: inserted as is
	 *                          - :variable: inserted as is
	 *                          - @variable: escape plain text to HTML (HTML::chars)
	 *                          - %variable: escape text and theme as a placeholder for user-submitted
	 *                          - ^variable: escape text and uppercase the first character of each word in a string
	 *                          - ~variable: escape text and make a string's first character uppercase
	 *                          content (HTML::chars + theme_placeholder)
	 * @param   string  $lang   Source language [Optional]
	 * @return  string
	 *
	 * @uses    I18n::get
	 * @uses    HTML::chars
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
						// Escaped only
						$values[$key] = HTML::chars($value);
						break;
					case '%':
						// Escaped and placeholder
						$values[$key] = '<em class="placeholder">' . HTML::chars($value) . '</em>';
						break;
					case '^':
						// Escaped and uppercase the first character of each word in a string
						$values[$key] = ucwords(HTML::chars($value));
						break;
					case '~':
						// Escaped and make a string's first character uppercase
						$values[$key] = ucfirst(HTML::chars($value));
						break;
					case '!':
					case ':':
					default:
						// Pass-through
				}
			}
		}

		return strtr($string, $values);
	}
}

/**
 * Displays the returned translated text from __()
 *
 * @param   string  $string Text to translate
 * @param   array   $values Values to replace in the translated text. [Optional]
 * @param   string  $lang   Source language [Optional]
 */
function _e($string, array $values = NULL, $lang = 'en-us')
{
	echo __($string, $values, $lang);
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
