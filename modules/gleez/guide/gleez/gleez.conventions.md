II. Conventions
---------------

[!!] Note: This section is particularly useful to developers who understand Kohana 3.

Since Gleez is a CMS that anticipates many 3rd party developers to contribute extensions, it has added some conventions alongside Kohana 3's own conventions to avoid conflicts between extensions.

The Cascading File System is flexible indeed, but it opens up potential conflicts if developers are not careful enough.

If you're a developer who would like to develop extensions that can be used inside Gleez, just follow these additional conventions. Gleez extensions are simply Kohana 3 Modules.

Gleez just provides an interface that wraps around your Kohana 3 Module.

1. **Encapsulate your extensions inside a directory with the same name as your extension**

    Suppose you created a Gleez App named `myapp`. You should then put your helpers and libraries inside `classes/myapp`.

    Your controllers should be located inside `classes/controller/myapp`.

    Your models should be located inside `classes/model/myapp` which will consequently have a table prefix `myapp_`.

    Keep the name of your extension as one word. Only ASCII English alphabet characters are allowed, no spaces, no underscores, no hyphen.
    You can use the extension Title to describe your extension more clearly. Your app's name is simply a unique and short identifier for your extension.

    [!!] Note: This convention can become obsolete when PHP Namespaces become widely adopted.

2. **There are 2 kinds of Routes**

    Gleez has a Site section and an Admin section. Site is what we commonly call the Frontend. Admin is the Backend. Only Administrators are allowed to access the admin backend.
    When you set Routes, you must specify which ones are for the site, and which ones are for the admin.


    **You declare a Route for the site frontend like this:**

        Route::set('site/myapp', '<myapp>(/<action>(/<id>))')
            ->defaults(array(
                'controller' => 'defaultcontroller',
                'action'     => 'index',
            ));

    Notice that the Route name is prefixed with `site/`. This is used to indicate that this route is only for the site frontend.

    It is also strongly suggested that you specify which controllers are accessible via this route through the Regex parameter. This is a precaution to avoid conflicts.

    Notice also that the Route name `site/myapp` has a second segment `myapp`. The second segment should always be the name of your app.
    You can create many other routes with different names like `site/myapp/firstroute` or `site/myapp/secondroute` but it should always begin with `site/myapp`.
    You don't need to set the `directory` parameter because it will always be the name of your app.

    Another important thing to mention is that you should always prefix your Route's URI with `<app>`. You don't need to adhere to this convention if you really know what you're doing.
    But it's strongly suggested to avoid conflicts with other routes. When Reverse routing is used, `<app>` will be replaced by your app's alias. Alias is a "slug" for your app. If your app doesn't have an alias,
    your app's name will be used. App aliases can be set by the Site Administrators for Search Engine Friendly and Human Readable URLs.

    **You declare a Route for the admin backend like this:**

        Route::set('admin/myapp', 'admin/myapp(/<action>(/<id>))')
            ->defaults(array(
                'controller' => 'defaultcontroller',
                'action'     => 'index',
            ));

    If your app has admin pages, you need to use admin specific routes. Some magic is going on here. `admin` is automatically appended to the URI.
    So on reverse routing, the URI can look like this: `http://www.mydomain.com/admin/defaultcontroller/index/id`.

    Besides being a shortcut, the reason why you don't need to put `admin` manually in the URI like this `admin/<app>(/<controller>(/<action>(/<id>)))` is because `admin` can also have an alias.
    That means you can hide your admin backend to a different URI segment like `mysecretadminsection`. You can then access your admin section like this `http://www.mydomain.com/mysecretadminsection` instead of `http://www.mydomain.com/admin`.

3. **Declaring Widgets**

    Unlike other CMS's, Gleez's Widgets aren't necessarily separate from your app. Widgets are simply HMVC calls. Thus, widgets can reside in the same controller as the app. It can also be a stand alone widget.

    You don't need a route to make your widgets accessible, all you have to do is declare them in your extension's init.php, where you also declare your app's routes.

        Widgets::register('mywidget');

    There will be more discussion about Widgets in another Chapter.

4. **Declaring Permissions**

    Gleez has 3 main kinds of users and nothing more: Guests, Members and Administrators. You can create unlimited groups of users under Members and also unlimited groups of users under Administrators.
    Each Group can have fine grained permissions management similar to Drupal.

    You can't create a user group under Guests. Members can login to the Site Frontend only. Admins can login to both Site and Admin.

    For instance, you declare permissions like this on the init.php of your app:

        ACL::set('content', array(
            'access content' =>  array(
                'title' => __('Create content'),
                'restrict access' => FALSE,
                'description' => '',
            ),
        ));

    For multiple permissions, it's like this:

        ACL::set('content', array(
            'administer content' =>  array(
                'title' => __('Administer content'),
                'restrict access' => TRUE,
                'description' => '',
            ),
            'access content' =>  array(
                'title' => __('Create content'),
                'restrict access' => FALSE,
                'description' => '',
            ),
        ));

    Each permission is an array indexed by permission, Human readable title and description. If you specify restrict access true, admin permission settings page shows an warning that assigning this permission implies security implication, assing to only trusted roles.

    This is just a basic declaration of permissions. More details will be discussed in another chapter. But as a preview, you can specify the controllers and actions related to the permission. This will allow Gleez to automatically check for the user's permissions when certain controllers are accessed.

5. **Extending Gleez's Controllers**

    If you want your app to automatically check user permissions as declared in your init.php, you must extend `Controller_Base` or `Controller_Admin`.

    For your site controllers do something like this:

        class Controller_MyApp extends Template{

            public function action_index()
            {

            }
        }

    Or for the admin:

        class Controller_Admin_MyApp extends Controller_Admin{

            public function action_index()
            {

            }
        }

    These Site and Admin controllers have a default `before()` methods that checks permissions automatically.
