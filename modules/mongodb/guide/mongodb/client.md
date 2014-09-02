# Introduction

This class wraps the functionality of \MongoClient (connection) and \MongoDB (database object) into one `\Gleez\Mango\Client` class and can be instantiated simply by:

~~~
$db = \Gleez\Mango\Client::instance();
~~~

The above will assume the 'default' configuration from the `config/mongodb.php` file. Alternatively it may be instantiated with the name and configuration specified as arguments:

~~~
$db = \Gleez\Mango\Client::instance('test', array(
    'test' => array(
        'connection' => array(
            'hostnames'  => 'mongodb://whisky:13000/?replicaset=seta',
            'options'    => array(
                'db'       => 'MyDB',
                'username' => 'username',
                'password' => 'password',
                // ...
            )
        ),
        'profiling' => true,
        // ...
    )
));
~~~

The `\Gleez\Mango\Collection` class will gain access to the server by calling the instance method with a configuration name, so if the configuration name is not present in the config file then the instance should be created before using any classes that extend `\Gleez\Mango\Collection`.

Client can proxy all methods of \MongoDB to the database instance as well as select collections using the `\Gleez\Mango\Client::__get` magic method and allows for easy benchmarking if profiling is enabled.
