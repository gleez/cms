# Mango Reader

Module monitors system events and recording them in a log using [MongoDB DBMS] (http://www.mongodb.org)


## Description

**Mango Reader** is a [Gleez] (http://gleezcms.org/) module and it's a simple object wrapper for the Mongo PHP driver.
It  monitors your website, capturing system events in a log to be reviewed by an authorized individual at a later time.
The log is simply a list of recorded events containing usage data, performance data, errors, warnings and operational information.
It is vital to check the log report on a regular basis as it is often the only way to tell what is going on.


## Available versions

- **0.1.1.0** for Gleez CMS 0.9.8.1 or higher

## Features

- View list of all events
- View single log event
- Delete event from log


## Future Plans

- Divide the **Mango_Database Class** into the following three:
 - **Mango_Database Class** — database and connection managing
 - **Mango_Collection Class** — collection managing
 - **Mango_Document Class** — document managing
- Implement profiling
- Special version for Kohana Framework 3.2.x
- Special version for Kohana Framework 3.3.x
- Special version for Kohana Framework 3.4.x
- More pure and correct English in the documentation and the string resources


## Installation & Usage

- Include Mango Reader into your module path. For example:
```php
  /**
   * Enable modules. Modules are referenced by a relative or absolute path.
   */
  Kohana::modules(array(
    'gleez'     => MODPATH.'gleez',      // Gleez Core Module
    'user'      => MODPATH.'user',       // User and group Administration
    'cache'     => MODPATH.'cache',      // Caching with multiple backends
    'database'  => MODPATH.'database',   // Database access
    'image'     => MODPATH.'image',      // Image manipulation
    'captcha'   => MODPATH.'captcha',    // Captcha implementation
    'unittest'  => MODPATH.'unittest',   // Unit testing
    'codebench' => MODPATH.'codebench',  // Benchmarking tool
    'userguide' => MODPATH.'userguide',  // User guide and API documentation
    'mango'     => MODPATH.'mango',      // Mango Reader module
  ));
```

- Attach the MangoDB write to logging:
```php
  // Kohana::$log->attach(new Gleez_Log_File(APPPATH.'logs'));
  Kohana::$log->attach(new Log_Mango());
```

- For Routes see `MODPATH/<mango_dir>/init.php`

- See `MODPATH/<mango_dir>/config/mango.php` and create `APPATH/config/mango.php` for your individual settings


## Contributors

- [Sergey Yakovlev] (https://github.com/sergeyklay) - Code

Now that you're here, why not start contributing as well? :)


##  Special thanks to

- [sign] (https://github.com/sergey-sign) - Code
- [sandeepone] (https://github.com/sandeepone) - Gleez Team


## Changelog

**0.1.1.0** - *January 17 2013*

- Initial release
