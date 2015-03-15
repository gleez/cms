Gleez CMS v2 (Bocydium Globulare)
=================================

### What is Gleez CMS

[Gleez CMS] [gleez-site] is a user-friendly website [Content Management System] [wiki-cms].

**Work In Progress. Do not use this version in production!**

### Directory Structure

```
app/                 ready-to-use application template
config/              application configs
public/              public document root
build/               internally used build tools
tests/               tests of the core Gleez CMS code
vendor/              vendor libraries (Composer install path)
```

### System Requirements

The minimum requirement by Gleez CMS is that your Web server supports PHP 5.3.9. But requirements may change at any time prior to 5.4 

* PHP >= 5.3.9

Additional requirements

* PHPUnit >= 4.0.0 (for testing)

### Tests

```sh
$ cd tests
$ phpunit \
--coverage-text \
--coverage-clover ../build/logs/clover.xml \
--coverage-html   ../build/report
```

For additional information see [PHPUnit The Command-Line Test Runner] [php-unit]

### License

Licensed under the [Gleez CMS License] [license]. © Gleez Technologies


[gleez-site]: http://gleezcms.org/
[wiki-cms]: http://en.wikipedia.org/wiki/CMS
[php-unit]: http://phpunit.de/manual/current/en/textui.html
[license]: http://gleezcms.org/license

