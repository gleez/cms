<?php
/**
 * OAuth v2  Token
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
abstract class OAuth2_Token {

	/**
	 * @var  string  token type name: request, access
	 */
	protected $name;

	/**
	 * @var  string  token key
	 */
	protected $token;

	/**
	 * @var  string  required parameters
	 */
	protected $required = array(
		'token',
	);

	/**
	 * Create a new token object.
	 *
	 *     $token = OAuth2_Token::factory($name);
	 *
	 * @param   string  token type
	 * @param   array   token options
	 * @return  OAuth2_Token
	 */
	public static function factory($name, array $options = NULL)
	{
		$class = 'OAuth2_Token_'.$name;

		return new $class($options);
	}

	/**
	 * Sets the token and secret values.
	 *
	 * @param   array   token options
	 * @return  void
	 */
	public function __construct(array $options = NULL)
	{
		foreach ($this->required as $key)
		{
			if ( ! isset($options[$key]))
			{
				throw new OAuth2_Exception('Required option not passed: :option',
					array(':option' => $key));
			}

			$this->$key = $options[$key];
		}
	}

	/**
	 * Return the value of any protected class variable.
	 *
	 *     // Get the token secret
	 *     $secret = $token->secret;
	 *
	 * @param   string  variable name
	 * @return  mixed
	 */
	public function __get($key)
	{
		return $this->$key;
	}

	/**
	 * Returns the token key.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return (string) $this->token;
	}

}
