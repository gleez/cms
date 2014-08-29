# Introduction

This class objectifies a \MongoDB document into `\Gleez\Mango\Document` and can be used with one of the following design patterns:

## Table Data Gateway pattern:

~~~
class Model_Post extends \Gleez\Mango\Document {
    protected $name = 'posts';
    // All model-related code here
}

$post = \Gleez\Mango\Document::factory('post', $post_id);
~~~

## Row Data Gateway pattern:
~~~
class Model_Post_Collection extends \Gleez\Mango\Collection {
    protected $name = 'posts';
    // Collection-related code here
}

class Model_Post extends \Gleez\Mango\Document {
    // Document-related code here
}

$post = \Gleez\Mango\Document::factory('post', $post_id);
~~~

The following examples could be used with either pattern with no differences in usage. The Row Data Gateway pattern is recommended for more complex models to improve code organization while the Table Data Gateway pattern is recommended for simpler models.

**Example**:
~~~
class Model_Document extends \Gleez\Mango\Document {
    protected $name = 'test';
}

$document = new Model_Document();
// or \Gleez\Mango\Document::factory('document');

$document->name = 'Mongo';
$document->type = 'db';

$document->save();
// db.test.save({"name":"Mongo","type":"db"});
~~~

The `_id` is aliased to id by default. Other aliases can also be defined using the `\Gleez\Mango\Document::$aliases` protected property. Aliases can be used anywhere that a field name can be used including dot-notation for nesting.

**Example**:
~~~
$id = $document->_id; // \MongoId
~~~

All methods that take query parameters support JSON strings as input in addition to PHP arrays. The JSON parser is more lenient than usual.

[!!] Note: `\Gleez\Mango\Client`, `\Gleez\Mango\Collection` and `\Gleez\Mango\Document` uses Gleez [JSON], [Profiler], [Arr], [Text] helper classes.

**Example**:
~~~
$document->load('{name:"Mongo"}');
// db.test.findOne({"name":"Mongo"});
~~~

Methods which are intended to be overridden are {before,after}{Save,Load,Delete} so that special actions may be taken when these events occur:
~~~
public function beforeSave()
{
    $this->inc('visits');
    $this->last_visit = time();
}
~~~

When a document is saved, update will be used if the document already exists, otherwise insert will be used, determined by the presence of an `_id`. A document can be modified without being loaded from the database if an _id is passed to the constructor:
~~~
$doc = new Model_Document($id);
~~~

Atomic operations and updates are not executed until `\Gleez\Mango\Document::save` is called and operations are chainable.

**Example**:
~~~
$doc->inc('uses.boing');
    ->push('used', array('type' => 'sound', 'desc' => 'boing'));

$doc->inc('uses.bonk')
    ->push('used', array('type' => 'sound', 'desc' => 'bonk'))
    ->save();

// db.test.update(
//     {"_id": "some-id-here"},
//     {"$inc":
//         {"uses.boing": 1, "uses.bonk": 1},
//         "$pushAll":
//             {"used": [
//                          {"type": "sound", "desc": "boing"},
//                          {"type": "sound", "desc": "bonk"}
//                      ]
//             }
//     }
// );
~~~

Documents are loaded lazily so if a property is accessed and the document is not yet loaded, it will be loaded on the first property access:
~~~
echo "{$doc->name} rocks!";
// Mongo rocks!
~~~

Documents are reloaded when accessing a property that was modified with an operator and then saved:
~~~
in_array($doc->roles, 'admin'); // true

$doc->pull('roles', 'admin');
in_array($doc->roles, 'admin'); // true

$doc->save();
in_array($doc->roles, 'admin'); // false
~~~

Documents can have references to other documents which will be loaded lazily and saved automatically:
~~~
class Model_Post extends \Gleez\Mango\Document {
    protected $name = 'posts';
    protected $references = array('user' => array('model' => 'user'));
}

class Model_User extends \Gleez\Mango\Document {
    protected $name = 'users';
}

$user = \Gleez\Mango\Document::factory('user')
                       ->set('id',    'john')
                       ->set('email', 'john@doe.com');

$post = \Gleez\Mango\Document::factory('post');

$post->user  = $user;
$post->title = 'MongoDB';

$post->save();
// db.users.save({"_id": "john", "email": "john@doe.com"})
// db.posts.save({"_id": Object, "_user": "john", "title": "MongoDB"})

$post = new Model_Post($id);

$post->_user;
// "john" - the post was loaded lazily.

$post->user->_id;
// "john" - the user object was created lazily but not loaded.

$post->user->email;
// "john@doe.com" - now the user document was loaded as well.
~~~
