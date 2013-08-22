<?php
/**
 * # Mango Document
 *
 * This class objectifies a MongoDB document and can be used with one
 * of the following design patterns:
 *
 * ## Usage
 *
 * ### Table Data Gateway pattern:
 * ~~~
 * class Model_Post extends Mango_Document {
 *     protected $_name = 'posts';
 *     // All model-related code here
 * }
 *
 * $post = Mango_Document::factory('post', $post_id);
 * ~~~
 *
 * ### Row Data Gateway pattern:
 * ~~~
 * class Model_Post_Collection extends Mango_Collection {
 *     protected $_name = 'posts';
 *     // Collection-related code here
 * }
 *
 * class Model_Post extends Mango_Document {
 *     // Document-related code here
 * }
 *
 * $post = Mango_Document::factory('post', $post_id);
 * ~~~
 *
 * The following examples could be used with either pattern with no differences in usage.
 * The Row Data Gateway pattern is recommended for more complex models to improve code
 * organization while the Table Data Gateway pattern is recommended for simpler models.
 *
 * __Example__:
 * ~~~
 * class Model_Document extends Mango_Document {
 *     protected $_name = 'test';
 * }
 *
 * $document = new Model_Document();
 * // or Mango_Document::factory('document');
 *
 * $document->name = 'Mongo';
 * $document->type = 'db';
 *
 * $document->save();
 * // db.test.save({"name":"Mongo","type":"db"});
 * ~~~
 *
 * The `_id` is aliased to id by default. Other aliases can also be defined using
 * the [Mango_Document::$_aliases] protected property. Aliases can be used anywhere that a
 * field name can be used including dot-notation for nesting.
 *
 * __Example__:
 * ~~~
 * $id = $document->id; // MongoId
 * ~~~
 *
 * All methods that take query parameters support JSON strings as input in addition to PHP arrays.
 * The JSON parser is more lenient than usual.
 *
 * [!!] Note: [Mango], [Mango_Collection] and [Mango_Document] uses Gleez [JSON] helper class.
 *
 * __Example__:
 * ~~~
 * $document->load('{name:"Mongo"}');
 * // db.test.findOne({"name":"Mongo"});
 * ~~~
 *
 * Methods which are intended to be overridden are {before,after}_{save,load,delete} so that special
 * actions may be taken when these events occur:
 * ~~~
 * public function before_save()
 * {
 *     $this->inc('visits');
 *     $this->last_visit = time();
 * }
 * ~~~
 *
 * When a document is saved, update will be used if the document already exists, otherwise insert
 * will be used, determined by the presence of an `_id`. A document can be modified without being
 * loaded from the database if an _id is passed to the constructor:
 * ~~~
 * $doc = new Model_Document($id);
 * ~~~
 *
 * Atomic operations and updates are not executed until [Mango_Document::save] is called
 * and operations are chainable.
 *
 * __Example__:
 * ~~~
 * $doc->inc('uses.boing');
 *     ->push('used', array('type' => 'sound', 'desc' => 'boing'));
 *
 * $doc->inc('uses.bonk')
 *     ->push('used', array('type' => 'sound', 'desc' => 'bonk'))
 *     ->save();
 *
 * // db.test.update(
 * //     {"_id": "some-id-here"},
 * //     {"$inc":
 * //         {"uses.boing": 1, "uses.bonk": 1},
 * //         "$pushAll":
 * //             {"used": [
 * //                          {"type": "sound", "desc": "boing"},
 * //                          {"type": "sound", "desc": "bonk"}
 * //                      ]
 * //             }
 * //     }
 * // );
 * ~~~
 *
 * Documents are loaded lazily so if a property is accessed and the document is not yet loaded,
 * it will be loaded on the first property access:
 * ~~~
 * echo "{$doc->name} rocks!";
 * // Mongo rocks!
 * ~~~
 *
 * Documents are reloaded when accessing a property that was modified with
 * an operator and then saved:
 * ~~~
 * in_array($doc->roles, 'admin'); // TRUE
 *
 * $doc->pull('roles', 'admin');
 * in_array($doc->roles, 'admin'); // TRUE
 *
 * $doc->save();
 * in_array($doc->roles, 'admin'); // FALSE
 * ~~~
 *
 * Documents can have references to other documents which will be loaded
 * lazily and saved automatically:
 * ~~~
 * class Model_Post extends Mango_Document {
 *     protected $_name = 'posts';
 *     protected $_references = array('user' => array('model' => 'user'));
 * }
 *
 * class Model_User extends Mango_Document {
 *     protected $name = 'users';
 * }
 *
 * $user = Mango_Document::factory('user')
 *                       ->set('id',    'john')
 *                       ->set('email', 'john@doe.com');
 *
 * $post = Mango_Document::factory('post');
 *
 * $post->user  = $user;
 * $post->title = 'MongoDB';
 *
 * $post->save();
 * // db.users.save({"_id": "john", "email": "john@doe.com"})
 * // db.posts.save({"_id": Object, "_user": "john", "title": "MongoDB"})
 *
 * $post = new Model_Post($id);
 *
 * $post->_user;
 * // "john" - the post was loaded lazily.
 *
 * $post->user->id;
 * // "john" - the user object was created lazily but not loaded.
 *
 * $post->user->email;
 * // "john@doe.com" - now the user document was loaded as well.
 * ~~~
 *
 * ## System Requirements
 *
 * - MongoDB 2.4 or higher
 * - PHP-extension MongoDB 1.4.0 or higher
 *
 * This class was adapted from
 * [colinmollenhour/mongodb-php-odm](https://github.com/colinmollenhour/mongodb-php-odm)
 *
 * @package    Gleez\Mango\Document
 * @author     Gleez Team
 * @version    0.1.0
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @license    http://gleezcms.org/license  Gleez CMS License
 *
 * @link       https://github.com/colinmollenhour/mongodb-php-odm  MongoDB PHP ODM
 * @link       http://www.martinfowler.com/eaaCatalog/tableDataGateway.html  Table Data Gateway pattern
 * @link       http://www.martinfowler.com/eaaCatalog/rowDataGateway.html  Row Data Gateway pattern
 */
