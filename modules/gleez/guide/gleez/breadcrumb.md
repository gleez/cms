# Breadcrumb component for Gleez CMS

### Usage

Copy `config/breadcrumb.php` to your `application/config` folder if you want to change config values:

* Separator
* Last link enabled

Define somewhere your base item:

~~~
Breadcrumb::instance()->addItem('Home', Route::url('default'));
~~~

[!!] Note: the first parameter is always translated in the default view

In the controller or in the view you can add more items:

~~~
Breadcrumb::instance()
            ->addItem('Users list', Route::url('users-list'));
            ->addItem('John Doe');
~~~

Result: `Home > Users list > John Doe`
