<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Form helper class
 *
 * @package		Gleez\Helper\Form
 * @author		Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2011-2013 Gleez Technologies
 * @license		http://gleezcms.org/license
 */
class Gleez_Form extends Kohana_Form {

	/**
	 * Creates a form input. If no type is specified, a "text" type input will
	 * be returned.
	 * <code>
	 *   echo Form::input('username', $username);
	 * </code>
	 *
	 * @param   string  input name
	 * @param   string  input value
	 * @param   array   html attributes
	 * @param   string  input url (autocomplete url)
	 * @param   bool    smart smart results listing
	 * @return  string
	 * @uses    HTML::attributes
	 */
	public static function input($name, $value = NULL, array $attributes = NULL, $url = FALSE, $smart = TRUE)
	{
		// Set the input name
		$attributes['name'] = $name;

		// Set the input value
		$attributes['value'] = $value;

		if ( ! isset($attributes['type']))
		{
			// Default type is text
			$attributes['type'] = 'text';
		}

		$out = '';

		if( $attributes['type'] === 'text' AND $url )
		{
			$attributes['class'] = isset($attributes['class']) ? $attributes['class'].' form-autocomplete' : 'form-autocomplete';
			$attributes['id'] = $name;
			// Assign the autocomplete js file
			Assets::js('autocomplete', 'media/js/autocomplete.js', 'gleez');

			$attributes['data-autocomplete-path'] = URL::site($url, TRUE);
			$attributes['data-autocomplete-smart'] = $smart;
		}

		$out .= '<input'.HTML::attributes($attributes).' />';

		return $out;
	}

	/**
	 * Creates CSRF token input.
	 *
	 * @param   string  $id      e.g. uid
	 * @param   string  $action  optional action
	 * @return  string
	 */
	public static function csrf($id = '', $action = '') {
		return Form::hidden('token', CSRF::token($id, $action));
	}

	/**
	 * Creates weight select field.
	 *
	 * @param   string   input name
	 * @param   int      selected option int
	 * @param   array    html attributes
	 * @param   int      delta
	 * @return  string
	 * @uses    Form::select
	 */
	public static function weight($name, $selected = 0, array $attributes = NULL, $delta = 15) {

		for ($n = (-1 * $delta); $n <= $delta; $n++)
		{
			$options[$n] = $n;
		}
		return Form::select($name, $options, $selected, $attributes);
	}

	/**
	 * create a form field for filtering
	 *
	 * @param string  $column
	 * @param array  $filtervals
	 * @return string
	 */
	public static function filter($column, $filtervals)
	{
		return Form::input("filter[$column]", Arr::get($filtervals, $column), array('style' => 'width:100%'));
	}

	/**
	 * Creates a submit form input.
	 *
	 *     echo Form::submit(NULL, 'Login');
	 *
	 * @param   string  input name
	 * @param   string  input value
	 * @param   array   html attributes
	 * @return  string
	 * @uses    Form::input
	 */
	public static function submit($name, $value, array $attributes = NULL)
	{
		$attributes['type'] = 'submit';

		return Form::input($name, $value, $attributes);
	}

	/**
	 * create a 'new x button'
	 *
	 * @param string  $name
	 * @param string  $title. (default: null)
	 * @param string  $url.   (default: null)
	 * @return void
	 */
	public static function newButton($name, $title = null, $url = null)
	{
		$url = ($url) ? $url : Request::current()->uri(array('action' => 'add'));
		$title = ($title) ? $title : HTML::sprite_img('add') . __('add :object', array(':object' => __($name)));

		return HTML::anchor($url, $title, array('class' => 'button positive'));
	}

	/**
	 * Generates an opening HTML form tag.
	 *
	 *     // Form will submit back to the current page using POST
	 *     echo Form::open();
	 *
	 *     // Form will submit to 'search' using GET
	 *     echo Form::open('search', array('method' => 'get'));
	 *
	 *     // When "file" inputs are present, you must include the "enctype"
	 *     echo Form::open(NULL, array('enctype' => 'multipart/form-data'));
	 *
	 * @param   mixed   form action, defaults to the current request URI, or [Request] class to use
	 * @param   array   html attributes
	 * @return  string
	 * @uses    Request::instance
	 * @uses    URL::site
	 * @uses    HTML::attributes
	 * @uses    ACL::csrf
	 * @uses    ACL::key
	 */
	public static function open($action = NULL, array $attributes = NULL)
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
		elseif (strpos($action, '://') === FALSE)
		{
			// Make the URI absolute
			$action = URL::site($action);
		}

