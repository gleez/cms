#Version for 3.2

This version was forked from 3.3/master, and classes names replaced from uppercase to lowercase in 3.2 style.
Class names changes too.

To ran tasks you need also add this to your Kohana 3.2 bootstrap.php file:

    if (PHP_SAPI == 'cli') // Try and load minion
    {
        class_exists('Minion_Task') OR die('Please enable the Minion module for CLI support.');
        set_exception_handler(array('Minion_Exception', 'handler'));

        Minion_Task::factory(Minion_CLI::options())->execute();
    }
    else
    {

        echo Request::factory()
                ->execute()
                ->send_headers(TRUE)
                ->body();
    }

And now you can use it like 3.3 minion module.

    class Task_Welcome extends Minion_Task {
        protected function _execute(array $params)
        {
            Minion_CLI::write('hello world!');
        }
    }

./minion --task=welcome

---------------

# Minion

Minion is a framework for running tasks via the CLI.

The system is inspired by ruckusing, which had a nice system for defining tasks but lacked the desired flexibility for kohana integration.

## Getting Started

First off, download and enable the module in your bootstrap

Then copy the bash script `minion` alongside your index.php (most likely the webroot).
If you'd rather the executable be in a different location to index.php then simply modify the bash script to point to index.php.

You can then run minion like so:

	./minion {task}

To view a list of minion tasks, run minion without any parameters, or with the `--help` option

	./minion
	./minion --help

To view help for a specific minion task run

	./minion {task} --help

For security reasons Minion will only run from the cli.  Attempting to access it over http will cause
a `Kohana_Exception` to be thrown.

If you're unable to use the binary file for whatever reason then simply replace `./minion {task}` in the above
examples with

	php index.php --uri=minion --task={task}

## Writing your own tasks

All minion tasks must be located in `classes/task/`.  They can be in any module, thus allowing you to
ship custom minion tasks with your own module / product.

Each task must extend the abstract class `Minion_Task` and implement `Minion_Task::_execute()`.

See `Minion_Task` for more details.

## Documentation

Code should be commented well enough not to need documentation, and minion can extract a class' doccomment to use
as documentation on the cli.

## Testing

This module is unittested using the [unittest module](http://github.com/kohana/unittest).
You can use the `minion` group to only run minion tests.

i.e.

	phpunit --group minion

Feel free to contribute tests(!), they can be found in the `tests/minion` directory. :)

## License

This is licensed under the [same license as Kohana](http://kohanaframework.org/license).
