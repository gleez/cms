<?php
/**
 * @package    Gleez/Tests/Codebench
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Bench_ValidURL extends Codebench {

	public $description = 'filter_var vs regex';

	public $loops = 1000;

	public $subjects = array
	(
		// Valid
		'http://google.com',
		'http://google.com/',
		'http://google.com/?q=abc',
		'http://google.com/#hash',
		'http://localhost',
		'http://hello-world.pl',
		'http://hello--world.pl',
		'http://h.e.l.l.0.pl',
		'http://server.tld/get/info',
		'http://127.0.0.1',
		'http://127.0.0.1:80',
		'http://user@127.0.0.1',
		'http://user:pass@127.0.0.1',
		'ftp://my.server.com',
		'rss+xml://rss.example.com',

		// Invalid
		'http://google.2com',
		'http://google.com?q=abc',
		'http://google.com#hash',
		'http://hello-.pl',
		'http://hel.-lo.world.pl',
		'http://ww£.google.com',
		'http://127.0.0.1234',
		'http://127.0.0.1.1',
		'http://user:@127.0.0.1',
		"http://finalnewline.com\n",
	);

	public function bench_filter_var($url)
	{
		return (bool) filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
	}

	public function bench_regex($url)
	{
		return Valid::url($url);
	}
}
