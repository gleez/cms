<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Form helper class
 *
 * @package    Gleez\Helpers
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Gleez_Form extends Kohana_Form {

	/**
	 * Creates a form input
	 *
	 * If no type is specified, a "text" type input will be returned.
	 *
	 * Example:<br>
	 * <code>
	 *   echo Form::input('username', $username);
	 * </code>
	 *
	 * @param   string  $name   Input name
	 * @param   string  $value  Input value [Optional]
	 * @param   array   $attrs  HTML attributes [Optional]
	 * @param   string  $url    Input url (autocomplete url) [Optional]
	 * @param   boolean $smart  Smart smart results listing [Optional]
	 * @return  string  HTML form input
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

		$out = '';

		if ($attrs['type'] === 'text' AND ! empty($url))
		{
			$attrs['class'] = isset($attrs['class']) ? $attrs['class'].' form-autocomplete' : 'form-autocomplete';
			$attrs['id'] = $name;
			$attrs['autocomplete'] = "off";

			// Assign the autocomplete js file
			Assets::js('autocomplete', 'media/js/autocomplete.js', 'gleez');

			$attrs['data-url'] = URL::site($url, TRUE);
			//$attrs['data-autocomplete-smart'] = $smart;
		}

		$out .= '<input'.HTML::attributes($attrs).'>';

		return $out;
	}

	/**
	 * Creates CSRF token input
	 *
	 * @param   string  $id      ID e.g. uid [Optional]
	 * @param   string  $action  Action [Optional]
	 * @return  string
	 *
	 * @uses    CSRF::token
	 */
	public static function csrf($id = '', $action = '')
	{
		return Form::hidden('token', CSRF::token($id, $action));
	}

	/**
	 * Creates weight select field
	 *
	 * @param   string   $name      Input name
	 * @param   integer  $selected  Selected option int [Optional]
	 * @param   array    $attrs     HTML attributes [Optional]
	 * @param   integer  $delta     Delta [Optional]
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

		return Form::select($name, $options, $selected, $attrs);
	}

	/**
	 * Create a form field for filtering
	 *
	 * @param   string $column  Column
	 * @param   array  $vals    Filter values
	 * @param   array  $attrs   Filter attributes [Optional]
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

		return Form::input("filter[$column]", Arr::get($vals, $column), $attrs);
	}

	/**
	 * Creates a submit form input
	 *
	 * Example:<br>
	 * <code>
	 * 	echo Form::submit(NULL, 'Login');
	 * </code>
	 *
	 * @param   string  $name   Input name
	 * @param   string  $value  Input value
	 * @param   array   $attrs  HTML attributes [Optional]
	 * @return  string
	 */
	public static function submit($name, $value, array $attrs = array())
	{
		$attrs['type'] = 'submit';

		return Form::input($name, $value, $attrs);
	}

	/**
	 * Creates a button
	 *
	 * Example:<br>
	 * <code>
	 * 	echo Form::button('login', 'Login', array('class' => 'pull-right'));
	 * </code>
	 *
	 * @param   string  $name     Button name
	 * @param   string  $caption  Button caption
	 * @param   array   $attrs    HTML attributes [Optional]
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

		$out = '<button '.HTML::attributes($attrs).'>'.$caption.'</button>';

		return $out;
	}

	/**
	 * Create a 'new x button'
	 *
	 * @param   string  $name   Button name
	 * @param   string  $title  Button title [Optional]
	 * @param   string  $url    Button URL [Optional]
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
	 * Generates an opening HTML form tag
	 *
	 * #### Usage
	 *
	 * Form will submit back to the current page using POST:<br>
	 * <code>
	 * 	echo Form::open();
	 * </code>
	 *
	 * Form will submit to 'search' using GET:<br>
	 * <code>
	 *	echo Form::open('search', array('method' => 'get'));
	 * </code>
	 *
	 * When "file" inputs are present, you must include the "enctype":<br>
	 * <code>
	 * 	echo Form::open(NULL, array('enctype' => 'multipart/form-data'));
	 * </code>
	 *
	 * @param   mixed  $action  Form action, defaults to the current request URI, or Request class to use [Optional]
	 * @param   array  $attrs   HTML attributes [Optional]
	 * @return  string
	 *
	 * @see     Request
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

		if ( ! isset($attributes['method']))
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
			$out 	.= Form::hidden('_token', CSRF::token(FALSE, $action)).PHP_EOL;
			$out 	.= Form::hidden('_action', $action).PHP_EOL;
		}

		return $out;
	}

	/**
	 * Creates a multiselect form input
	 *
	 * @param   string  $name      Input name
	 * @param   array   $options   Available options [Optional]
	 * @param   array   $selected  Selected options [Optional]
	 * @param   array   $attrs     HTML attributes [Optional]
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
			$out .= Form::label($name, Form::radio($name, $k, ($selected == $k) ? TRUE : FALSE).Text::plain($v), $attrs);
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
			$output .= Form::label($name, Form::checkbox($name, $k, (in_array($k, $selected) ? TRUE : FALSE)).Text::plain($v), $attrs);
		}

		return $output;
	}

	/**
	 * Creates a select form input with raw labels
	 *
	 * Example:<br>
	 * <code>
	 * 	echo Form::select('country', $countries, $country);
	 * </code>
	 *
	 * @param   string  $name      Input name
	 * @param   array   $options   Available options [Optional]
	 * @param   mixed   $selected  Selected option string, or an array of selected options [Optional]
	 * @param   array   $attrs     HTML attributes
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
}
