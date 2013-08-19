<?php
/**
 * Gleez Format Manager
 *
 * @package    Gleez\Format
 * @author     Sergey Yakovlev - Gleez
 * @version    1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license Gleez CMS License
 */
class Gleez_Format {

	/** ID safely format */
	const FALLBACK_FORMAT = 1;

	/** @var Format Format instance */
	public static $instance = NULL;

	/** @var array Formats set */
	public static $formats = NULL;

	/** @var integer Fallback format */
	public static $fallback_format;

	/** @var array Format configuration */
	protected $_config = array();

	/**
	 * Creates a singleton of a Gleez_Format
	 *
	 * Access to instantce directly:<br>
	 * <code>
	 *   Format::$instance;
	 * </code>
	 *
	 * @param   array   $config  Pass a configuration array to bypass the Kohana config [Optional]
	 * @return  Format  Format instance
	 */
	public static function instance(array $config = array())
	{
		if ( ! is_null(Format::$instance))
		{
			// Return the current instance if initiated already
			return Format::$instance;
		}

		if (empty($config))
		{
			// Load the configuration
			$config = Kohana::$config->load('inputfilter');
		}

		new Format($config->as_array());

		return Format::$instance;
	}

	/**
	 * Class constructor
	 *
	 * @param   array  $config  Format config
	 */
	protected function __construct(array $config)
	{
		$this->_config = $config;

		// Initiate Fallback format ID
		Format::$fallback_format = isset($this->_config['default_format'])
			? (int) $this->_config['default_format']
			: self::FALLBACK_FORMAT;

		self::_prepare($this->_config);

		// Store the instance
		Format::$instance = $this;
	}

	/**
	 * Prepare formats
	 *
	 * @param   array  $formats  Available formats
	 * @return  Gleez_Format
	 * @uses    HTML::chars
	 */
	protected function _prepare(array $config)
	{
		if (isset(Format::$formats))
		{
			// Return the current format set if initiated already
			return $this;
		}
		foreach ($config['formats'] as $id => $format)
		{
			Format::$formats[$id]['#is_fallback'] = ($id == Format::$fallback_format);
			Format::$formats[$id]['name']         = HTML::chars($format['name']);
			Format::$formats[$id]['weight']       = HTML::chars($format['weight']);

			if ($id == Format::$fallback_format)
			{
				$roles_markup = __('All roles may use this format');
			}
			else
			{
				$roles = $format['roles'];
				$roles_markup = $roles ? implode(',', $roles) : __('No roles may use this format');
			}

			Format::$formats[$id]['filters'] = $format['filters'];
			Format::$formats[$id]['roles'] = $roles_markup;
		}

		return $this;
	}

	/**
	 * Getting all formats
	 *
	 * Example:<br>
	 * <code>
	 *   $formats = Format::instance()->get_all();
	 * </code>
	 *
	 * @return  array
	 */
	public function get_all()
	{
		return Format::$formats;
	}

	/**
	 * Retrieve a single format from an `Format::$formats`
	 *
	 * If `$id` does not exist in the `Format::$formats`,
	 * the `$default` value will be returned instead.
	 *
	 * Example:<br>
	 * <code>
	 *   $formats = Format::instance()->get(1);
	 * </code>
	 *
	 * @param   integer  $id      Format ID
	 * @param   mixed    $default Default value
	 * @return  mixed
	 * @uses    Arr::get
	 */
	public function get($id, $default = NULL)
	{
		return Arr::get(Format::$formats, $id, $default);
	}

	/**
	 * Count the number of formats in `Format::$formats`
	 *
	 * Example:<br>
	 * <code>
	 *   $total = Format::instance()->count_all();
	 * </code>
	 *
	 * @return  integer
	 */
	public function count_all()
	{
		return count(Format::$formats);
	}

}