abstract class Mango_Document {

	/**
	 * Array of document factory names.
	 * You can specify own factory name to class mapping.
	 *
	 * __Example__:
	 * ~~~
	 * Mango_Document::$models['foo'] = 'Model_My_Foo';
	 * ~~~
	 *
	 * @var array
	 */
	public static $models = array();

	/**
	 * The name of the collection within the database or the gridFS prefix if
	 * [Mango_Document::$_gridFS] is TRUE. If using a corresponding [Mango_Collection]
	 * subclass, set this only in the [Mango_Collection] subclass.
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * The database configuration name (passed to [Mango::instance]).
	 * If using a corresponding [Mango_Collection] subclass, set this only
	 * in the [Mango_Collection] subclass.
	 *
	 * @var string
	 */
	protected $_db;

	/**
	 * Whether or not this collection is a gridFS collection. If using a corresponding
	 * [Mango_Collection] subclass, set this only in the [Mango_Collection] subclass.
	 *
	 * @var boolean
	 */
	protected $_gridFS = FALSE;

	/**
	 * Definition of references existing in this document. If `model` is not specified it
	 * defaults to the reference name. If `field` is not specified it defaults to the
	 * reference name prefixed with an `_`.
	 *
	 * __Example__:
	 * ~~~
	 * // Example Document: {_id: 1, user_id: 2, _token: 3}
	 * protected $_references = array(
	 *     'user' => array(
	 *         'model' => 'user',
	 *         'field' => 'user_id'
	 *     ),
	 *     'token' => NULL,
	 * );
	 * ~~~
	 *
	 * You can also specify getter and setter functions:
	 *
	 * + `getter` - NULL will make the value write-only,
	 *              string will call `$this->{$getter}($name)`,
	 *              callable will be called as `$getter($this, $name)`
	 * + `setter` - NULL will make the value read-only,
	 *              string will call $this->{$setter}($value),
	 *              callable will be called as $setter($value, $this, $name)
	 *
	 * @var array
	 */
	protected $_references = array();

