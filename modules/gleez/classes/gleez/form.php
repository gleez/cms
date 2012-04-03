<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Form helper class.
 *
 * @package	Gleez
 * @category	Form
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Form extends Kohana_Form {

	/**
	 * Creates a form input. If no type is specified, a "text" type input will
	 * be returned.
	 *
	 *     echo Form::input('username', $username);
	 *
	 * @param   string  input name
	 * @param   string  input value
	 * @param   array   html attributes
	 * @param   string  input url (autocomplete url)
	 * @return  string
	 * @uses    HTML::attributes
	 */
	public static function input($name, $value = NULL, array $attributes = NULL, $url = FALSE)
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
		
			$attr = array();
			$attr['type'] = 'hidden';
			$attr['id'] = $name . '-autocomplete';
			$attr['value'] = URL::site($url, TRUE);
			$attr['disabled'] = 'disabled';
			$attr['class'] = 'autocomplete';
			$out .= Form::hidden($attr['id'], $attr['value'], $attr);
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
	 * @access public
	 * @static
	 * @param string  $column
	 * @param array  $filtervals
	 * @return string
	 */
	public static function filter($column, $filtervals)
	{
		return Form::input("filter[$column]", Arr::get($filtervals, $column), array('style' => 'width:100%'));
	}
	
	/**
	 * create a 'new x button'
	 *
	 * @access public
	 * @static
	 * @param string  $name
	 * @param string  $title. (default: null)
	 * @param string  $url.   (default: null)
	 * @return void
	 */
	public static function newButton($name, $title = null, $url = null)
	{
		$url = ($url) ? $url : Request::current()->uri(array('action' => 'add'));
		$title = ($title) ? $title : Gleez::spriteImg('add') . __('add :object', array(':object' => __($name)));

		return Html::anchor($url, $title, array('class' => 'button positive'));

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

}
