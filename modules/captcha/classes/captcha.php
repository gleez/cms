<?php defined('SYSPATH') OR die('No direct access.');
/**
 * Captcha abstract class.
 *
 * @package		Captcha
 * @author		Michael Lavers
 * @author		Kohana Team
 * @copyright	(c) 2008-2010 Kohana Team
 * @license		http://kohanaphp.com/license.html
 */
abstract class Captcha
{
	/**
	 * @var object Captcha singleton
	 */
	public static $instance;

	/**
	 * @var string Style-dependent Captcha driver
	 */
	protected $driver;

	/**
	 * @var array Default config values
	 */
	public static $config = array
	(
		'style'      	=> 'basic',
		'width'      	=> 150,
		'height'     	=> 50,
		'complexity' 	=> 4,
		'background' 	=> '',
		'fontpath'   	=> '',
		'fonts'      	=> array(),
		'promote'    	=> FALSE,
	);

	/**
	 * @var string The correct Captcha challenge answer
	 */
	protected $response;

	/**
	 * @var string Image resource identifier
	 */
	protected $image;

	/**
	 * @var string Image type ("png", "gif" or "jpeg")
	 */
	protected $image_type = 'png';

	/**
	 * Singleton instance of Captcha.
	 *
	 * @param string $group Config group name
	 * @return object
	 */
	public static function instance($group = 'default')
	{
		if ( ! isset(Captcha::$instance))
		{
			// Load the configuration for this group
			$config = Kohana::$config->load('captcha.'.$group);

			// Set the captcha driver class name
			$class = 'Captcha_'.ucfirst($config['style']);

			// Create a new captcha instance
			Captcha::$instance = $captcha = new $class($group);

			// Save captcha response at shutdown
			//register_shutdown_function(array($captcha, 'update_response_session'));
		}

		return Captcha::$instance;
	}

	/**
	 * Constructs a new Captcha object.
	 *
	 * @throws Kohana_Exception
	 * @param string Config group name
	 * @return void
	 */
	public function __construct($group = NULL)
	{
		// Create a singleton instance once
		empty(Captcha::$instance) and Captcha::$instance = $this;

		// No config group name given
		if ( ! is_string($group))
		{
			$group = 'default';
		}

		// Load and validate config group
		if ( ! is_array($config = Kohana::$config->load('captcha')->get($group)))
			throw new Kohana_Exception('Captcha group not defined in :group configuration',
					array(':group' => $group));

		// All captcha config groups inherit default config group
		if ($group !== 'default')
		{
			// Load and validate default config group
			if ( ! is_array($default = Kohana::$config->load('captcha')->get('default')))
				throw new Kohana_Exception('Captcha group not defined in :group configuration',
					array(':group' => 'default'));

			// Merge config group with default config group
			$config += $default;
		}

		// Assign config values to the object
		foreach ($config as $key => $value)
		{
			if (array_key_exists($key, Captcha::$config))
			{
				Captcha::$config[$key] = $value;
			}
		}

		// Store the config group name as well, so the drivers can access it
		Captcha::$config['group'] = $group;

		// If using a background image, check if it exists
		if ( ! empty($config['background']))
		{
			Captcha::$config['background'] = str_replace('\\', '/', realpath($config['background']));

			if ( ! is_file(Captcha::$config['background']))
				throw new Kohana_Exception('The specified file, :file, was not found.',
					array(':file' => Captcha::$config['background']));
		}

		// If using any fonts, check if they exist
		if ( ! empty($config['fonts']))
		{
			Captcha::$config['fontpath'] = str_replace('\\', '/', realpath($config['fontpath'])).'/';

			foreach ($config['fonts'] as $font)
			{
				if ( ! is_file(Captcha::$config['fontpath'].$font))
					throw new Kohana_Exception('The specified file, :file, was not found.',
						array(':file' => Captcha::$config['fontpath'].$font));
			}
		}

		// Generate a new challenge
		$this->response = $this->generate_challenge();

		Kohana::$log->add(Log::DEBUG, 'Captcha Library initialized');
	}

