# Installation

To install Gleez, follow these instructions:

## Installing using the zip

1. Download the [latest version](https://github.com/gleez/cms/archive/master.zip) of Gleez CMS.
2. Create a database called `gleezcms`, and a database user with access to that database in [phpMyAdmin](http://www.phpmyadmin.net/), [Adminer](http://www.adminer.org/) or equivalent.
3. Upload the contents of the `gleezcms` folder to your server.
4. <u>If your operating system is based on access rights, such as Linux or UNIX</u>
  * Files should have permissions set to 644 and folders should be set to 755, except...
  * Set `/application/cache` and its sub-folders to 777
  * Set `/application/logs` and its sub-folders to 777
  * Set `/application/media` and its sub-folders to 777
  * Set `/application/config` and its sub-folders to 777
  * Set `/media` and its sub-folders to 777
5. Go to `http://example.com/` and follow the steps
6. When finished, change the admin password.

[!!] Depending on your platform, the installation's subdirs may have lost their permissions thanks to zip extraction. Chmod them all to 755 by running `find . -type d -exec chmod 0755 {} \;` from the root of your gleez installation.

## Git Clone Installation

~~~
git clone https://github.com/gleez/cms.git gleezcms
cd gleezcms
git submodule init
git submodule update
~~~
Follow from step 2 of the above instructions


## Setting up a production environment

There are a few things you'll want to do with your application before moving into production.

1. See the [Configuration page](about.configuration) in the docs. 
   This covers most of the global settings that would change between environments. 
   As a general rule, you should enable caching and disable profiling ([Kohana::init] settings) for production sites. 
   [Route caching](api/Route#cache) can also help if you have a lot of routes.
   
2. Turn on APC or some kind of opcode caching. 
   This is the single easiest performance boost you can make to PHP itself. The more complex your application, the bigger the benefit of using opcode caching.

[!!] Note: The default bootstrap will set Kohana::$environment = $_ENV['KOHANA_ENV'] if set. Docs on how to supply this variable are available in your web server's documentation (e.g. [Apache](http://httpd.apache.org/docs/1.3/mod/mod_env.html#setenv), [Lighttpd](http://redmine.lighttpd.net/wiki/1/Docs:ModSetEnv#Options), [Nginx](http://wiki.nginx.org/NginxHttpFcgiModule#fastcgi_param)). This is considered better practice than many alternative methods to set Kohana::$enviroment.

		/**
		 * Set the environment string by the domain (defaults to Kohana::DEVELOPMENT).
		 */
		Kohana::$environment = ($_SERVER['SERVER_NAME'] !== 'localhost') ? Kohana::PRODUCTION : Kohana::DEVELOPMENT;
		/**
		 * Initialise Kohana based on environment
		 */
		Kohana::init(array(
			'base_url'   => '/',
			'index_file' => FALSE,
			'profile'    => Kohana::$environment !== Kohana::PRODUCTION,
			'caching'    => Kohana::$environment === Kohana::PRODUCTION,
		));
		

## Putting Gleez in a subfolder

If Gleez is not in the root of the server we need to change some files.  Lets say we are putting gleez in a subfolder called "subfolder"

In **.htaccess**

    RewriteBase /
    -- change to --
    RewriteBase /subfolder
  
In **application/bootstrap.php**
   
    'base_url'   => '/',
    -- change to --
    'base_url'   => 'http://example.com/subfolder/',

Please use full aboslute url in base_url when running in subfolder is mandatory.
If you put Gleez in a subfolder, the links on all your pages will probably be broken, especially if you move a site that is already made. You could probably fix it by adding a [<base\>](http://w3schools.com/tags/tag_base.asp) tag.