	/**
	 * Definition of predefined searches for use with [Mango_Document::__call].
	 * This instantiates a collection for the target model and initializes the
	 * search with the specified field being equal to the `_id` of the current object.
	 *
	 * __Example__:
	 * ~~~
	 * $_searches
	 * {events: {model: 'event', field: '_user'}}
	 * // db.event.find({_user: <_id>})
	 * ~~~
	 *
	 * @var array
	 */
	protected $_searches = array();

	/**
	 * Field name aliases.
	 * `_id` is automatically aliased to `id`.
	 *
	 * @var array
	 */
	protected $_aliases = array();

	/**
	 * If set to `TRUE`, operator functions (set, inc, push etc.)
	 * will emulate database functions for eventual consistency.
	 *
	 * __Example__:
	 * ~~~
	 * $doc = new Mango_Document();
	 *
	 * $doc->_emulate = TRUE;
	 * $doc->number = 1;
	 * $doc->inc('number');
	 *
	 * // This will print '2'
	 * echo $doc->number;
	 * ~~~
	 *
	 * If set to `FALSE`, the field will be marked as dirty, and the new
	 * value will only be available after reload.
	 *
	 * __Example__:
	 * ~~~
	 * $doc = new Mango_Document();
	 *
	 * $doc->_emulate = FALSE;
	 * $doc->number = 1;
	 * $doc->inc('number');
	 *
	 * // This will print '1'
	 * echo $doc->number;
	 * ~~~
	 *
	 * @var boolean
	 */
	protected $_emulate = FALSE;

	/**
	 * Internal storage of object data
	 * @var array
	 */
	protected $_object = array();

	/**
	 * Keep track of fields changed using [Mango_Document::__set]
	 * or [Mango_Document::load_values]
	 *
	 * @var array
	 */
	protected $_changed = array();

	/**
	 * Set of operations to perform on update/insert
	 * @var array
	 */
	protected $_operations = array();

	/**
	 * Keep track of data that is dirty
	 * (changed by an operation but not yet updated from database)
	 *
	 * @var array
	 */
	protected $_dirty = array();

	/**
	 * Storage for referenced objects
	 * @var array
	 */
	protected $_related_objects = array();

	/**
	 * Document loaded status
	 *
	 * + `NULL`  - not attempted
	 * + `FALSE` - failed
	 * + `TRUE`  - succeeded
	 *
	 * @var boolean
	 */
	protected $_loaded = NULL;

	/**
	 * A cache of [Mango_Collection] instances for performance
	 * @var array
	 */
	protected static $_collections = array();

	/**
	 * Designated place for non-persistent data storage
	 * (will not be saved to the database or after sleep)
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * Instantiate an object conforming to Mango_Document conventions
	 *
	 * [!!] Note: The document is not loaded until [Mango_Document::load] is called.
	 *
	 * Example:
	 * ~~~
	 * // Attempts to create the Model_Post object
	 * $model = Mango_Document::factory('post');
	 *
	 * // Attempts to create the Model_User object
	 * $model = Mango_Document::factory('User');
	 *
	 * // Attempts to create the Model_Document_First object
	 * $model = Mango_Document::factory('document_first');
	 *
	 * // Attempts to create the Document object
	 * $model = Mango_Document::factory('\Document');
	 *
	 * // Attempts to create the \Document\Last object
	 * $model = Mango_Document::factory('\document\last');
	 * ~~~
	 *
	 * @param   string        $name  Model name
	 * @param   string|array  $id    The _id of the document [Optional]
	 *
	 * @return  \Mango_Document
	 */
	public static function factory($name, $id = NULL)
	{
		if (isset(self::$models[$name]))
		{
			$class = self::$models[$name];
		}
		elseif (FALSE !== strpos($name, '\\'))
		{
			$class = implode('\\', array_map('ucfirst', explode('\\', Text::reduce_slashes($name))));;
		}
		else
		{
			$class = 'Model_' . implode('_', array_map('ucfirst', explode('_', $name)));
		}

		return new $class($id);
	}