		// Add the form action to the attributes
		$attributes['action'] = $action;

		//dynamically sets destination url to from action if exists in url
		if($desti = Request::current()->query('destination') AND ! empty($desti) )
		{
			//properly parse the path and query
			$url = URL::explode($action);

			//On seriously malformed URLs, parse_url() may return FALSE.
			if( isset($url['path']) AND is_array($url['query_params']) )
			{
				//add destination param
				$url['query_params']['destination'] = $desti;

				//set the form action parameter
				$attributes['action'] = $url['path'].URL::query($url['query_params']);
			}
		}

		// Only accept the default character set
		$attributes['accept-charset'] = Kohana::$charset;

		if ( ! isset($attributes['method']))
		{
			// Use POST method
			$attributes['method'] = 'post';
		}

		$out = "<form".HTML::attributes($attributes).">\n";

		if( Gleez::$installed )
		{
			// Assign the global form css file
			Assets::css('form', 'media/css/form.css', array('weight' => 2));

			$action  = md5($action.CSRF::key());
			$out 	.= Form::hidden('_token', CSRF::token(false, $action))."\n";
			$out 	.= Form::hidden('_action', $action)."\n";
		}

		return $out;
	}

	/**
	 * Creates a multiselect form input.
	 *
	 * @param   string   input name
	 * @param   array    available options
	 * @param   array    selected options
	 * @param   array    html attributes
	 * @return  string
	 */
	public static function multiselect($name, array $options = NULL, $selected = NULL, array $attributes = NULL)
	{
		// Set the input name
		$attributes['name'] = $name;
		$attributes['multiple'] = 'multiple';

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
					$_options = "\n".implode("\n", $_options)."\n";

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
			$options = "\n".implode("\n", $options)."\n";
		}

		return '<select'.HTML::attributes($attributes).'>'.$options.'</select>';
	}

        public static function radios($name, array $options = NULL, $selected = NULL, array $attributes = NULL)
        {
		if ( !isset($attributes['class']))
		{
			$attributes['class'] = 'radio';
		}
		else
		{
			$attributes['class'] .= ' radio';
		}

		$output = '';

                foreach ($options as $k => $v)
                {
                        $output .= Form::label($name, Form::radio($name, $k, ($selected == $k) ? TRUE : FALSE).Text::plain($v), $attributes);
                }
		return $output;
        }

	public static function checkboxes($name, array $options = NULL, array $selected = NULL, array $attributes = NULL)
	{
		if ( !isset($attributes['class']))
		{
			$attributes['class'] = ' checkbox';
		}
		else
		{
			$attributes['class'] .= ' checkbox';
		}

		if ($selected == NULL) $selected = array();

		$output = '';

                foreach ($options as $k => $v)
                {
                        $output .= Form::label($name, Form::checkbox($name, $k, (in_array($k, $selected) ? TRUE : FALSE)).Text::plain($v), $attributes);
                }
                return $output;
	}

	public static function mycheckbox($name, $option = NULL, $value = 0, $selected = NULL, array $attributes = NULL)
	{
		if ( !isset($attributes['class']))
		{
			$attributes['class'] = ' checkbox';
		}
		else
		{
			$attributes['class'] .= ' checkbox';
		}

		$output = '';
		$output .= Form::hidden($name, 0);
		$output .= Form::label($name, Form::checkbox( $name, $value, ( (($selected != 0) AND ($selected == $value)) ? TRUE : FALSE ) ).Text::plain($option), $attributes);

		return $output;
	}

	/**
	 * Creates a select form input with raw labels.
	 *
	 *     echo Form::select('country', $countries, $country);
	 *
	 * [!!] Support for multiple selected options was added in v3.0.7.
	 *
	 * @param   string   input name
	 * @param   array    available options
	 * @param   mixed    selected option string, or an array of selected options
	 * @param   array    html attributes
	 * @return  string
	 * @uses    HTML::attributes
	 */
	public static function rawselect($name, array $options = NULL, $selected = NULL, array $attributes = NULL)
	{
		// Set the input name
		$attributes['name'] = $name;

		if (is_array($selected))
		{
			// This is a multi-select, god save us!
			$attributes['multiple'] = 'multiple';
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
						$option['selected'] = 'selected';
					}

					// Change the option to the HTML string
					$options[$value] = '<option'.HTML::attributes($option).'>'.$name.'</option>';
				}
			}

			// Compile the options into a single string
			$options = "\n".implode("\n", $options)."\n";
		}

		return '<select'.HTML::attributes($attributes).'>'.$options.'</select>';
	}

}// End Form