	/**
	 * Update captcha response session variable.
	 *
	 * @return void
	 */
	public function update_response_session()
	{
		// Store the correct Captcha response in a session
		Session::instance()->set('captcha_response', sha1(strtoupper($this->response)));
	}

	/**
	 * Validates user's Captcha response and updates response counter.
	 *
	 * @staticvar integer $counted Captcha attempts counter
	 * @param string $response User's captcha response
	 * @return boolean
	 */
	public static function valid($response)
	{
		// Maximum one count per page load
		static $counted;

		// User has been promoted, always TRUE and don't count anymore
		if (Captcha::instance()->promoted())
			return TRUE;

		// Challenge result
		$result = (bool) (sha1(strtoupper($response)) === Session::instance()->get('captcha_response'));

		// Increment response counter
		if ($counted !== TRUE)
		{
			$counted = TRUE;

			// Valid response
			if ($result === TRUE)
			{
				Captcha::instance()->valid_count(Session::instance()->get('captcha_valid_count') + 1);
			}
			// Invalid response
			else
			{
				Captcha::instance()->invalid_count(Session::instance()->get('captcha_invalid_count') + 1);
			}
		}

		return $result;
	}

	/**
	 * Gets or sets the number of valid Captcha responses for this session.
	 *
	 * @param integer $new_count New counter value
	 * @param boolean $invalid Trigger invalid counter (for internal use only)
	 * @return integer Counter value
	 */
	public function valid_count($new_count = NULL, $invalid = FALSE)
	{
		// Pick the right session to use
		$session = ($invalid === TRUE) ? 'captcha_invalid_count' : 'captcha_valid_count';

		// Update counter
		if ($new_count !== NULL)
		{
			$new_count = (int) $new_count;

			// Reset counter = delete session
			if ($new_count < 1)
			{
				Session::instance()->delete($session);
			}
			// Set counter to new value
			else
			{
				Session::instance()->set($session, (int) $new_count);
			}

			// Return new count
			return (int) $new_count;
		}

		// Return current count
		return (int) Session::instance()->get($session);
	}

	/**
	 * Gets or sets the number of invalid Captcha responses for this session.
	 *
	 * @param integer $new_count New counter value
	 * @return integer Counter value
	 */
	public function invalid_count($new_count = NULL)
	{
		return $this->valid_count($new_count, TRUE);
	}

	/**
	 * Resets the Captcha response counters and removes the count sessions.
	 *
	 * @return void
	 */
	public function reset_count()
	{
		$this->valid_count(0);
		$this->valid_count(0, TRUE);
	}

	/**
	 * Checks whether user has been promoted after having given enough valid responses.
	 *
	 * @param integer $threshold Valid response count threshold
	 * @return boolean
	 */
	public function promoted($threshold = NULL)
	{
		// Promotion has been disabled
		if (Captcha::$config['promote'] === FALSE)
			return FALSE;

		// Use the config threshold
		if ($threshold === NULL)
		{
			$threshold = Captcha::$config['promote'];
		}

		// Compare the valid response count to the threshold
		return ($this->valid_count() >= $threshold);
	}

	/**
	 * Magically outputs the Captcha challenge.
	 *
	 * @return mixed
	 */
	public function __toString()
	{
		return $this->render(TRUE);
	}

	/**
	 * Returns the image type.
	 *
	 * @param string $filename Filename
	 * @return string|boolean Image type ("png", "gif" or "jpeg")
	 */
	public function image_type($filename)
	{
		switch (strtolower(substr(strrchr($filename, '.'), 1)))
		{
			case 'png':
				return 'png';

			case 'gif':
				return 'gif';

			case 'jpg':
			case 'jpeg':
				// Return "jpeg" and not "jpg" because of the GD2 function names
				return 'jpeg';

			default:
				return FALSE;
		}
	}

