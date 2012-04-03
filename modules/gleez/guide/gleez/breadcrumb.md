# Breadcrumb component for Gleez CMS

### Usage

Copy config/breadcrumb.php to your app/config folder if you want to change config values:
- separator
- last link enabled

Define somewhere your base item:

Breadcrumb::factory()->addItem('Home', Route::url('default'));

Note: the first parameter is always translated in the default view

In the controller or in the view you can add more items:

Breadcrumbs::factory()->addItem('Users list', Route::url('users-list'));
Breadcrumbs::factory()->addItem('John Doe');

Result:

Home > Users list > John Doe

