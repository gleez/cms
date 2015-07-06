<?php

if (!class_exists('Kohana')) {
    throw new RuntimeException('Please include the Gleez bootstrap file (see README.md)');
}

if ($file = Kohana::find_file('classes', 'unittest/tests')) {
    require_once $file;

    // PHPUnit requires a test suite class to be in this file,
    // so we create a faux one that uses the kohana base
    class TestSuite extends Unittest_Tests
    {}
} else {
    throw new RuntimeException('Could not include the test suite');
}
