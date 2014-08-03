<?php
/*
 * @package		OAuth2 Module
 * @author      Pap Tamas
 * @copyright   (c) 2011-2014 Pap Tamas
 * @website		https://github.com/app-skeleton/oauth2
 * @license		http://opensource.org/licenses/MIT
 *
 */
abstract class OAuth2_Client {

    /**
     * Authorization types
     */
    const AUTH_TYPE_URI                 = 0;
    const AUTH_TYPE_AUTHORIZATION       = 1;
    const AUTH_TYPE_FORM                = 2;

    /**
     * Access token types
     */
    const TOKEN_TYPE_URI      = 0;
    const TOKEN_TYPE_BEARER   = 1;
    const TOKEN_TYPE_OAUTH    = 2;
    const TOKEN_TYPE_MAC      = 3;

    /**
    * Grant types
    */
    const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    const GRANT_TYPE_PASSWORD           = 'password';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    const GRANT_TYPE_REFRESH_TOKEN      = 'refresh_token';

    /**
     * HTTP Form content types
     */
    const HTTP_FORM_CONTENT_TYPE_APPLICATION    = 0;
    const HTTP_FORM_CONTENT_TYPE_MULTIPART      = 1;

    /**
     * HTTP Methods
     */
    const HTTP_METHOD_GET       = 'GET';
    const HTTP_METHOD_POST      = 'POST';
    const HTTP_METHOD_PUT       = 'PUT';
    const HTTP_METHOD_DELETE    = 'DELETE';
    const HTTP_METHOD_HEAD      = 'HEAD';
    const HTTP_METHOD_PATCH     = 'PATCH';

    /**
     * @var string  Client id
     */
    protected $_client_id = NULL;

    /**
     * @var string  Client secret
     */
    protected $_client_secret = NULL;

    /**
     * @var int     Client authentication type
     */
    protected $_client_auth_type = self::AUTH_TYPE_URI;

    /**
     * @var string  Access token
     */
    protected $_access_token = NULL;

    /**
     * @var int     Access token type
     */
    protected $_access_token_type = self::TOKEN_TYPE_URI;

    /**
     * @var string  Access token secret
     */
    protected $_access_token_secret = NULL;

    /**
     * @var string  Access token crypt algorithm
     */
    protected $_access_token_algorithm = NULL;

    /**
     * @var string  Access token parameter name
     */
    protected $_access_token_param_name = 'access_token';

    /**
     * @var string  The path to the certificate file to use for https connections
     */
    protected $_certificate_file = NULL;

    /**
     * @var array   cURL options
     */
    protected $_curl_options = array();

    /**
     * @var array   The last response from the OAuth server
     */
    protected $_last_response;

    /**
     * @var array   Required params for different grant types
     */
    protected $_required_params = array(
        'authorization_code'    => array('code', 'redirect_uri'),
        'password'              => array('username', 'password'),
        'refresh_token'         => array('refresh_token'),
        'client_credentials'    => array()
    );

    /**
     * Return the authorization endpoint
     *
     * @return  string
     */
    abstract function get_authorization_endpoint();

    /**
     * Return the access token endpoint
     *
     * @return  string
     */
    abstract function get_access_token_endpoint();

    /**
     * Return the user profile service url
     *
     * @return  string
     */
    abstract function get_user_profile_service_url();

    /**
     * Return data about the user
     *
     * @return  array
     */
    abstract function get_user_data();

    /**
     * Construct
     *
     * @param   string  $client_id
     * @param   string  $client_secret
     * @param   int     $client_auth_type
     * @param   string  $certificate_file
     * @throws  OAuth2_Client_Exception
     */
    public function __construct($client_id, $client_secret, $client_auth_type = self::AUTH_TYPE_URI, $certificate_file = NULL)
    {
        if ( ! extension_loaded('curl'))
        {
            throw new OAuth2_Client_Exception('The cURL extension must be installed.', array(), OAuth2_Client_Exception::E_NO_CURL_INSTALLED);
        }

        $this->_client_id           = $client_id;
        $this->_client_secret       = $client_secret;
        $this->_client_auth_type    = $client_auth_type;
        $this->_certificate_file    = $certificate_file;

        if ( ! empty($this->_certificate_file)  && ! is_file($this->_certificate_file))
        {
            throw new OAuth2_Client_Exception('The certificate file was not found.', array(), OAuth2_Client_Exception::E_CERTIFICATE_FILE_INVALID);
        }
    }