	/**
	 * Instantiate a new Document object
	 *
	 * If an id or other data is passed then it will be assumed that
	 * the document exists in the database and updates will be performed without loading the document first.
	 *
	 * @param   string|array  $id  _id of the document to operate on or criteria used to load [Optional]
	 *
	 * @return  \Mango_Document
	 *
	 * @throws  Mango_Exception
	 */
	public function __construct($id = NULL)
	{
		if ( ! is_null($id))
		{
			if (is_array($id))
			{
				foreach ($id as $key => $value)
				{
					$this->_object[$this->getFieldName($key)] = $value;
				}
			}
			elseif(is_string($id))
			{
				$this->_object['_id'] = $this->_cast('_id', $id);
			}
			else
			{
				throw new Mango_Exception('_id of the document must be string or array of strings');
			}
		}
	}

	/**
	 * Returns the attributes that should be serialized
	 *
	 * @return  array
	 */
	public function __sleep()
	{
		return array('_references', '_aliases', '_object', '_changed', '_operations', '_loaded', '_dirty');
	}

	/**
	 * Checks if a field is set
	 *
	 * @param  string  $name  Field name
	 *
	 * @return boolean
	 */
	public function __isset($name)
	{
		$name = $this->getFieldName($name, FALSE);

		if (isset($this->_object[$name]))
		{
			return TRUE;
		}

		// check for dirties...
		if ($this->get($name))
		{
			return TRUE;
		}

		return isset($this->_object[$name]);
	}

	/**
	 * Unset a field
	 *
	 * @param  string  $name  Field name
	 */
	public function __unset($name)
	{
		$this->_unset($name);
	}

	/**
	 * Gets one of the following:
	 *
	 * - A referenced object
	 * - A search() result
	 * - A field's value
	 *
	 * @param   string  $name  Field name
	 *
	 * @return  mixed
	 *
	 * @throws Mango_Exception
	 */
	public function __get($name)
	{
		$name = $this->getFieldName($name, FALSE);

		// Auto-loading for special references
		if (array_key_exists($name, $this->_references))
		{
			if (isset($this->_references[$name]['getter']))
			{
				if (is_null($this->_references[$name]['getter']))
				{
					throw new Mango_Exception('$name is write only!');
				}
				elseif (is_string($this->_references[$name]['getter']))
				{
					return call_user_func(array($this, $this->_references[$name]['getter']), $name);
				}
				else
				{
					return call_user_func(array($this, $this->_references[$name]['getter']), $this, $name);
				}
			}

			if ( ! isset($this->_related_objects[$name]))
			{
				$model = isset($this->_references[$name]['model'])
					? $this->_references[$name]['model']
					: $name;

				$foreign_field = isset($this->_references[$name]['foreign_field'])
					? $this->_references[$name]['foreign_field']
					: FALSE;

				if ($foreign_field)
				{
					$this->_related_objects[$name] = Mango_Document::factory($model)
						->getCollection(TRUE)
						->find($foreign_field, $this->id);

					return $this->_related_objects[$name];
				}

				$id_field = isset($this->_references[$name]['field'])
					? $this->_references[$name]['field']
					: "_{$name}";

				$value = $this->__get($id_field);

				if ( ! empty($this->_references[$name]['multiple']))
				{
					$this->_related_objects[$name] = Mango_Document::factory($model)
						->getCollection(TRUE)
						->find(array('_id' => array('$in' => (array)$value)));
				}
				else
				{
					// Extract just id if value is a DBRef
					if (is_array($value) AND isset($value['$id']))
					{
						$value = $value['$id'];
					}

					$this->_related_objects[$name] = Mango_Document::factory($model, $value);
				}
			}

			return $this->_related_objects[$name];
		}

		$this->_load_if_needed($name);

		return isset($this->_object[$name])
			? $this->_object[$name]
			: NULL;
	}

