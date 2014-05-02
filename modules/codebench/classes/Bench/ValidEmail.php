<?php
/**
 * @package    Gleez/Tests/Codebench
 * @author     Gleez Team
 * @copyright  (c) 2011-2014 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
class Bench_ValidEmail extends Codebench {

	public $description = 'filter_var vs regex';

	public $loops = 1000;

	public $subjects = array
	(
		// Valid
		'john@doe.com',
		'john.doe@site.com',
		'john_doe@site.com',
		'john_doe@domain.site.com',
		'john@doe-site.com',
		'j.o.h.n@d.o.e.com',
		'isposable.style.email.with+symbol@example.com',
		'sandeep@localhost', // FILTER_VALIDATE_EMAIL not works correctly
		'j.o.h.n@my.c',

		// Invalid
		'A@b@c@example.com',
		'a"b(c)d,e:f;gi[j\k]l@example.com',
		'just"not"right@example.com',
		'this is"not\allowed@example.com',
		'this\ still\"not\allowed@example.com'
	);

	public function bench_filter_var($email)
	{
		return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public function bench_regexNoStrict($email)
	{
		return Valid::email($email, FALSE);
	}

	public function bench_regexStrict($email)
	{
		return Valid::email($email, TRUE);
	}
}
