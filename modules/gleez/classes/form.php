<?php
/**
 * Form helper class
 *
 * @package    Gleez\Helpers
 * @author     Gleez Team
 * @version    1.0.1
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Form {

	/**
	 * Generates an opening HTML form tag
	 *
	 * Examples:
	 * ~~~
	 * // Form will submit back to the current page using POST
	 * echo Form::open();
	 *
	 * // Form will submit to 'search' using GET
	 * echo Form::open('search', array('method' => 'get'));
	 *
	 * // When "file" inputs are present, you must include the "enctype"
	 * echo Form::open(NULL, array('enctype' => 'multipart/form-data'));
	 * ~~~
	 *
	 * @param   mixed  $action  Form action, defaults to the current request URI, or Request class to use [Optional]
	 * @param   array  $attrs   HTML attributes [Optional]
	 * @return  string
	 *
	 * @uses    Request::uri
	 * @uses    URL::site
	 * @uses    URL::is_remote
	 * @uses    URL::explode
	 * @uses    HTML::attributes
	 * @uses    Assets::css
	 * @uses    CSRF::key
	 * @uses    CSRF::token
	 */
	public static function open($action = NULL, array $attrs = NULL)
	{
		if ($action instanceof Request)
		{
			// Use the current URI
			$action = $action->uri();
		}

		if ($action === '')
		{
			// Allow empty form actions (submits back to the current url).
			$action = '';
		}
		elseif ( ! URL::is_remote($action))
		{

			// Make the URI absolute
			$action = URL::site($action);
		}

		// Add the form action to the attributes
		$attrs['action'] = $action;

		// Dynamically sets destination url to from action if exists in url
		if (Kohana::$is_cli === FALSE AND $desti = Request::current()->query('destination') AND ! empty($desti))
		{
			// Properly parse the path and query
			$url = URL::explode($action);

			//On seriously malformed URLs, parse_url() may return FALSE.
			if (isset($url['path']) AND is_array($url['query_params']))
			{
				//add destination param
				$url['query_params']['destination'] = $desti;

				//set the form action parameter
				$attrs['action'] = $url['path'].URL::query($url['query_params']);
			}
		}

		// Only accept the default character set
		$attrs['accept-charset'] = Kohana::$charset;

		if ( ! isset($attrs['method']))
		{
			// Use POST method
			$attrs['method'] = 'post';
		}

		$out = '<form'.HTML::attributes($attrs).'>'.PHP_EOL;

		if (Gleez::$installed)
		{
			// Assign the global form css file
			Assets::css('form', 'media/css/form.css', array('weight' => 2));

			$action  = md5($action . CSRF::key());
			$out 	.= self::hidden('_token', CSRF::token(FALSE, $action)).PHP_EOL;
			$out 	.= self::hidden('_action', $action).PHP_EOL;
		}

		return $out;
	}

	/**
	 * Creates the closing form tag
	 *
	 * Example:
	 * ~~~
	 * echo Form::close();
	 * ~~~
	 *
	 * @return  string
	 */
	public static function close()
	{
		return '</form>';
	}

	/**
	 * Creates a form input
	 *
	 * If no type is specified, a "text" type input will be returned.
	 *
	 * Example:
	 * ~~~
	 * echo Form::input('username', $username);
	 * ~~~
	 *
	 * @param   string  $name   Input name
	 * @param   string  $value  Input value [Optional]
	 * @param   array   $attrs  HTML attributes [Optional]
	 * @param   string  $url    Input url (autocomplete url) [Optional]
	 * @param   boolean $smart  Smart smart results listing [Optional]
	 *
	 * @return  string
	 *
	 * @uses    HTML::attributes
	 * @uses    Assets::js
	 * @uses    URL::site
	 */
	public static function input($name, $value = NULL, array $attrs = NULL, $url = '', $smart = TRUE)
	{
		// Set the input name
		$attrs['name'] = $name;

		// Set the input value
		$attrs['value'] = $value;

		if ( ! isset($attrs['type']))
		{
			// Default type is text
			$attrs['type'] = 'text';
		}

		if (! isset($attrs['id']))
		{
			$attrs['id'] = self::_get_id_by_name($name);
		}

		$out = '';

		if ($attrs['type'] === 'text' AND ! empty($url))
		{
			$attrs['class'] = isset($attrs['class']) ? $attrs['class'].' form-autocomplete' : 'form-autocomplete';
			$attrs['id'] = $name;
			$attrs['autocomplete'] = "off";
			$attrs['data-url']     = URL::site($url, TRUE);
			$attrs['data-provide'] = 'typeahead';

			// Assign the typeahead js file
			Assets::js('greet.typeahead', 'media/js/greet.typeahead.js', 'gleez');
		}

		$out .= '<input'.HTML::attributes($attrs).'>';

		return $out;
	}


	/**
	 * Creates a hidden form input
	 *
	 * Example:
	 * ~~~
	 * echo Form::hidden('csrf', $token);
	 * ~~~
	 *
	 * @param   string  $name        Input name [Optional]
	 * @param   string  $value       Input value [Optional]
	 * @param   array   $attributes  HTML attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Form::input
	 */
	public static function hidden($name, $value = NULL, array $attributes = NULL)
	{
		$attributes['type'] = 'hidden';

		return self::input($name, $value, $attributes);
	}

	/**
	 * Creates a password form input
	 *
	 * Example:
	 * ~~~
	 * echo Form::password('password');
	 * ~~~
	 *
	 * @param   string  $name        Input name [Optional]
	 * @param   string  $value       Input value [Optional]
	 * @param   array   $attributes  HTML attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Form::input
	 */
	public static function password($name, $value = NULL, array $attributes = NULL)
	{
		$attributes['type'] = 'password';

		return self::input($name, $value, $attributes);
	}

	/**
	 * Creates a file upload form input. No input value can be specified
	 *
	 * Example:
	 * ~~~
	 * echo Form::file('image');
	 * ~~~
	 *
	 * @param   string  $name        Input name
	 * @param   array   $attributes  HTML attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Form::input
	 */
	public static function file($name, array $attributes = NULL)
	{
		$attributes['type'] = 'file';

		return self::input($name, NULL, $attributes);
	}

	/**
	 * Creates a checkbox form input
	 *
	 * Example:
	 * ~~~
	 * echo Form::checkbox('remember_me', 1, (bool) $remember);
	 * ~~~
	 *
	 * @param   string  $name        Input name
	 * @param   string  $value       Input value [Optional]
	 * @param   boolean $checked     Checked status [Optional]
	 * @param   array   $attributes  HTML attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Form::input
	 */
	public static function checkbox($name, $value = NULL, $checked = FALSE, array $attributes = NULL)
	{
		$attributes['type'] = 'checkbox';

		if ($checked === TRUE)
		{
			// Make the checkbox active
			$attributes[] = 'checked';
		}

		return self::input($name, $value, $attributes);
	}

	/**
	 * Creates a radio form input
	 *
	 * Example:
	 * ~~~
	 * echo Form::radio('like_cats', 1, $cats);
	 * echo Form::radio('like_cats', 0, ! $cats);
	 * ~~~
	 *
	 * @param   string  $name        Input name
	 * @param   string  $value       Input value [Optional]
	 * @param   boolean $checked     Checked status [Optional]
	 * @param   array   $attributes  HTML attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Form::input
	 */
	public static function radio($name, $value = NULL, $checked = FALSE, array $attributes = NULL)
	{
		$attributes['type'] = 'radio';

		if ($checked === TRUE)
		{
			// Make the radio active
			$attributes[] = 'checked';
		}

		return self::input($name, $value, $attributes);
	}

	/**
	 * Creates a textarea form input
	 *
	 * Example:
	 * ~~~
	 * echo Form::textarea('about', $about);
	 * ~~~
	 *
	 * @param   string  $name           Textarea name
	 * @param   string  $body           Textarea body [Optional]
	 * @param   array   $attributes     HTML attributes [Optional]
	 * @param   boolean $double_encode  Encode existing HTML characters [Optional]
	 *
	 * @return  string
	 *
	 * @uses    HTML::attributes
	 * @uses    HTML::chars
	 */
	public static function textarea($name, $body = '', array $attributes = NULL, $double_encode = TRUE)
	{
		// Set the input name
		$attributes['name'] = $name;

		if ( ! isset($attributes['id']))
		{
			$attributes['id'] = self::_get_id_by_name($name);
		}

		// Add default rows and cols attributes (required)
		$attributes += array('rows' => 10, 'cols' => 50);

		return '<textarea'.HTML::attributes($attributes).'>'.HTML::chars($body, $double_encode).'</textarea>';
	}

	/**
	 * Creates a select form input
	 *
	 * Example:
	 * ~~~
	 * echo Form::select('country', $countries, $country);
	 * ~~~
	 *
	 * @param   string  $name        Input name
	 * @param   array   $options     Available options [Optional]
	 * @param   mixed   $selected    Selected option string, or an array of selected options [Optional]
	 * @param   array   $attributes  HTML attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    HTML::attributes
	 */
	public static function select($name, array $options = NULL, $selected = NULL, array $attributes = NULL)
	{
		// Set the input name
		$attributes['name'] = $name;

		if (! isset($attributes['id']))
		{
			$attributes['id'] = self::_get_id_by_name($name);
		}

		if (is_array($selected))
		{
			// This is a multi-select, god save us!
			$attributes[] = 'multiple';
		}

		if ( ! is_array($selected))
		{
			if ($selected === NULL)
			{
				// Use an empty array
				$selected = array();
			}
			else
			{
				// Convert the selected options to an array
				$selected = array( (string) $selected);
			}
		}

		if (empty($options))
		{
			// There are no options
			$options = '';
		}
		else
		{
			foreach ($options as $value => $name)
			{
				if (is_array($name))
				{
					// Create a new optgroup
					$group = array('label' => $value);

					// Create a new list of options
					$_options = array();

					foreach ($name as $_value => $_name)
					{
						// Force value to be string
						$_value = (string) $_value;

						// Create a new attribute set for this option
						$option = array('value' => $_value);

						if (in_array($_value, $selected))
						{
							// This option is selected
							$option[] = 'selected';
						}

						// Change the option to the HTML string
						$_options[] = '<option'.HTML::attributes($option).'>'.HTML::chars($_name, FALSE).'</option>';
					}

					// Compile the options into a string
					$_options = "\n".implode("\n", $_options)."\n";

					$options[$value] = '<optgroup'.HTML::attributes($group).'>'.$_options.'</optgroup>';
				}
				else
				{
					// Force value to be string
					$value = (string) $value;

					// Create a new attribute set for this option
					$option = array('value' => $value);

					if (in_array($value, $selected))
					{
						// This option is selected
						$option[] = 'selected';
					}

					// Change the option to the HTML string
					$options[$value] = '<option'.HTML::attributes($option).'>'.HTML::chars($name, FALSE).'</option>';
				}
			}

			// Compile the options into a single string
			$options = "\n".implode("\n", $options)."\n";
		}

		return '<select'.HTML::attributes($attributes).'>'.$options.'</select>';
	}

	/**
	 * Creates a submit form input
	 *
	 * Example:
	 * ~~~
	 * echo Form::submit(NULL, 'Login');
	 * ~~~
	 *
	 * @param   string  $name   Input name
	 * @param   string  $value  Input value
	 * @param   array   $attrs  HTML attributes [Optional]
	 *
	 * @return  string
	 */
	public static function submit($name, $value, array $attrs = array())
	{
		$attrs['type'] = 'submit';

		return self::input($name, $value, $attrs);
	}

	/**
	 * Creates a image form input
	 *
	 * Example:
	 * ~~~
	 * echo Form::image(NULL, NULL, array('src' => 'media/img/login.png'));
	 * ~~~
	 *
	 * @param   string  $name        Input name
	 * @param   string  $value       Input value
	 * @param   array   $attributes  HTML attributes [Optional]
	 * @param   boolean $index       Add index file to URL? [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Form::input
	 */
	public static function image($name, $value, array $attributes = NULL, $index = FALSE)
	{
		if ( ! empty($attributes['src']))
		{
			if (strpos($attributes['src'], '://') === FALSE)
			{
				// Add the base URL
				$attributes['src'] = URL::base($index).$attributes['src'];
			}
		}

		$attributes['type'] = 'image';

		return self::input($name, $value, $attributes);
	}

	/**
	 * Creates a button
	 *
	 * Example:
	 * ~~~
	 * echo Form::button('login', 'Login', array('class' => 'pull-right'));
	 * ~~~
	 *
	 * @param   string  $name     Button name
	 * @param   string  $caption  Button caption
	 * @param   array   $attrs    HTML attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    HTML::attributes
	 */
	public static function button($name, $caption, array $attrs = array())
	{
		// Set the button name
		$attrs['name'] = $name;

		// Set the button type
		if ( ! isset($attrs['type']))
		{
			// Default type is button
			$attrs['type'] = 'button';
		}

		if (! isset($attrs['id']))
		{
			$attrs['id'] = self::_get_id_by_name($name);
		}

		$out = '<button '.HTML::attributes($attrs).'>'.$caption.'</button>';

		return $out;
	}

	/**
	 * Creates a form label. Label text is not automatically translated
	 *
	 * Example:
	 * ~~~
	 * echo Form::label('username', 'Username');
	 * ~~~
	 *
	 * @param   string  $input       Target input
	 * @param   string  $text        Label text [Optional]
	 * @param   array   $attributes  HTML attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    HTML::attributes
	 */
	public static function label($input, $text = NULL, array $attributes = NULL)
	{
		if ($text === NULL)
		{
			// Use the input name as the text
			$text = ucwords(preg_replace('/[\W_]+/', ' ', $input));
		}

		// Set the label target
		$attributes['for'] = $input;

		return '<label'.HTML::attributes($attributes).'>'.$text.'</label>';
	}

	/**
	 * Creates CSRF token input
	 *
	 * @param   string  $id      ID e.g. uid [Optional]
	 * @param   string  $action  Action [Optional]
	 *
	 * @return  string
	 *
	 * @uses    CSRF::token
	 */
	public static function csrf($id = '', $action = '')
	{
		return self::hidden('token', CSRF::token($id, $action));
	}

	/**
	 * Creates weight select field
	 *
	 * @param   string   $name      Input name
	 * @param   integer  $selected  Selected option int [Optional]
	 * @param   array    $attrs     HTML attributes [Optional]
	 * @param   integer  $delta     Delta [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Form::select
	 */
	public static function weight($name, $selected = 0, array $attrs = NULL, $delta = 15)
	{
		$options = array();

		for ($n = (-1 * $delta); $n <= $delta; $n++)
		{
			$options[$n] = $n;
		}

		return self::select($name, $options, $selected, $attrs);
	}

	/**
	 * Create a form field for filtering
	 *
	 * @param   string $column  Column
	 * @param   array  $vals    Filter values
	 * @param   array  $attrs   Filter attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    Arr::get
	 */
	public static function filter($column, array $vals, array $attrs = array())
	{
		if ( ! isset($attrs['style']))
		{
			// Default type is text
			$attrs['style'] = 'width: 100%';
		}

		return self::input("filter[$column]", Arr::get($vals, $column), $attrs);
	}

	/**
	 * Create a 'new x button'
	 *
	 * @param   string  $name   Button name
	 * @param   string  $title  Button title [Optional]
	 * @param   string  $url    Button URL [Optional]
	 *
	 * @return  string
	 *
	 * @uses    HTML::anchor
	 * @uses    HTML::sprite_img
	 * @uses    Request::uri
	 */
	public static function newButton($name, $title = NULL, $url = NULL)
	{
		if (is_null($url))
		{
			$url = Request::current()->uri(array('action' => 'add'));
		}

		if (is_null($title))
		{
			$title = HTML::sprite_img('add') . __('add :object', array(':object' => __($name)));
		}

		$out = HTML::anchor($url, $title, array('class' => 'button positive'));

		return $out;
	}

	/**
	 * Creates a multiselect form input
	 *
	 * @param   string  $name      Input name
	 * @param   array   $options   Available options [Optional]
	 * @param   array   $selected  Selected options [Optional]
	 * @param   array   $attrs     HTML attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses    HTML::attributes
	 */
	public static function multiselect($name, array $options = array(), $selected = NULL, array $attrs = NULL)
	{
		// Set the input name
		$attrs['name'] = $name;
		$attrs['multiple'] = 'multiple';

		if (empty($options))
		{
			// There are no options
			$options = '';
		}
		else
		{
			foreach ($options as $value => $name)
			{
				if (is_array($name))
				{
					// Create a new optgroup
					$group = array('label' => $value);

					// Create a new list of options
					$_options = array();

					foreach ($name as $_value => $_name)
					{
						// Create a new attribute set for this option
						$option = array('value' => $_value);

						if (in_array($_value, $selected))
						{
							// This option is selected
							$option['selected'] = 'selected';
						}

						// Sanitize the option title
						$title = htmlspecialchars($_name, ENT_NOQUOTES, Kohana::$charset, FALSE);

						// Change the option to the HTML string
						$_options[] = '<option'.HTML::attributes($option).'>'.$title.'</option>';
					}

					// Compile the options into a string
					$_options = PHP_EOL.implode(PHP_EOL, $_options).PHP_EOL;

					$options[$value] = '<optgroup'.HTML::attributes($group).'>'.$_options.'</optgroup>';
				}
				else
				{
					// Create a new attribute set for this option
					$option = array('value' => $value);

					if (in_array($value, $selected))
					{
						// This option is selected
						$option['selected'] = 'selected';
					}

					// Sanitize the option title
					$title = htmlspecialchars($name, ENT_NOQUOTES, Kohana::$charset, FALSE);

					// Change the option to the HTML string
					$options[$value] = '<option'.HTML::attributes($option).'>'.$title.'</option>';
				}
			}

			// Compile the options into a single string
			$options = PHP_EOL.implode(PHP_EOL, $options).PHP_EOL;
		}

		return '<select'.HTML::attributes($attrs).'>'.$options.'</select>';
	}

	/**
	 * Creates form radios
	 *
	 * @param   string  $name      Radios name
	 * @param   array   $options   Radios options [Optional]
	 * @param   mixed   $selected  Selected radio [Optional]
	 * @param   array   $attrs     Additional attributes [Optional]
	 *
	 * @return  string
	 *
	 * @uses  Text::plain
	 */
	public static function radios($name, array $options = array(), $selected = NULL, array $attrs = array())
	{
		if ( ! isset($attrs['class']))
		{
			$attrs['class'] = 'radio';
		}
		else
		{
			$attrs['class'] .= ' radio';
		}

		$out = '';

		foreach ($options as $k => $v)
		{
			$out .= self::label($name, self::radio($name, $k, ($selected == $k) ? TRUE : FALSE).Text::plain($v), $attrs);
		}

		return $out;
	}

	/**
	 * Creates form checkboxes
	 *
	 * @param   string  $name      Checkboxes name
	 * @param   array   $options   Checkboxes options [Optional]
	 * @param   array   $selected  Selected checkboxes [Optional]
	 * @param   array   $attrs     Additional attributes [Optional]
	 *
	 * @return  string
	 */
	public static function checkboxes($name, array $options = array(), array $selected = array(), array $attrs = array())
	{
		if ( ! isset($attrs['class']))
		{
			$attrs['class'] = ' checkbox';
		}
		else
		{
			$attrs['class'] .= ' checkbox';
		}

		$output = '';

		foreach ($options as $k => $v)
		{
			$output .= self::label($name, self::checkbox($name, $k, (in_array($k, $selected) ? TRUE : FALSE)).Text::plain($v), $attrs);
		}

		return $output;
	}

	/**
	 * Creates a select form input with raw labels
	 *
	 * Example:
	 * ~~~
	 * echo Form::select('country', $countries, $country);
	 * ~~~
	 *
	 * @param   string  $name      Input name
	 * @param   array   $options   Available options [Optional]
	 * @param   mixed   $selected  Selected option string, or an array of selected options [Optional]
	 * @param   array   $attrs     HTML attributes
	 *
	 * @return  string
	 *
	 * @uses    HTML::attributes
	 */
	public static function rawselect($name, array $options = NULL, $selected = NULL, array $attrs = array())
	{
		// Set the input name
		$attrs['name'] = $name;

		if (is_array($selected))
		{
			// This is a multi-select, god save us!
			$attrs['multiple'] = 'multiple';
		}

		if ( ! is_array($selected))
		{
			if ($selected === NULL)
			{
				// Use an empty array
				$selected = array();
			}
			else
			{
				// Convert the selected options to an array
				$selected = array( (string) $selected);
			}
		}

		if (empty($options))
		{
			// There are no options
			$options = '';
		}
		else
		{
			foreach ($options as $value => $name)
			{
				if (is_array($name))
				{
					// Create a new optgroup
					$group = array('label' => $value);

					// Create a new list of options
					$_options = array();

					foreach ($name as $_value => $_name)
					{
						// Force value to be string
						$_value = (string) $_value;

						// Create a new attribute set for this option
						$option = array('value' => $_value);

						if (in_array($_value, $selected))
						{
							// This option is selected
							$option['selected'] = 'selected';
						}

						// Change the option to the HTML string
						$_options[] = '<option'.HTML::attributes($option).'>'.$_name.'</option>';
					}

					// Compile the options into a string
					$_options = PHP_EOL.implode(PHP_EOL, $_options).PHP_EOL;

					$options[$value] = '<optgroup'.HTML::attributes($group).'>'.$_options.'</optgroup>';
				}
				else
				{
					// Force value to be string
					$value = (string) $value;

					// Create a new attribute set for this option
					$option = array('value' => $value);

					if (in_array($value, $selected))
					{
						// This option is selected
						$option['selected'] = 'selected';
					}

					// Change the option to the HTML string
					$options[$value] = '<option'.HTML::attributes($option).'>'.$name.'</option>';
				}
			}

			// Compile the options into a single string
			$options = PHP_EOL.implode(PHP_EOL, $options).PHP_EOL;
		}

		return '<select'.HTML::attributes($attrs).'>'.$options.'</select>';
	}

	/**
	 * Generates a valid HTML ID based the name.
	 *
	 * @param  string  $name   Element name
	 *
	 * @return string
	 */
	protected static function _get_id_by_name($name)
	{
		return 'form-'.str_replace(array('[]', '][', '[', ']', '\\'), array('', '_', '_', '', '_'), $name);
	}
}
