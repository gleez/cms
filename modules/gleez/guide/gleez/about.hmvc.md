HMVC in Gleez
-------------

One of the most powerful features in Kohana 3.0 is the ability to call another request at any time during the [request flow](http://gleezcms.org/guide/about.flow). This layered MVC approach allows you to assemble a complex client tier and really harness the powerful benefits of object orientation.

**An optimally layered architecture:**

  * Reduces dependencies between disparate parts of the program
  * Encourages reuse of code, components, and modules
  * Increases extensibility while easing maintainability

**Some uses for HMVC design in a client-tier architecture**

  * Modular interface elements or widgets
  * Application and Menu Controls
  * Server Interaction
  * Reusable Application Flows

### HMVC Basics

An easy way to think of HMVC is to think of AJAX without an extra server call. For instance, if you have an action for AJAX that displays a list of users, you can reuse that action in other controllers, rather than duplicating the method.

**The Request Factory**

HMVC comes to Kohana by way of the [Request::factory()](http://gleezcms.org/guide/api/Request#factory) method. Using the Request factory you can instigate and fully execute a Kohana request at any time during the [request flow](http://gleezcms.org/guide/about.flow).

The [Request::factory()](http://gleezcms.org/guide/api/Request#factory) method accepts a Route URI as a parameter and when combined with Kohana's powerful Routing features brings full extensibility to any application you build.

**Using the Request Factory in a Controller**

The following example shows you how to use the request factory from inside another controller. While it doesn't fully highlight the power behind HMVC, it does show how two separate requests can be layered together.

    class Controller_Static extends Controller
    {
        /**
         * The following action loads page.
         * A sub request is called to load a dynamic menu
         */
        public function action_page()
        {
           $page_name = Request::instance()->param('page');

           $this->request->response = View::factory('page/'.$page_name)
                    ->bind('menu', $menu);

           $menu = Request::factory('static/menu')->execute()->response;
        }

        public function action_menu()
        {
           $page_name = Request::instance()->param('page');

           $this->request->response = View::factory('page/menu')
                    ->bind('links', $links);

           $links = Kohana::$config->load('menu')->$page_name;
        }
    }

**Using the Request Factory in a View**

Another effective way to use the Request factory is to call a request from a View. In the example below we call a dynamic menu and a dynamic footer from a View instead of a controller.

    <h1><?php echo $page_title ?></h1>

    <?php echo Request::factory('page/menu')->execute()->response ?>

    <div id="container">
      <?php echo $content ?>
    </div>

    <?php echo Request::factory('page/menu')->execute()->response ?>

The only difference in calling **Request::instance()** over **Request::factory()** is that //instance()// creates a singleton of the Request class which is intended to handle the main request and also output neccessary response headers.

An important thing to point out is that it isn't absolutely necessary to create a singleton instance of Request or output any headers. A Kohana request can fully execute using just the **Request::factory()** as long as Kohana is initialized.