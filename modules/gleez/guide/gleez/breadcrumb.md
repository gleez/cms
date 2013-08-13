# Breadcrumb component for Gleez CMS

### Usage

Copy `config/breadcrumb.php` to your `application/config` folder if you want to change config values:

* separator
* last link enabled

Define somewhere your base item:

~~~
Breadcrumb::factory()->addItem('Home', Route::url('default'));
~~~

[!!] Note: the first parameter is always translated in the default view

In the controller or in the view you can add more items:

~~~
Breadcrumb::factory()->addItem('Users list', Route::url('users-list'));
Breadcrumb::factory()->addItem('John Doe');
~~~

Result: `Home > Users list > John Doe`

