Gleez CMS v2
============

### What is Gleez CMS

[Gleez CMS] [gleez-site] is a user-friendly website [Content Management System] [wiki-cms].

**Work In Progress. Do not use this version in production!**

### System Requirements

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

