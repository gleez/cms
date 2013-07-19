# Installation

To install Gleez, follow these instructions:

## First-time Installation

### Installing using the zip

1. Download the [latest version](https://github.com/gleez/cms/archive/master.zip) of Gleez CMS
2. Upload the contents of the `gleezcms` folder to your server
3. Create a database called `gleezcms`, and a database user with access to that database in [phpMyAdmin](http://www.phpmyadmin.net/), [Adminer](http://www.adminer.org/) or equivalent
4. If your operating system is based on access rights, such as Linux or UNIX. Files should have permissions set to 644 and folders should be set to 755, except:
  * Set `/application/cache` and its sub-folders to 777
  * Set `/application/logs` and its sub-folders to 777
  * Set `/application/media` and its sub-folders to 777
  * Set `/application/config` and its sub-folders to 777
  * Set `/media` and its sub-folders to 777

  For example you can execute immediately after Gleez installation this commands:
  * `cd path_to_the_your_installed_gleez_dir\application`
  * `find -type d -exec chmod -R a=rwx {} \;`
  * `cd .. && chmod a=rwx media`
5. Go to `http://your_site_name/` and follow the Gleez Installer steps
6. When finished, change the admin password

[!!] Note: Depending on your platform, the installation's subdirs may have lost their permissions thanks to zip extraction. Chmod them all to 755 by running `find . -type d -exec chmod 0755 {} \;` from the root of your gleez installation.

### Git Clone Installation

The [source code](https://github.com/gleez/cms) for Gleez is hosted with [GitHub](http://github.com). To install Gleez using the github source code first you need to install [git](http://git-scm.com/).  Visit [http://help.github.com](http://help.github.com) for details on how to install git on your platform.

~~~
  git clone https://github.com/gleez/cms.git gleezcms
  cd gleezcms
  git submodule update --init --recursive
~~~

### ArchLinux Installation

You can install Gleez in ArchLinux using [Yaourt](https://wiki.archlinux.org/index.php/Yaourt):

~~~
yaourt -S gleez
~~~


Follow from step 3 of the above instructions

Also you can search Gleez & Gleez Modules for ArchLinux by following command:

~~~
yaourt gleez
# OR
yaourt -Ss gleez
~~~

### Putting Gleez in a subfolder

If Gleez is not in the root of the server we need to change some files. Lets say we are putting gleez in a subfolder called "subfolder"

In `.htaccess`

~~~
    RewriteBase /
    -- change to --
    RewriteBase /subfolder
~~~

and

~~~
    RewriteRule ^(.*)index.php$ /$1 [R=301,L]
    -- change to --
    RewriteRule ^(.*)index.php$ /subfolder/$1 [R=301,L]
~~~

In `application/bootstrap.php`

~~~
    'base_url'   => '/',
    -- change to --
    'base_url'   => 'http://example.com/subfolder/',
~~~

Please use full aboslute url in base_url when running in subfolder is mandatory.


Please use full aboslute url in base_url when running in subfolder is mandatory.
If you put Gleez in a subfolder, the links on all your pages will probably be broken, especially if you move a site that is already made. You could probably fix it by adding a [<base\>](http://w3schools.com/tags/tag_base.asp) tag.

## Setting up a production environment

There are a few things you'll want to do with your application before moving into production.

1. See the [Configuration page](about.configuration) in the docs.
   This covers most of the global settings that would change between environments.
   As a general rule, you should enable caching and disable profiling ([Kohana::init] settings) for production sites. [Route caching](api/Route#cache) can also help if you have a lot of routes.

2. Turn on APC or some kind of opcode caching. This is the single easiest performance boost you can make to PHP itself. The more complex your application, the bigger the benefit of using opcode caching.

[!!] Note: The default bootstrap will set `Kohana::$environment = $_ENV['GLEEZ_ENV']` if set. Docs on how to supply this variable are available in your web server's documentation (e.g. [Apache](http://httpd.apache.org/docs/1.3/mod/mod_env.html#setenv), [Lighttpd](http://redmine.lighttpd.net/wiki/1/Docs:ModSetEnv#Options), [Nginx](http://wiki.nginx.org/NginxHttpFcgiModule#fastcgi_param)). This is considered better practice than many alternative methods to set Kohana::$enviroment.

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


## Setting up your site

 1. Log into Admin and go to **Admin -> Settings**
 2. Change the settings as appropriate.
 3. Download modules from the Module Downloads, unzip and upload them to
    the `/modules/` directory.
 4. Go to **Admin -> Modules** and install the modules.
 5. Edit settings for each widget listed in the sidebar under widgets.
 6. Click the site title to view your changes.

## Friendly URLs

If you want to use friendly urls and Apache HTTP Server, use `.htaccess` file, and
edit it according to the instructions within the `.htaccess` file if any.

## Troubleshooting

If you're having trouble installing Gleez, please post your questions with as
much detail as possible in the issues Thanks.

Make sure the `application/cache` and `application/logs` directories are writable by the web server.