<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Add plural support to i18n
 *
 * @package   Gleez\I18n
 * @author    Sandeep Sangamreddi - Gleez
 * @copyright (c) 2011-2013 Gleez Technologies
 * @license   http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_I18n extends Kohana_I18n{

	/**
	 * This method is borrowed from the s7ncms code:
	 */
	public static function get_plural($string, $count)
	{
		// Load the translation table
		$table = I18n::load(I18n::$lang);

		$key = Gleez_I18n::get_plural_key(I18n::$lang, $count);

		// Return the translated string if it exists
		return isset($table[$string][$key]) ? $table[$string][$key]: $string;
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
	private static function get_plural_key($lang, $count) {

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