	/**
	 * Magic method for setting the value of a field
	 *
	 * In order to set the value of a nested field,
	 * you must use the [Mango_Document::set] method, not the magic method.
	 *
	 * Examples:
	 * ~~~
	 * // Works
	 * $doc->set('address.city', 'Visakhapatnam');
	 *
	 * // Does not work
	 * $doc->address['city'] = 'Visakhapatnam';
	 * ~~~
	 *
	 * @param   string  $name   The name of the property being interacted with
	 * @param   mixed   $value  The value the $name'ed property should be set to
	 *
	 * @return  mixed|void
	 *
	 * @throws  Mango_Exception
	 */
	public function __set($name, $value)
	{
		$name = $this->getFieldName($name, FALSE);

		// Automatically save references to other Mango_Document objects
		if (array_key_exists($name, $this->_references))
		{
			if (isset($this->_references[$name]['setter']))
			{
				if (is_null($this->_references[$name]['setter']))
				{
					throw new Mango_Exception("':name' is read only!", array(
						':name' => $name
					));
				}
				elseif (is_string($this->_references[$name]['setter']))
				{
					return call_user_func(array($this, $this->_references[$name]['setter']), $value, $name);
				}
				else
				{
					return call_user_func(array($this, $this->_references[$name]['setter']), $value, $this, $name);
				}
			}
			if ( ! $value instanceof Mango_Document)
			{
				throw new Mango_Exception('Cannot set reference to object that is not a Mongo_Document');
			}

			$this->_related_objects[$name] = $value;

			if (isset($value->_id))
			{
				$id_field = isset($this->_references[$name]['field']) ? $this->_references[$name]['field'] : "_{$name}";
				$this->__set($id_field, $value->_id);
			}

			return;
		}

		// Do not save sets that result in no change
		$value = $this->_cast($name, $value);

		if (isset($this->_object[$name]) AND $this->_object[$name] === $value)
		{
			return;
		}

		$this->_object[$name]  = $value;
		$this->_changed[$name] = TRUE;
	}

	/**
	 * Current magic methods supported:
	 *
	 * + find_<search>() - Perform predefined search (using key from $_searches)
	 *
	 * @param string  $name       The name of the method being called
	 * @param array   $arguments  Enumerated array containing the parameters passed to the $name'ed method
	 *
	 * @return Mango_Collection|Mango_Document
	 *
	 * @throws Mango_Exception
	 */
	public function __call($name, $arguments)
	{
		// Workaround Reserved Keyword 'unset'
		// http://php.net/manual/en/reserved.keywords.php
		if ($name == 'unset')
		{
			return $this->_unset($arguments[0]);
		}

		$parts = explode('_', $name, 2);

		if ( ! isset($parts[1]))
		{
			throw new Mango_Exception('Method :name not found for :class.', array(
					':name'  => $name,
					':class' => get_class($this)
			));
		}

		switch ($parts[0])
		{
			case 'find':
				$search = $parts[1];

				if ( ! isset($this->_searches[$search]))
				{
					throw new Mango_Exception('Predefined search :search not found for :class', array(
							':search' => $search,
							':class'  => get_class($this)
					));
				}

				return Mango_Document::factory($this->_searches[$search]['model'])
							->getCollection(TRUE)
							->find(array($this->_searches[$search]['field'] => $this->_id));
			break;

			default:
				throw new Mango_Exception('Method :method not found for :class', array(
						':method' => $name,
						':class'  => get_class($this)
				));
			break;
		}
	}

