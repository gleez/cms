<?php
/**
 * OAuth v2 Client
 *
 * @package    Gleez\OAuth
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license
 */
class OAuth2_Client {

	/**
	 * Create a new consumer object.
	 *
	 *     $consumer = OAuth2_Client::factory($options);
	 *
	 * @param   array  consumer options, key and secret are required
	 * @return  OAuth_Consumer
	 */
	public static function factory(array $options = NULL)
	{
		return new OAuth2_Client($options);
	}

	/**
	 * @var  string  client id
	 */
	protected $id;

	/**
	 * @var  string  client secret
	 */
	protected $secret;

	/**
	 * @var  string  callback URL for OAuth authorization completion
	 */
	protected $callback;

	/**
	 * @var  string  scope URL for OAuth authorization completion
	 */
	protected $scope = array();

	/**
	 * Sets the consumer key and secret.
	 *
	 * @param   array  consumer options, key and secret are required
	 * @return  void
	 */
	public function __construct(array $options = NULL)
	{
		if ( ! isset($options['id']))
		{
			throw new OAuth2_Exception('Required option not passed: :option',
				array(':option' => 'id'));
		}

		if ( ! isset($options['secret']))
		{
			throw new OAuth2_Exception('Required option not passed: :option',
				array(':option' => 'secret'));
		}

		$this->id = $options['id'];

		$this->secret = $options['secret'];

		if (isset($options['callback']))
		{
			$this->callback = $options['callback'];
		}

		if (isset($options['scope']))
		{
			$this->scope = $options['scope'];
		}
	}

	/**
	 * Return the value of any protected class variable.
	 *
	 *     // Get the client key
	 *     $key = $client->key;
	 *
	 * @param   string  variable name
	 * @return  mixed
	 */
	public function __get($key)
	{
		return $this->$key;
	}

	/**
	 * Change the client callback.
	 *
	 * @param   string  new consumer callback
	 * @return  $this
	 */
	public function callback($callback)
	{
		$this->callback = $callback;

		return $this;
	}

	/**
	 * Change the client scope.
	 *
	 * @param   string  new consumer scope
	 * @return  $this
	 */
	public function scope( $scope)
	{
		$this->scope = array( 'scope' => $scope );

		return $this;
	}
}
