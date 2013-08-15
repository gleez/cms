# Introduction

The __Gleez Log__ component monitors your website, capturing system events in a log to be reviewed by an authorized individual at a later time. The log is simply a list of recorded events containing usage data, performance data, errors, warnings and operational information. It is vital to check the log report on a regular basis as it is often the only way to tell what is going on.

## Supported Log Writers

+ __File__ - Writes out messages and stores them in a YYYY/MM directory
+ __STDERR__ - Writes out messages to STDERR
+ __STDOUT__ - Writes out messages to STDOUT
+ __Syslog__ - Writes out messages to syslog
+ __Mango__ -  Writes out messages to with using [MongoDB DBMS](http://www.mongodb.org)

## Usage

You can specify the log writer at bootstrap time in `/application/bootstrap.php` file:

~~~
// Using file log writer
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

// Using STDERR log writer
Kohana::$log->attach(new Log_StdErr());

// Using STDOUT log writer
Kohana::$log->attach(new Log_StdOut());

// Using Syslog log writer
Kohana::$log->attach(new Log_Syslog());

// Using Gleez Mango Log
Kohana::$log->attach(new Log_Mango());
~~~

Multiple writers are supported.

## Mango Log Writer

Mango Log Writer uses __Gleez Mango__ component. In its turn __Gleez Mango__ uses configuration groups to create MongoDB instances.

The default configuration group is loaded based on the `Mango::$default` setting.
It is set to the `default` group as standard, however this can be changed within the `/application/bootstrap.php` file:

~~~
// Change the default config group
Mango::$default = 'mygroup';

// Using Gleez Mango Log
Kohana::$log->attach(new Log_Mango());
~~~

### Mango Settings

Below are the default Mango configuration group setting:

Name           | Required | Description
-------------- | -------- | ---------------------------------------------------------------
connection     | __YES__  | (_array_) Connection Setup
profiling      | __NO__   | (_boolean_) Whether or not to use profiling. If enabled, profiling data will be shown through Gleez profiler library
collection     | __NO__   | (_string_) You can override the class name for the MongoCollection wrapper. By default using `Mango::$_collection_class` it `Mango_Collection`

The following options are available for __connection__ group:

Name           | Required | Description
-------------- | -------- | ---------------------------------------------------------------
hostnames      | __NO__   | (_string_) Server hostname, or socket. Separate multiple hosts by commas. __FALSE__ and '' are identical.
options        | __YES__  | (_array_) Options array. Only __db__ param is required

The following extra options are available for __options__ group:

Name             | Required | Description
---------------- | -------- | ---------------------------------------------------------------
db               | __YES__  | (_string_) Database to connect to. Cannot contain spaces, dots or be the empty string. The name `system` is also reserved.
connectTimeoutMS | __NO__   | (_integer_) Default timeout. It is measured in milliseconds.
connect          | __NO__   | (_boolean_) Connect to DB on creation connection. How do you want to deal with connection errors? __TRUE__ - `Mango::instance` fails and an exception is thrown. Next call to `Mango::instance` will try to connect again. __FALSE__ - Exception is thrown when you run first DB action. Next call to `Mango::instance` will return same object.
username         | __NO__   | (_string_) Database username. __FALSE__ and '' are identical.
password         | __NO__   | (_string_) Database password. __FALSE__ and '' are identical.
replicaSet       | __NO__   | (_string_) The name of the replica set to connect to. __FALSE__ and '' are identical.
w                | __NO__   | (_mixed_) When a write is given a [Write Concern](http://www.php.net/manual/ru/mongo.writeconcerns.php) option ("w") the driver will send the query to MongoDB and piggy back a [getLastError](http://docs.mongodb.org/manual/core/write-operations/) command (GLE) with the Write Concern option at the same time.