    /**
     * Get the client id
     *
     * @return  string
     */
    public function get_client_id()
    {
        return $this->_client_id;
    }

    /**
     * Get the client secret
     *
     * @return  string
     */
    public function get_client_secret()
    {
        return $this->_client_secret;
    }

    /**
     * Get the authentication url
     *
     * @param   string  $redirect_uri
     * @param   array   $extra_parameters
     * @return  string  URL used for authentication
     */
    public function get_authentication_url($redirect_uri, array $extra_parameters = array())
    {
        $parameters = array_merge(array(
            'response_type' => 'code',
            'client_id'     => $this->_client_id,
            'redirect_uri'  => $redirect_uri
        ), $extra_parameters);

        return $this->get_authorization_endpoint().'?'.http_build_query($parameters, NULL, '&');
    }

    /**
     * Request the access token
     *
     * @param   int     $grant_type         Grant Type ('authorization_code', 'password', 'client_credentials', 'refresh_token', or a custom code (@see GrantType Classes)
     * @param   array   $parameters         Array sent to the server (depend on which grant type you're using)
     * @return  array                       The server response
     * @throws  OAuth2_Client_Exception
     */
    public function request_access_token($grant_type, array $parameters)
    {
        if ( ! $grant_type)
        {
            throw new OAuth2_Client_Exception('The grant_type is mandatory.', array(), OAuth2_Client_Exception::E_NO_GRANT_TYPE_SPECIFIED);
        }

        foreach ($this->_required_params[$grant_type] as $param)
        {
            if ( ! isset($parameters[$param]))
                throw new OAuth2_Client_Exception('The ":param" parameter must be defined for ":grant_type" grant type.', array(
                    ':param' => $param,
                    ':grant_type' => $grant_type
                ), OAuth2_Client_Exception::E_MISSING_PARAMETER);
        }

        // Set grant type
        $parameters['grant_type'] = $grant_type;

        $http_headers = array();

        switch ($this->_client_auth_type)
        {
            case self::AUTH_TYPE_URI:
            case self::AUTH_TYPE_FORM:
                $parameters['client_id'] = $this->_client_id;
                $parameters['client_secret'] = $this->_client_secret;
                break;

            case self::AUTH_TYPE_AUTHORIZATION:
                $parameters['client_id'] = $this->_client_id;
                $http_headers['Authorization'] = 'Basic '.base64_encode($this->_client_id.':'.$this->_client_secret);
                break;

            default:
                throw new OAuth2_Client_Exception('Unknown client auth type ":client_auth_type".', array(
                    ':client_auth_type' => $this->_client_auth_type
                ), OAuth2_Client_Exception::E_UNKNOWN_AUTH_TYPE);
                break;
        }

        return $this->_execute_request($this->get_access_token_endpoint(), $parameters, self::HTTP_METHOD_POST, $http_headers, self::HTTP_FORM_CONTENT_TYPE_APPLICATION);
    }

    /**
     * Get the access token
     *
     * @param   string  $grant_type
     * @param   array   $parameters
     * @param   string  $response
     * @return  string
     * @throws  OAuth2_Client_Exception
     */
    public function get_access_token($grant_type, $parameters, $response = NULL)
    {
        $response = $response ?: $this->request_access_token($grant_type, $parameters);
        $result   = $response['result'];

        if ( ! is_array($result))
        {
            // Make sure `$result` is an array
            parse_str($result, $result);
        }

        if ( ! isset($result[$this->_access_token_param_name]))
        {
            throw new OAuth2_Client_Exception('Unable to get the access token.', array(), OAuth2_Client_Exception::E_CANT_GET_ACCESS_TOKEN);
        }

        // Return the access token
        return $result[$this->_access_token_param_name];
    }

    /**
     * Set the access token
     *
     * @param   string
     */
    public function set_access_token($token)
    {
        $this->_access_token = $token;
    }

    /**
     * Set the client authentication type
     *
     * @param   string  $client_auth_type   (AUTH_TYPE_URI, AUTH_TYPE_AUTHORIZATION, AUTH_TYPE_FORM)
     */
    public function set_client_auth_type($client_auth_type)
    {
        $this->_client_auth_type = $client_auth_type;
    }

