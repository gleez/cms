# Mango Reader

_Module that monitors all system events and recording them in a log using [MongoDB DBMS] (http://www.mongodb.org)!_


## Overview

**MongoDB** (from "hu**mongo**us") is an open source document-oriented database system developed and supported by
[10gen] (http://www.10gen.com/). It is part of the NoSQL family of database systems. Instead of storing data in
tables as is done in a "classical" relational database, MongoDB stores structured data as JSON-like documents with
dynamic schemas (MongoDB calls the format BSON), making the integration of data in certain types of applications
easier and faster.


## Description

**Mango Reader** is a [Gleez CMS] (http://gleezcms.org/) module and it's a simple object wrapper for the
[Mongo PHP] (http://php.net/manual/en/book.mongo.php) driver. It monitors your website, capturing system
events in a log to be reviewed by an authorized individual at a later time. The log is simply a list of
recorded events containing usage data, performance data, errors, warnings and operational information.

It is vital to check the log report on a regular basis as it is often the only way to tell what is going on.


## Current available versions

- **0.1.1.1** for Gleez CMS 0.9.8.1 or higher [Download] (https://github.com/sergeyklay/gleez-mango/archive/master.zip)


## System Requirements

- [PHP] (http://php.net/) 5.3 or higher
- [PHP-extension] (http://php.net/manual/en/mongo.installation.php) MongoDB 1.3 or higher
- [Gleez CMS] (http://gleezcms.org/) 0.9.8.1 or higher
- ACL (optional for module specific permissions)


## Features

- View list of all events
- View single log event
- Delete event from log
- Drop system log


## Future Plans

- Divide the `Mango_Database` Class into the following three:
 - `Mango_Database` Class: Database and connection managing
 - `Mango_Collection` Class: Collection managing
 - `Mango_Document` Class: Document managing
- Implement Profiling
- Implement Session Storage *(in the long term)*
- More pure and correct English in the documentation and the string resources


## Installation & Usage

- [Download] (https://github.com/sergeyklay/gleez-mango/archive/master.zip) module from its GitHub [homepage] (https://github.com/sergeyklay/gleez-mango) 

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
  // Disable logging into files
  // Kohana::$log->attach(new Gleez_Log_File(APPPATH.'logs'));

  // Enable logging into MongoDB database
  Kohana::$log->attach(new Log_Mango());
```

- For Routes see `MODPATH/<mango_dir>/init.php`

- Use `MODPATH/<mango_dir>/config/mango.php` as an example for creating `APPATH/config/mango.php` with your individual settings


## Contributors

- [Sergey Yakovlev] (https://github.com/sergeyklay) - Code

Now that you're here, why not start contributing as well? :)


##  Special thanks to

- [sign] (https://github.com/sergey-sign) - Code
- [sandeepone] (https://github.com/sandeepone) - Gleez Team


## Changelog

**0.1.1.1** - *January 19 2013*

- Added I18n ability
- Added ability to clear all messages from the log
- Minor changes (see commits diff)

**0.1.1.0** - *January 17 2013*

- Initial release
