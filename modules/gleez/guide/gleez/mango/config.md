# Gleez Mango configuration

## Introduction

Gleez Mango uses configuration groups to create [MongoDB](http://www.mongodb.org) instances.

The default configuration group is loaded based on the `Mango::$default` setting.
It is set to the `default` group as standard, however this can be changed within the `/application/boostrap.php` file:

    // Change the default config group
    Mango::$default = 'mygroup';

[!!] Please note that you can change configuration group only after enabling Gleez Mango module.

## Group settings

Below are the default MongoDB configuration groups.

Name           | Required | Description
-------------- | -------- | ---------------------------------------------------------------
connection     | __YES__  | (_array_) Connection Setup
profiling      | __NO__   | (_boolean_) Whether or not to use profiling
collection     | __NO__   | (_string_) You can override the class name for the MongoCollection wrapper