    /**
     * Set an option for the curl transfer
     *
     * @param   int     $option
     * @param   mixed   $value
     */
    public function set_curl_option($option, $value)
    {
        $this->_curl_options[$option] = $value;
    }

    /**
     * Set multiple options for a cURL transfer
     *
     * @param   array   $options
     */
    public function set_curl_options($options)
    {
        $this->_curl_options = array_merge($this->_curl_options, $options);
    }

    /**
     * Set the access token type
     *
     * @param   int     $type       Access token type (ACCESS_TOKEN_BEARER, ACCESS_TOKEN_MAC, ACCESS_TOKEN_URI)
     * @param   string  $secret     The secret key used to encrypt the MAC header
     * @param   string  $algorithm  Algorithm used to encrypt the signature
     */
    public function set_access_token_type($type, $secret = NULL, $algorithm = NULL)
    {
        $this->_access_token_type = $type;
        $this->_access_token_secret = $secret;
        $this->_access_token_algorithm = $algorithm;
    }

    /**
     * Fetch a protected resource
     *
     * @param   string  $protected_resource_url
     * @param   array   $parameters
     * @param   string  $http_method
     * @param   array   $http_headers
     * @param   int     $form_content_type
     * @param   bool    $check_http_status
     * @param   int     $expected_http_status
     * @throws  OAuth2_Client_Exception
     * @return  array
     */
    public function fetch($protected_resource_url, $parameters = array(), $http_method = self::HTTP_METHOD_GET, array $http_headers = array(), $form_content_type = self::HTTP_FORM_CONTENT_TYPE_MULTIPART, $check_http_status = TRUE, $expected_http_status = 200)
    {
        if ($this->_access_token)
        {
            switch ($this->_access_token_type)
            {
                case self::TOKEN_TYPE_URI:
                    if ( ! is_array($parameters))
                        throw new OAuth2_Client_Exception('You need to give parameters as array if you want to give the token within the URI.', array(), OAuth2_Client_Exception::E_INCORRECT_PARAMETER);

                    $parameters[$this->_access_token_param_name] = $this->_access_token;
                    break;

                case self::TOKEN_TYPE_BEARER:
                    $http_headers['Authorization'] = 'Bearer '.$this->_access_token;
                    break;

                case self::TOKEN_TYPE_OAUTH:
                    $http_headers['Authorization'] = 'OAuth '.$this->_access_token;
                    break;

                case self::TOKEN_TYPE_MAC:
                    $http_headers['Authorization'] = 'MAC '.$this->_generate_mac_signature($protected_resource_url, $parameters, $http_method);
                    break;

                default:
                    throw new OAuth2_Client_Exception('Unknown access token type: ":access_token_type".', array(
                        ':access_token_type' => $this->_access_token_type
                    ), OAuth2_Client_Exception::E_UNKNOWN_ACCESS_TOKEN_TYPE);
                    break;
            }
        }

        $response = $this->_execute_request($protected_resource_url, $parameters, $http_method, $http_headers, $form_content_type);

        if ($check_http_status && $response['code'] != $expected_http_status)
        {
            throw new OAuth2_Client_Exception('Fetching ":resource" was unsuccessful. See the last server response for more details.', array(
                ':resource' => $protected_resource_url
            ), OAuth2_Client_Exception::E_FETCH_UNSUCCESSFUL);

        }

        return $response;
    }

    /**
     * Generate the MAC signature
     *
     * @param   string  $url
     * @param   array   $parameters
     * @param   string  $http_method
     * @return  string
     */
    protected function _generate_mac_signature($url, $parameters, $http_method)
    {
        $timestamp  = time();
        $nonce      = uniqid();
        $parsed_url = parse_url($url);

        if ( ! isset($parsed_url['port']))
        {
            $parsed_url['port'] = ($parsed_url['scheme'] == 'https') ? 443 : 80;
        }

        if ($http_method == self::HTTP_METHOD_GET)
        {
            if (is_array($parameters))
            {
                $parsed_url['path'] .= '?'.http_build_query($parameters, NULL, '&');
            }
            elseif ($parameters)
            {
                $parsed_url['path'] .= '?'.$parameters;
            }
        }

        $signature = base64_encode(hash_hmac($this->_access_token_algorithm,
            $timestamp."\n" .
            $nonce."\n" .
            $http_method."\n" .
            $parsed_url['path']."\n" .
            $parsed_url['host']."\n" .
            $parsed_url['port']."\n\n" .
            $this->_access_token_secret, TRUE));

        return 'id="'.$this->_access_token.'", ts="'.$timestamp.'", nonce="'.$nonce.'", mac="'.$signature.'"';
    }

