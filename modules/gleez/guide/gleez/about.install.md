# Installation

To install Gleez, follow these instructions:

## Installing using the zip

1.  Download the [latest zip file](http://gleezcms.org/download), and extract it onto your server.

2.  We need to make `application/logs` and `application/cache`, and `application/tmp` folders writable, Using an ftp client you can usually right-click on each folder, click on permisions, and mark as group and world writable.  If you have command-line access you can run the following commands:

   ~~~
   chmod 777 application/logs
   chmod 777 application/cache
   chmod 777 application/tmp
   ~~~

[!!] Depending on your platform, the installation's subdirs may have lost their permissions thanks to zip extraction. Chmod them all to 755 by running `find . -type d -exec chmod 0755 {} \;` from the root of your gleez installation.

3.  You will need to create a empty database, and a database user with access to that database.

4.  Now point your browser to `base_url`, and click follow the instructions. If you get no errors, then Gleez is installed!

5. Test your site by opening the URL you set as the `base_url` in your favorite browser.


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

If Gleez is not in the root of the server we need to change some files.  Lets say we are putting kohanut in a subfolder called "subfolder"

In **.htaccess**

    RewriteBase /
    -- change to --
    RewriteBase /subfolder
  
In **application/bootstrap.php**
   
    'base_url'   => '/',
    -- change to --
    'base_url'   => '/subfolder',

If you put Gleez in a subfolder, the links on all your pages will probably be broken, especially if you move a site that is already made. You could probably fix it by adding a [<base\>](http://w3schools.com/tags/tag_base.asp) tag.