	/**
	 * Creates an image resource with the dimensions specified in config.
	 * If a background image is supplied, the image dimensions are used.
	 *
	 * @throws Kohana_Exception If no GD2 support
	 * @param string $background Path to the background image file
	 * @return void
	 */
	public function image_create($background = NULL)
	{
		// Check for GD2 support
		if ( ! function_exists('imagegd2'))
			throw new Kohana_Exception('captcha.requires_GD2');

		// Create a new image (black)
		$this->image = imagecreatetruecolor(Captcha::$config['width'], Captcha::$config['height']);

		// Use a background image
		if ( ! empty($background))
		{
			// Create the image using the right function for the filetype
			$function = 'imagecreatefrom'.$this->image_type($background);
			$this->background_image = $function($background);

			// Resize the image if needed
			if (imagesx($this->background_image) !== Captcha::$config['width']
			    or imagesy($this->background_image) !== Captcha::$config['height'])
			{
				imagecopyresampled
				(
					$this->image, $this->background_image, 0, 0, 0, 0,
					Captcha::$config['width'], Captcha::$config['height'],
					imagesx($this->background_image), imagesy($this->background_image)
				);
			}

			// Free up resources
			imagedestroy($this->background_image);
		}
	}

	/**
	 * Fills the background with a gradient.
	 *
	 * @param resource $color1 GD image color identifier for start color
	 * @param resource $color2 GD image color identifier for end color
	 * @param string $direction Direction: 'horizontal' or 'vertical', 'random' by default
	 * @return void
	 */
	public function image_gradient($color1, $color2, $direction = NULL)
	{
		$directions = array('horizontal', 'vertical');

		// Pick a random direction if needed
		if ( ! in_array($direction, $directions))
		{
			$direction = $directions[array_rand($directions)];

			// Switch colors
			if (mt_rand(0, 1) === 1)
			{
				$temp = $color1;
				$color1 = $color2;
				$color2 = $temp;
			}
		}

		// Extract RGB values
		$color1 = imagecolorsforindex($this->image, $color1);
		$color2 = imagecolorsforindex($this->image, $color2);

		// Preparations for the gradient loop
		$steps = ($direction === 'horizontal') ? Captcha::$config['width'] : Captcha::$config['height'];

		$r1 = ($color1['red'] - $color2['red']) / $steps;
		$g1 = ($color1['green'] - $color2['green']) / $steps;
		$b1 = ($color1['blue'] - $color2['blue']) / $steps;

		if ($direction === 'horizontal')
		{
			$x1 =& $i;
			$y1 = 0;
			$x2 =& $i;
			$y2 = Captcha::$config['height'];
		}
		else
		{
			$x1 = 0;
			$y1 =& $i;
			$x2 = Captcha::$config['width'];
			$y2 =& $i;
		}

		// Execute the gradient loop
		for ($i = 0; $i <= $steps; $i++)
		{
			$r2 = $color1['red'] - floor($i * $r1);
			$g2 = $color1['green'] - floor($i * $g1);
			$b2 = $color1['blue'] - floor($i * $b1);
			$color = imagecolorallocate($this->image, $r2, $g2, $b2);

			imageline($this->image, $x1, $y1, $x2, $y2, $color);
		}
	}

	/**
	 * Returns the img html element or outputs the image to the browser.
	 *
	 * @param boolean $html Output as HTML
	 * @return mixed HTML, string or void
	 */
	public function image_render($html)
	{
		// Output html element
		if ($html === TRUE)
			return '<img src="'.url::site('captcha/'.Captcha::$config['group']).'" width="'.Captcha::$config['width'].'" height="'.Captcha::$config['height'].'" alt="Captcha" class="captcha" />';

		// Send the correct HTTP header
		Request::current()->response()
			->headers('Content-Type', 'image/'.$this->image_type)
			->headers('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
			->headers('Pragma', 'no-cache')
			->headers('Connection', 'close');

		// Pick the correct output function
		$function = 'image'.$this->image_type;
		$function($this->image);

		// Free up resources
		imagedestroy($this->image);
	}

	/* DRIVER METHODS */

	/**
	 * Generate a new Captcha challenge.
	 *
	 * @return string The challenge answer
	 */
	abstract public function generate_challenge();

	/**
	 * Output the Captcha challenge.
	 *
	 * @param boolean $html Render output as HTML
	 * @return mixed
	 */
	abstract public function render($html = TRUE);

} // End Captcha Class