    /**
     * Execute a request with cURL
     *
     * @param   string  $url
     * @param   mixed   $parameters
     * @param   string  $http_method
     * @param   array   $http_headers
     * @param   int     $form_content_type
     * @return  array
     * @throws  OAuth2_Client_Exception
     */
    protected function _execute_request($url, $parameters = array(), $http_method = self::HTTP_METHOD_GET, array $http_headers = NULL, $form_content_type = self::HTTP_FORM_CONTENT_TYPE_MULTIPART)
    {
        $curl_options = array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_CUSTOMREQUEST  => $http_method
        );

        switch ($http_method)
        {
            case self::HTTP_METHOD_POST:
                $curl_options[CURLOPT_POST] = TRUE;
                /* No break */

            case self::HTTP_METHOD_PUT:
			case self::HTTP_METHOD_PATCH:

                /**
                 * Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data,
                 * while passing a URL-encoded string will encode the data as application/x-www-form-urlencoded.
                 * http://php.net/manual/en/function.curl-setopt.php
                 */
                if (is_array($parameters) && self::HTTP_FORM_CONTENT_TYPE_APPLICATION === $form_content_type)
                {
                    $parameters = http_build_query($parameters, NULL, '&');
                }

                $curl_options[CURLOPT_POSTFIELDS] = $parameters;
                break;

            case self::HTTP_METHOD_HEAD:
                $curl_options[CURLOPT_NOBODY] = TRUE;
                /* No break */

            case self::HTTP_METHOD_DELETE:
            case self::HTTP_METHOD_GET:
                if (is_array($parameters))
                {
                    $url .= '?'.http_build_query($parameters, NULL, '&');
                }
                elseif ($parameters)
                {
                    $url .= '?'.$parameters;
                }
                break;

            default:
                break;
        }

        $curl_options[CURLOPT_URL] = $url;

        if (is_array($http_headers))
        {
            $header = array();
            foreach ($http_headers as $key => $parsed_url_value)
            {
                $header[] = "$key: $parsed_url_value";
            }
            $curl_options[CURLOPT_HTTPHEADER] = $header;
        }

        // Init cURL
        $ch = curl_init();

        // Set CURL options
        curl_setopt_array($ch, $curl_options);

        // Https handling
        if ( ! empty($this->certificate_file))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $this->certificate_file);
        }
        else
        {
            // Bypass SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        if ( ! empty($this->curl_options))
        {
            curl_setopt_array($ch, $this->curl_options);
        }

        //Github checks for useragent header
        curl_setopt($ch, CURLOPT_USERAGENT, Template::getSiteName());

        $result       = curl_exec($ch);
        $http_code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if ($curl_error = curl_error($ch))
        {
            throw new OAuth2_Client_Exception($curl_error, array(), OAuth2_Client_Exception::E_CURL_ERROR);
        }
        else
        {
            $json_decode = json_decode($result, TRUE);
        }

        curl_close($ch);

        return $this->_last_response = array(
            'result'        => ($json_decode === NULL) ? $result : $json_decode,
            'code'          => $http_code,
            'content_type'  => $content_type
        );
    }

    /**
     * Return the last response from the OAuth server
     *
     * @return array
     */
    public function get_last_response()
    {
        return $this->_last_response;
    }

    /**
     * Set the name of the parameter that carry the access token
     *
     * @param   string  $name
     */
    public function set_access_token_param_name($name)
    {
        $this->_access_token_param_name = $name;
    }

    /**
     * OAuth2 client factory for different providers
     *
     * @param   string  $provider
     * @param   string  $client_id
     * @param   string  $client_secret
     * @param   int     $client_auth_type
     * @param   string  $certificate_file
     * @return  mixed
     */
    public static function factory($provider, $client_id, $client_secret, $client_auth_type = self::AUTH_TYPE_URI, $certificate_file = NULL)
    {
        $class_name = 'OAuth2_Client_'.$provider;
        return new $class_name($client_id, $client_secret, $client_auth_type, $certificate_file);
    }
}