	/**
	 * Override to cast values when they are set with untrusted data
	 *
	 * @param   string  $field  The field name being set
	 * @param   mixed   $value  The value being set
	 *
	 * @return  mixed|\MongoId|string
	 */
	protected function _cast($field, $value)
	{
		switch($field)
		{
			case '_id':
				// Cast _id strings to MongoIds if they convert back and forth without changing
				if (is_string($value) AND strlen($value) == 24)
				{
					$id = new MongoId($value);

					if ((string)$id == $value)
					{
						return $id;
					}
				}
		}

		return $value;
	}

	/**
	 * Unset a key
	 *
	 * [!!] Note: unset() method call for _unset() is defined in __call() method
	 *      since 'unset' method name is reserved in PHP.
	 *      (Requires PHP > 5.2.3. See: http://php.net/manual/en/reserved.keywords.php)
	 *
	 * @param  string   $name     The key of the data to update (use dot notation for embedded objects)
	 * @param  boolean  $emulate  TRUE will emulate the database function for eventual consistency, FALSE will not change the object until save & reload.
	 *
	 * @see    $_emulate
	 *
	 * @return Mango_Document
	 */
	public function _unset($name, $emulate = NULL)
	{
		$name = $this->getFieldName($name);
		$this->_operations['$unset'][$name] = 1;

		if (FALSE === $emulate OR (is_null($emulate) AND FALSE === $this->_emulate))
		{
			return $this->_setDirty($name);
		}
		else
		{
			self::unsetNamedReference($this->_object, $name);

			return $this;
		}
	}

	/**
	 * Clear the document data
	 *
	 * @return  Mango_Document
	 */
	public function clear()
	{
		$this->_object = $this->_changed = $this->_operations = $this->_dirty = $this->_related_objects = array();
		$this->_loaded = NULL;

		return $this;
	}

