# Introduction

`\Gleez\Mangp\Collection` — this class can be used directly as a wrapper for \MongoCollection and \MongoCursor.

Usage:
~~~
$collection = new \Gleez\Mango\Collection('users');

// $users now is array of arrays
$users = $collection->sortDesc('published')
                     ->limit(10)
                     ->toArray();
~~~
