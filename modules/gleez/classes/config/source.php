<?php defined('SYSPATH') OR die('No direct script access.');

interface Config_Source
{
    public function load($group);
    public function write($group, $key, $value);
    public function delete($group, $key);
}