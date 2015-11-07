<?php

class Encrypt
{
    /**
     * Default instance name
     * @var  string
     */
    public static $default = 'default';

    /**
     * Encrypt class instances
     * @var array
     */
    public static $instances = [];

    /**
     * OS-dependent RAND type to use
     * @var string
     */
    protected static $rand;

    private $key;
    private $mode;
    private $cipher;
    private $ivSize;

    /**
     * Returns a singleton instance of Encrypt.
     *
     * An encryption key must be provided in your "encrypt" configuration file.
     *
     * <code>
     * $encrypt = Encrypt::instance();
     * </code>
     *
     * @param string $name Configuration group name [Optional]
     * @return mixed
     * @throws Gleez_Exception
     */
    public static function instance($name = null)
    {
        // Use the default instance name
        if (null === $name) {
            $name = static::$default;
        }

        if (!isset(static::$instances[$name])) {
            // Load the configuration data
            $config = Config::load('encrypt')->$name;

            if (!isset($config['key'])) {
                // No default encryption key is provided!
                throw new Gleez_Exception(
                    'No encryption key is defined in the encryption configuration group: :group',
                    [':group' => $name]
                );
            }

            if (!isset($config['mode'])) {
                // Add the default mode
                $config['mode'] = MCRYPT_MODE_NOFB;
            }

            if (!isset($config['cipher'])) {
                // Add the default cipher
                $config['cipher'] = MCRYPT_RIJNDAEL_128;
            }

            // Create a new instance
            static::$instances[$name] = new static($config['key'], $config['mode'], $config['cipher']);
        }

        return static::$instances[$name];
    }

    /**
     * Creates a new mcrypt wrapper.
     *
     * @param   string  $key    Encryption key
     * @param   string  $mode   The mcrypt mode
     * @param   string  $cipher The mcrypt cipher
     */
    public function __construct($key, $mode, $cipher)
    {
        // Find the max length of the key, based on cipher and mode
        $size = mcrypt_get_key_size($cipher, $mode);

        if (isset($key[$size])) {
            // Shorten the key to the maximum size
            $key = substr($key, 0, $size);
        }

        // Store the key, mode, and cipher
        $this->key    = $key;
        $this->mode   = $mode;
        $this->cipher = $cipher;

        // Store the IV size
        $this->ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
    }

    /**
     * Encrypts a string and returns an encrypted string that can be decoded.
     *
     * <code>
     * $data = $encrypt->encode($data);
     * </code>
     *
     * The encrypted binary data is encoded using [base64](http://php.net/base64_encode)
     * to convert it to a string. This string can be stored in a database,
     * displayed, and passed using most other means without corruption.
     *
     * @param  string $data Data to be encrypted
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function encode($data)
    {
        if (empty($data)) {
            return null;
        }

        if (!is_scalar($data)) {
            throw new InvalidArgumentException('The data to encrypt must be scalar type.');
        }

        // Set the rand type if it has not already been set
        if (null === static::$rand) {
            if (Gleez::$isWindows) {
                // Windows only supports the system random number generator
                static::$rand = MCRYPT_RAND;
            } else {
                if (defined('MCRYPT_DEV_URANDOM')) {
                    // Use /dev/urandom
                    static::$rand = MCRYPT_DEV_URANDOM;
                } elseif (defined('MCRYPT_DEV_RANDOM')) {
                    // Use /dev/random
                    static::$rand = MCRYPT_DEV_RANDOM;
                } else {
                    // Use the system random number generator
                    static::$rand = MCRYPT_RAND;
                }
            }
        }

        if (MCRYPT_RAND === static::$rand) {
            // The system random number generator must always be seeded each
            // time it is used, or it will not produce true random results
            mt_srand();
        }

        // Create a random initialization vector of the proper size for the current cipher
        $iv = mcrypt_create_iv($this->ivSize, static::$rand);

        // Encrypt the data using the configured options and generated iv
        $data = mcrypt_encrypt($this->cipher, $this->key, $data, $this->mode, $iv);

        // Use base64 encoding to convert to a string
        return base64_encode($iv . $data);
    }

    /**
     * Decrypts an encoded string back to its original value.
     *
     * <code>
     * $data = $encrypt->decode($data);
     * <code>
     *
     * @param  string  $data Encoded string to be decrypted
     * @return string|null
     * @throws InvalidArgumentException
     */
    public function decode($data)
    {
        if (empty($data)) {
            return null;
        }

        if (!is_scalar($data)) {
            throw new InvalidArgumentException('The data to decrypt must be a string.');
        }

        // Convert the data back to binary
        $data = base64_decode($data, true);

        if (!$data) {
            // Invalid base64 data
            return null;
        }

        // Extract the initialization vector from the data
        $iv = substr($data, 0, $this->ivSize);

        if ($this->ivSize !== strlen($iv)) {
            // The iv is not the expected size
            return null;
        }

        // Remove the iv from the data
        $data = substr($data, $this->ivSize);

        // Return the decrypted data, trimming the \0 padding bytes from the end of the data
        return rtrim(mcrypt_decrypt($this->cipher, $this->key, $data, $this->mode, $iv), "\0");
    }
}