	/**
	 * Reload document only if there is need for it
	 *
	 * @param   string  $name  Name of the field to check for (no dot notation)
	 *
	 * @return  boolean
	 */
	protected function _load_if_needed($name)
	{
		// Reload when retrieving dirty data
		if ($this->_loaded AND empty($this->_operations) AND ! empty($this->_dirty[$name]))
		{
			return $this->load();
		}
		// Lazy loading!
		elseif (is_null($this->_loaded) AND isset($this->_object['_id']) AND ! isset($this->_changed['_id']) AND $name != '_id')
		{
			return $this->load();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Load the document from the database
	 *
	 * The first parameter may be one of:
	 *
	 * + a FALSE value - the object data will be used to construct the query
	 * + a JSON string - will be parsed and used for the query
	 * + an non-array value - the query will be assumed to be for an _id of this value
	 * + an array - the array will be used for the query
	 *
	 * @param   string|array  $criteria  Specify additional criteria
	 * @param   array         $fields    Specify the fields to return [Optional]
	 *
	 * @return  boolean  TRUE if the load succeeded
	 *
	 * @throws  Mango_Exception
	 *
	 * @uses    JSON::decode
	 */
	public function load($criteria = array(), array $fields = array())
	{
		$keepId = NULL;

		if (is_string($criteria) AND $criteria[0] == "{")
		{
			$criteria = JSON::decode($criteria, TRUE);
		}
		elseif ($criteria AND ! is_array($criteria))
		{
			// in case if we won't load it, we should set this object to this id
			$keepId   = $criteria;
			$criteria = array('_id' => $criteria);
		}
		elseif (isset($this->_object['_id']))
		{
			// in case if we won't load it, we should set this object to this id
			$keepId   = $this->_object['_id'];
			$criteria = array('_id' => $this->_object['_id']);
		}
		elseif (isset($criteria['id']))
		{
			// in case if we won't load it, we should set this object to this id
			$keepId   = $criteria['id'];
			$criteria = array('_id' => $criteria['id']);
		}
		elseif ( ! $criteria)
		{
			$criteria = $this->_object;
		}

		if ( ! $criteria)
		{
			throw new Mango_Exception('Cannot find :class without _id or other search criteria.', array(
				':class' => get_class($this)
			));
		}

		// Cast query values to the appropriate types and translate aliases
		$new = array();

		foreach ($criteria as $key => $value)
		{
			$key       = $this->getFieldName($key);
			$new[$key] = $this->_cast($key, $value);
		}

		$criteria = $new;

		// Translate field aliases
		$fields = array_map(array($this,'getFieldName'), $fields);
		$values = $this->getCollection()->__call('findOne', array($criteria, $fields));

		// Only clear the object if necessary
		if ( ! is_null($this->_loaded) OR $this->_changed OR $this->_operations)
		{
			$this->clear();
		}

		$this->load_values($values, TRUE);

		if ( ! $this->_loaded AND $keepId)
		{
			// restore the id previously set on this object...
			$this->id = $keepId;
		}

		return $this->_loaded;
	}

	/**
	 * Load all of the values in an associative array
	 *
	 * Ignores all fields not in the model.
	 *
	 * @param   array    $values  field => value pairs
	 * @param   boolean  $clean   values are clean (from database)?
	 *
	 * @return  Mango_Document
	 */
	public function load_values(array $values, $clean = FALSE)
	{
		if ($clean === TRUE)
		{
			$this->_before_load();

			$this->_object = $values;
			$this->_loaded = ! empty($this->_object);

			$this->_after_load();
		}
		else
		{
			foreach ($values as $field => $value)
			{
				$this->__set($field, $value);
			}
		}

		return $this;
	}

	/**
	 * Set dirty
	 *
	 * @param   string  $name  Field name
	 *
	 * @return  Mango_Document
	 */
	protected function _setDirty($name)
	{
		if ($pos = strpos($name, '.'))
		{
			$name = substr($name, 0, $pos);
		}

		$this->_dirty[$name] = TRUE;

		return $this;
	}

	/**
	 * Unset a field using dot notation
	 *
	 * @param  array  $arr   Array with data
	 * @param  string $name  Name with dot notation
	 */
	public static function unsetNamedReference(&$arr, $name)
	{
		$keys = explode('.', $name);
		$data = &$arr;

		foreach ($keys as $i => $key)
		{
			if (isset($data[$key]))
			{
				if ($i == count($keys) - 1)
				{
					unset($data[$key]);
					return;
				}

				$data = &$data[$key];
			}
			else
			{
				return;
			}
		}
	}

	/**
	 * Get the value for a key (using dot notation)
	 *
	 * @param   string  $name     Key name
	 * @param   mixed   $default  Default value [Optional]
	 *
	 * @return  mixed
	 */
	public function get($name, $default = NULL)
	{
		$name   = $this->getFieldName($name);
		$dotPos = strpos($name, '.');

		if ( ! $dotPos AND is_null($default))
		{
			return $this->__get($name);
		}

		$this->_load_if_needed($dotPos ? substr($name, 0, $dotPos) : $name);

		$ref = $this->getFieldReference($name, FALSE, $default);

		return $ref;
	}

	/**
	 * Returns direct reference to a field, using dot notation
	 *
	 * @param   string   $name     Name with dot notation
	 * @param   boolean  $create   TRUE to create a field if it's missing [Optional]
	 * @param   mixed    $default  Use default value to create missing fields, or return it if $create == FALSE [Optional]
	 *
	 * @return  mixed
	 */
	public function &getFieldReference($name, $create = FALSE, $default = NULL)
	{
		return self::getNamedReference($this->_object, $name, $create, $default);
	}

	/**
	 * Returns direct reference to a field, using dot notation
	 *
	 * @param   array    $arr      Array with data
	 * @param   string   $name     Dot notation name
	 * @param   boolean  $create   TRUE to create a field if it's missing [Optional]
	 * @param   mixed    $default  Use default value to create missing fields, or return it if $create == FALSE [Optional]
	 *
	 * @return  mixed
	 */
	public static function &getNamedReference(&$arr, $name, $create = FALSE, $default = NULL)
	{
		$keys = explode('.', $name);
		$data = & $arr;

		foreach ($keys as $i => $key)
		{
			if ( ! isset($data[$key]))
			{
				if ($create)
				{
					$data[$key] = $i == count($keys) - 1 ? $default : array();
				}
				else
				{
					$nil = $default;
					return $nil;
				}
			}
			$data = & $data[$key];
		}

		return $data;
	}

	/**
	 * Get a corresponding collection singleton
	 *
	 * @param   boolean  $new  Pass TRUE if you don't want to get the singleton instance [Optional]
	 *
	 * @return  Mango_Collection
	 *
	 * @uses    Mango::$default
	 */
	public function getCollection($new = FALSE)
	{
		if ($new)
		{
			if ($this->_name)
			{
				if (is_null($this->_db))
				{
					$this->_db = Mango::$default;
				}

				return new Mango_Collection($this->_name, $this->_db, $this->_gridFS, get_class($this));
			}
			else
			{
				$class_name = $this->getCollectionClass();

				return new $class_name(NULL, NULL, NULL, get_class($this));
			}
		}

		if ($this->name)
		{
			$name = "{$this->_db}.{$this->_name}.{$this->_gridFS}";

			if ( ! isset(self::$_collections[$name]))
			{
				if (is_null($this->_db))
				{
					$this->_db = Mango::$default;
				}

				self::$_collections[$name] = new Mango_Collection($this->_name, $this->_db, $this->_gridFS, get_class($this));
			}

			return self::$_collections[$name];
		}
		else
		{
			$name = $this->getCollectionClass();

			if ( ! isset(self::$_collections[$name]))
			{
				self::$_collections[$name] = new $name(NULL, NULL, NULL, get_class($this));
			}

			return self::$_collections[$name];
		}
	}

	/**
	 * Gets the collection name
	 *
	 * @return  string
	 */
	public function getCollectionClass()
	{
		return get_class($this).'_Collection';
	}

	/**
	 * This function translates an alias to a database field name
	 *
	 * Aliases are defined in [Mango_Document::$_aliases], and `id` is always aliased to `_id`.
	 * You can override this to disable aliases or define your own aliasing technique.
	 *
	 * @param   string   $name  The aliased field name
	 * @param   boolean  $dot   Use FALSE if a dot is not allowed in the field name for better performance [Optional]
	 *
	 * @return  string   The field name used within the database
	 */
	public function getFieldName($name, $dot = TRUE)
	{
		if ($name == 'id' OR $name == '_id')
		{
			return '_id';
		}

		if ( ! $dot OR ! strpos($name, '.'))
		{
			return (isset($this->_aliases[$name])
				? $this->_aliases[$name]
				: $name
			);
		}

		$parts    = explode('.', $name, 2);
		$parts[0] = $this->getFieldName($parts[0], FALSE);

		return implode('.', $parts);
	}

	/**
	 * Override this method to take certain actions before the data is saved
	 *
	 * @param  string  $action  The type of save action, one of Mango_Document::SAVE_*
	 */
	protected function _before_save($action) {}

	/**
	 * Override this method to take actions after data is saved
	 *
	 * @param  string  $action  The type of save action, one of Mango_Document::SAVE_*
	 */
	protected function _after_save($action) {}

	/**
	 * Override this method to take actions before the values are loaded
	 */
	protected function _before_load() {}

	/**
	 * Override this method to take actions after the values are loaded
	 */
	protected function _after_load() {}

	/**
	 * Override this method to take actions before the document is deleted
	 */
	protected function _before_delete() {}

	/**
	 * Override this method to take actions after the document is deleted
	 */
	protected function _after_delete() {}
}