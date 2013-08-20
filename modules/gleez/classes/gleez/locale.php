<?php
/**
 * Gleez Locale
 *
 * Base class for [i18n and l10n][ref-wiki] support.
 *
 * [!!] This code and ideas partly borrowed and partly adapted from
 *      [Zend Framework][ref-zend] 1.12. Please see Zend license: /licenses/Zend.txt
 *
 * [ref-zend]: http://framework.zend.com/
 * [ref-wiki]: http://en.wikipedia.org/wiki/Internationalization_and_localization
 *
 * @package    Gleez\Internationalization
 * @author     Sergey Yakovlev - Gleez
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Locale {

	/**
	 * The user's Web browser provides information with each request,
	 * which is published by PHP in the global variable $_SERVER['HTTP_ACCEPT_LANGUAGE'].
	 */
	const CLIENT = 'client';

	/**
	 * PHP publishes the host server's locale via the PHP internal
	 * function [setlocale()](http://php.net/setlocale).
	 */
	const ENVIRONMENT = 'environment';

	/**
	 * Using this constant during instantiation will give preference to choosing a locale
	 * based on Gleez defaults.
	 */
	const FRAMEWORK = 'framework';

	/**
	 * Gleez_Locale should automatically detect any locale which can be worked with.
	 */
	const DETECTED = 'detected';

	/**
	 * Actual set locale
	 * @var string
	 */
	protected $_locale;

	/**
	 * Browser detected locale
	 * @var string
	 */
	protected static $_client_locales;

	/**
	 * Environment detected locale
	 * @var string
	 */
	protected static $_environment_locales;

	/**
	 * Automatic detected locale
	 * @var string
	 */
	protected static $_detected;

	/**
	 * Default locale
	 * @var array
	 */
	protected static $_framework = array('en' => TRUE);

	/**
	 * Gleez_Locale instance
	 * @var Gleez_Locale
	 */
	protected static $_instance;

	/**
	 * Cookie name
	 * @var string
	 */
	public static $cookie = 'language';

	/**
	 * Singleton instance of Gleez_Locale
	 *
	 * @param   string|Gleez_Locale  $locale  Locale for parsing input [Optional]
	 * @return  Gleez_Locale
	 */
	public static function instance($locale = NULL)
	{
		if ( ! isset(Gleez_Locale::$_instance))
		{
			// Create a new locale instance
			Gleez_Locale::$_instance = new Gleez_Locale($locale);
		}

		return Gleez_Locale::$_instance;
	}

	/**
	 * Constructs a new locale object
	 *
	 * ### Overview
	 *
	 * If no locale is given a automatic search is done.
	 * Then the most probable locale will be automatically set.
	 *
	 * __Search order is__:
	 *
	 * 1. Given Locale
	 * 2. HTTP Client
	 * 3. Server Environment
	 * 4. Framework Standard
	 *
	 * Examples:
	 * ~~~
	 * // Choosing a specific locale
	 * $locale = new Gleez_Locale('de_DE');
	 *
	 * // Automatically selecting a locale
	 * $locale = new Gleez_Locale();
	 *
	 * // Default behavior, same as above:
	 * $locale = new Gleez_Locale(Gleez_Locale::CLIENT);
	 *
	 * // Prefer settings on host server
	 * $locale = new Gleez_Locale(Gleez_Locale::ENVIRONMENT);
	 *
	 * // Prefer Gleez framework settings
	 * $locale = new Gleez_Locale(Gleez_Locale::FRAMEWORK);
	 * ~~~
	 *
	 * @param   string|Gleez_Locale  $locale  Locale for parsing input [Optional]
	 *
	 * @throws  Locale_Exception
	 */
	public function __construct($locale = NULL)
	{
		$this->set_locale($locale);
	}

	/**
	 * Returns a string representation of the object
	 *
	 * Alias for toString
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Returns a string representation of the object
	 *
	 * Example
	 * ~~~
	 * print $locale->toString();
	 * ~~~
	 *
	 * @return  string
	 */
	public function toString()
	{
		return (string) $this->_locale;
	}

	/**
	 * Serialization Interface
	 *
	 * @return  string
	 */
	public function serialize()
	{
		return serialize($this);
	}

	/**
	 * Prepare and returns a single locale on detection
	 *
	 * @param   string|Gleez_Locale  $locale  Locale to work on
	 * @param   boolean              $strict  Strict preparation [Optional]
	 *
	 * @return  string
	 *
	 * @throws  Locale_Exception
	 *
	 * @uses    Locale_Data::getLocaleData
	 * @uses    Locale_Data::getTerritoryData
	 */
	private static function _prepare_locale($locale, $strict = FALSE)
	{
		if ($locale instanceof Gleez_Locale)
		{
			$locale = $locale->toString();
		}

		if (is_array($locale))
		{
			return '';
		}

		if (is_null(self::$_detected))
		{
			self::$_client_locales      = self::get_client_locales();
			self::$_environment_locales = self::get_environment_locales();
			self::$_detected            = self::$_client_locales + self::$_environment_locales + self::$_framework;
		}

		if ( ! $strict)
		{
			if ($locale === 'client')
			{
				$locale = self::$_client_locales;
			}

			if ($locale === 'environment')
			{
				$locale = self::$_environment_locales;
			}

			if ($locale === 'framework')
			{
				$locale = self::$_framework;
			}

			if (($locale === 'detected') OR (is_null($locale)))
			{
				$locale = self::$_detected;
			}

			if (is_array($locale))
			{
				$locale = key($locale);
			}
		}

		// This can only happen when someone extends Gleez_Locale and erases the `$_framework`
		if (is_null($locale))
		{
			throw new Locale_Exception('Failed to autodetect of Locale!');
		}

		if (strpos($locale, '-') !== FALSE)
		{
			$locale = strtr($locale, '-', '_');
		}

		$parts          = explode('_', $locale);
		$locale_data    = Locale_Data::getLocaleData();
		$territory_data = Locale_Data::getTerritoryData();

		if ( ! isset($locale_data[$parts[0]]))
		{
			if ((count($parts) == 1) AND array_key_exists($parts[0], $territory_data))
			{
				return $territory_data[$parts[0]];
			}

			return '';
		}

		foreach($parts as $key => $value)
		{
			if ((strlen($value) < 2) || (strlen($value) > 3))
			{
				unset($parts[$key]);
			}
		}

		$locale = implode('_', $parts);

		return (string) $locale;
	}

	/**
	 * Return an array of all accepted languages of the client including quality
	 *
	 * [!!] Expects RFC compliant header
	 *
	 * __The notation can be (examples):__
	 *
	 * +  ru,en-US;q=0.8,en;q=0.6
	 * +  de,en-UK-US;q=0.5,fr-FR;q=0.2
	 * +  fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4
	 *
	 * Example:
	 * ~~~
	 * $locale->get_client_locales();
	 * // returned an array, i.e:
	 * // array('fr_FR' => 1.0, 'fr' => 1.0, 'en_US' => 0.6, 'en' => 0.6)
	 * ~~~
	 *
	 * @return  array
	 *
	 * @link    http://php.net/manual/en/function.getenv.php getenv()
	 */
	public static function get_client_locales()
	{
		if ( ! is_null(self::$_client_locales))
		{
			return self::$_client_locales;
		}

		$languages  = array();
		$http_langs = getenv('HTTP_ACCEPT_LANGUAGE');

		if ( ! $http_langs)
		{
			return $languages;
		}

		$accepted = preg_split('/,\s*/', $http_langs);

		foreach ($accepted as $accept)
		{
			$match = NULL;

			$result = preg_match('/^([a-z]{1,8}(?:[-_][a-z]{1,8})*)(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accept, $match);

			if ($result < 1)
			{
				continue;
			}

			// The highest priority
			$quality = 1.0;

			if (isset($match[2]))
			{
				$quality = (float) $match[2];
			}

			$country1 = explode('-', $match[1]);
			$region   = array_shift($country1);

			$country2 = explode('_', $region);
			$region   = array_shift($country2);

			foreach ($country1 as $country)
			{
				$languages[$region . '_' . strtoupper($country)] = $quality;
			}

			foreach ($country2 as $country)
			{
				$languages[$region . '_' . strtoupper($country)] = $quality;
			}

			if ( ! isset($languages[$region]) OR $languages[$region] < $quality)
			{
				$languages[$region] = $quality;
			}
		}

		self::$_client_locales = $languages;

		return $languages;
	}

	/**
	 * Expects the Systems standard locale
	 *
	 * For Windows `LC_COLLATE=C;LC_CTYPE=German_Austria.1252;LC_MONETARY=C`
	 * would be recognised as `de_AT`
	 *
	 * @return  array
	 *
	 * @uses    Locale_Data::getLocaleData
	 * @uses    Locale_Data::$languages
	 * @uses    Locale_Data::$regions
	 *
	 * @link    http://php.net/setlocale setlocale()
	 */
	public static function get_environment_locales()
	{
		// Return cache
		if ( ! is_null(self::$_environment_locales))
		{
			return self::$_environment_locales;
		}

		$language      = setlocale(LC_ALL, 0);
		$languages     = explode(';', $language);
		$languagearray = array();
		$locale_data   = Locale_Data::getLocaleData();

		foreach ($languages as $locale)
		{
			if (strpos($locale, '=') !== FALSE)
			{
				$language = substr($locale, strpos($locale, '='));
				$language = substr($language, 1);
			}

			if ($language !== 'C')
			{
				if (strpos($language, '.') !== FALSE)
				{
					$language = substr($language, 0, strpos($language, '.'));
				}
				elseif (strpos($language, '@') !== FALSE)
				{
					$language = substr($language, 0, strpos($language, '@'));
				}

				// Locales
				$language = str_ireplace(
					array_keys(Locale_Data::$languages),
					array_values(Locale_Data::$languages),
					$language
				);

				// Regions
				$language = str_ireplace(
					array_keys(Locale_Data::$regions),
					array_values(Locale_Data::$regions),
					$language
				);

				if (isset($locale_data[$language]))
				{
					$languagearray[$language] = 1;

					if (strpos($language, '_') !== FALSE)
					{
						$languagearray[substr($language, 0, strpos($language, '_'))] = 1;
					}
				}
			}
		}

		self::$_environment_locales = $languagearray;

		return $languagearray;
	}

	/**
	 * Return the default locale
	 *
	 * Returns an array of all locale string.
	 *
	 * @return  array
	 */
	public static function get_framework_locales()
	{
		return self::$_framework;
	}

	/**
	 * List of all Locales
	 *
	 * Returns a list of all known locales where the locale is the key.
	 * Only real locales are returned, the internal locale 'root' are suppressed.
	 *
	 * @return  array
	 *
	 * @uses    Gleez_Locale_Data::getLocaleData
	 */
	public static function get_locale_list()
	{
		$list = Locale_Data::getLocaleData();
		unset($list['root']);

		return $list;
	}

	/**
	 * Returns the language part of the locale
	 *
	 * Static alias for [Gleez_Locale::get_language]
	 *
	 * @param   string  $locale  Locale (eg. en_US, ru_RU, ar_JO, ...)
	 *
	 * @return  string
	 */
	public static function get_language_by_locale($locale)
	{
		$locale = explode('_', $locale);

		return $locale[0];
	}

	/**
	 * Returns the region part of the locale if available
	 *
	 * Static alias for [Gleez_Locale::get_region]
	 *
	 * @param   string  $locale  Locale (eg. en_US, ru_RU, ar_JO, ...)
	 * @return  boolean|string
	 */
	public static function get_region_by_locale($locale)
	{
		$locale = explode('_', $locale);

		if (isset($locale[1]))
		{
			return $locale[1];
		}

		return FALSE;
	}

	/**
	 * Returns the language part of the locale
	 *
	 * Example:
	 * ~~~
	 * print $locale->get_language();
	 * ~~~
	 *
	 * @return string
	 */
	public function get_language()
	{
		$locale = explode('_', $this->_locale);

		return $locale[0];
	}

	/**
	 * Returns the region part of the locale if available
	 *
	 * Example:
	 * ~~~
	 * // For example, locale is 'de_AT'
	 * print $locale->get_region(); // 'AT'
	 * ~~~
	 *
	 * @return  string   Region part of the locale if available
	 *
	 * @return  boolean  FALSE if not available
	 */
	public function get_region()
	{
		$locale = explode('_', $this->_locale);

		if (isset($locale[1]))
		{
			return $locale[1];
		}

		return FALSE;
	}

	/**
	 * Sets a new locale
	 *
	 * @param  string|Gleez_Locale  $locale  New locale to set [Optional]
	 *
	 * @uses   Locale_Data::getLocaleData
	 */
	public function set_locale($locale = NULL)
	{
		$locale      = self::_prepare_locale($locale);
		$locale_data = Locale_Data::getLocaleData();

		if ( ! isset($locale_data[(string)$locale]))
		{
			$region = substr((string) $locale, 0, 3);

			if (isset($region[2]))
			{
				if (($region[2] === '_') OR ($region[2] === '-'))
				{
					$region = substr($region, 0, 2);
				}
			}

			if (isset($locale_data[(string)$region]))
			{
				$this->_locale = $region;
			}
			else
			{
				$this->_locale = 'root';
			}
		}
		else
		{
			$this->_locale = $locale;
		}
	}

	/**
	 * Set new default locale
	 *
	 * Sets a new default locale which will be used when no locale can be detected
	 * If provided you can set a quality between 0 and 1 (or 2 and 100) which represents
	 * the percent of quality the browser requested within HTTP
	 *
	 * Example:
	 * ~~~
	 * Gleez_Locale::set_default('de');
	 * ~~~
	 *
	 * @param   string|Gleez_Locale  $locale   Locale to set
	 * @param   integer              $quality  The quality to set from 0 to 1 [Optional]
	 * @throws  Locale_Exception
	 *
	 * @uses    Locale_Data::locale_data
	 */
	public static function set_default($locale, $quality = 1)
	{
		if (in_array($locale, array('detected', 'root', 'framework', 'environment', 'client')))
		{
			throw new Locale_Exception('Only full qualified locales can be used as default!');
		}

		if (($quality < 0.1) or ($quality > 100))
		{
			throw new Locale_Exception('Locale quality (priority) must be between 0.1 and 100');
		}

		if ($quality > 1)
		{
			$quality /= 100;
		}

		$locale      = self::_prepare_locale($locale);
		$locale_data = Locale_Data::getLocaleData();

		if (isset($locale_data[(string)$locale]))
		{
			self::$_framework = array((string) $locale => $quality);
		}
		else
		{
			$elocale = explode('_', (string) $locale);

			if (isset($locale_data[$elocale[0]]))
			{
				self::$_framework = array($elocale[0] => $quality);
			}
			else
			{
				throw new Locale_Exception("Can't set unknown locale as default!");
			}
		}

		self::$_detected = self::get_client_locales() + self::get_environment_locales() + self::get_framework_locales();
	}

	/**
	 * Returns true if both locales are equal
	 *
	 * Example:
	 * ~~~
	 * $locale = new Gleez_Locale();
	 * $mylocale = new Gleez_Locale('en_US');
	 *
	 * // Check if locales are equal
	 * if ($locale->equals($mylocale))
	 * {
	 *     // ...
	 * }
	 * ~~~
	 *
	 * @param   Gleez_Locale  $locale  Locale to check for equality
	 * @return  boolean
	 */
	public function equals(Gleez_Locale $locale)
	{
		return ($locale->toString() === $this->toString());
	}
}