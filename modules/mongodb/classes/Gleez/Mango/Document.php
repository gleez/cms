<?php
/**
 * Gleez CMS (http://gleezcms.org)
 *
 * @link https://github.com/cleez/cms Canonical source repository
 * @copyright Copyright (c) 2011-2014 Gleez Technologies
 * @license http://gleezcms.org/license Gleez CMS License
 */

namespace Gleez\Mango;

use Traversable;
use JSON;
use MongoId;

/**
 * Gleez Mongo Document
 *
 * This class objectifies a \MongoDB document.
 *
 * @property   \MongoId $_id
 *
 * @package    Gleez\Mango
 * @author     Gleez Team
 * @version    1.0.0
 */
abstract class Document
{
	const INSERT = 'insert';
	const UPDATE = 'update';
	const UPSERT = 'upsert';

	/**
	 * Array of document factory names.
	 * You can specify own factory name to class mapping.
	 *
	 * Example:<br>
	 * <code>
	 * \Gleez\Mango\Document::$models['foo'] = 'Model_My_Foo';
	 * </code>
	 *
	 * @var array
	 */
	public static $models = array();

	/**
	 * The name of the collection within the database or the gridFS prefix if
	 * [\Gleez\Mango\Document::$gridFS] is true. If using a corresponding [\Gleez\Mango\Collection]
	 * subclass, set this only in the [\Gleez\Mango\Collection] subclass.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The database configuration name (passed to [Mango::instance]).
	 * If using a corresponding [\Gleez\Mango\Collection] subclass, set this only
	 * in the [\Gleez\Mango\Collection] subclass.
	 *
	 * @var string
	 */
	protected $db;

	/**
	 * Whether or not this collection is a gridFS collection. If using a corresponding
	 * [\Gleez\Mango\Collection] subclass, set this only in the [\Gleez\Mango\Collection] subclass.
	 *
	 * @var boolean
	 */
	protected $gridFS = false;

	/**
	 * Definition of references existing in this document. If `model` is not specified it
	 * defaults to the reference name. If `field` is not specified it defaults to the
	 * reference name prefixed with an `_`.
	 *
	 * Example:<br>
	 * <code>
	 * // Example Document: {_id: 1, user_id: 2, _token: 3}
	 * protected $references = array(
	 *     'user' => array(
	 *         'model' => 'user',
	 *         'field' => 'user_id'
	 *     ),
	 *     'token' => null,
	 * );
	 * </code>
	 *
	 * You can also specify getter and setter functions:
	 *
	 * + `getter` - null will make the value write-only,
	 *              string will call `$this->{$getter}($name)`,
	 *              callable will be called as `$getter($this, $name)`
	 * + `setter` - null will make the value read-only,
	 *              string will call $this->{$setter}($value),
	 *              callable will be called as $setter($value, $this, $name)
	 *
	 * @var array
	 */
	protected $references = array();

	/**
	 * Definition of predefined searches for use with [\Gleez\Mango\Document::__call].
	 * This instantiates a collection for the target model and initializes the
	 * search with the specified field being equal to the `_id` of the current object.
	 *
	 * Example<br>:
	 * <code>
	 * $searches
	 * {events: {model: 'event', field: '_user'}}
	 * // db.event.find({_user: <_id>})
	 * </code>
	 *
	 * @var array
	 */
	protected $searches = array();

	/**
	 * Field name aliases.
	 * `_id` is automatically aliased to `id`.
	 *
	 * @var array
	 */
	protected $aliases = array();

	/**
	 * If set to `true`, operator functions (set, inc, push etc.)
	 * will emulate database functions for eventual consistency.
	 *
	 * Example:<br>
	 * <code>
	 * $doc = new \Gleez\Mango\Document;
	 *
	 * $doc->emulate = true;
	 * $doc->number = 1;
	 * $doc->inc('number');
	 *
	 * // This will print '2'
	 * echo $doc->number;
	 * </code>
	 *
	 * If set to `false`, the field will be marked as dirty, and the new
	 * value will only be available after reload.
	 *
	 * Example:<br>
	 * <code>
	 * $doc = new \Gleez\Mango\Document;
	 *
	 * $doc->emulate = false;
	 * $doc->number = 1;
	 * $doc->inc('number');
	 *
	 * // This will print '1'
	 * echo $doc->number;
	 * </code>
	 *
	 * @var boolean
	 */
	protected $emulate  = false;

	/**
	 * Internal storage of object data
	 * @var array
	 */
	protected $object = array();

	/**
	 * Keep track of fields changed using [\Gleez\Mango\Document::__set]
	 * or [\Gleez\Mango\Document::load_values]
	 *
	 * @var array
	 */
	protected $changed = array();

	/**
	 * Set of operations to perform on update/insert
	 * @var array
	 */
	protected $operations = array();

	/**
	 * Keep track of data that is dirty
	 * (changed by an operation but not yet updated from database)
	 *
	 * @var array
	 */
	protected $dirty = array();

	/**
	 * Storage for referenced objects
	 * @var array
	 */
	protected $relatedObjects = array();

	/**
	 * Document loaded status
	 *
	 * <pre>
	 * null  - not attempted
	 * false - failed
	 * true  - succeeded
	 * </pre>
	 *
	 * @var boolean
	 */
	protected $loaded = null;

	/**
	 * A cache of [\Gleez\Mango\Collection] instances for performance
	 * @var array
	 */
	protected static $collections = array();

	/**
	 * Designated place for non-persistent data storage
	 * (will not be saved to the database or after sleep)
	 *
	 * @var array
	 */
	public $data = array();

	/**
	 * Instantiate an object conforming to \Gleez\Mango\Document conventions
	 *
	 * [!!] Note: The document is not loaded until [\Gleez\Mango\Document::load] is called.
	 *
	 * Example:<br>
	 * <code>
	 * // Attempts to create the Model_Post object
	 * $model = \Gleez\Mango\Document::factory('post');
	 *
	 * // Attempts to create the Model_User object
	 * $model = \Gleez\Mango\Document::factory('User');
	 *
	 * // Attempts to create the Model_Document_First object
	 * $model = \Gleez\Mango\Document::factory('document_first');
	 *
	 * // Attempts to create the Document object
	 * $model = \Gleez\Mango\Document::factory('\Document');
	 *
	 * // Attempts to create the \Document\Last object
	 * $model = \Gleez\Mango\Document::factory('\document\last');
	 * </code>
	 *
	 * @param   string $name  Model name
	 * @param   mixed  $id    The _id of the document to operate on or criteria used to load [Optional]
	 *
	 * @return  \Gleez\Mango\Document
	 */
	public static function factory($name, $id = null)
	{
		if (isset(static::$models[$name]))
			$class = static::$models[$name];
		elseif (false !== strpos($name, '\\'))
			$class = implode('\\', array_map('ucfirst', explode('\\', preg_replace('#(?<!:)//+#', '/', $name))));
		else
			$class = 'Model_' . implode('_', array_map('ucfirst', explode('_', $name)));

		return new $class($id);
	}

	/**
	 * Instantiate a new Document object
	 *
	 * If an id or other data is passed then it will be assumed that
	 * the document exists in the database and updates will be performed without loading the document first.
	 *
	 * @param   mixed  $id  _id of the document to operate on or criteria used to load [Optional]
	 *
	 * @return  \Gleez\Mango\Document
	 */
	public function __construct($id = null)
	{
		if (!empty($id)) {
			if (is_array($id) || $id  instanceof Traversable) {
				foreach ($id as $key => $value)
					$this->object[$this->getFieldName($key)] = $value;
			} else
				$this->object['_id'] = $this->cast('_id', $id);
		}
	}

	/**
	 * Returns the attributes that should be serialized
	 *
	 * @return  array
	 */
	public function __sleep()
	{
		return array('references', 'aliases', 'object', 'changed', 'operations', 'loaded', 'dirty');
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
		$name = $this->getFieldName($name, false);

		if (isset($this->object[$name]) || $this->get($name))
			return true;

		return isset($this->object[$name]);
	}

	/**
	 * Unset a field
	 *
	 * @param  string  $name  Field name
	 */
	public function __unset($name)
	{
		$this->unsetKey($name);
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
	 * @throws \Gleez\Mango\Exception
	 */
	public function __get($name)
	{
		$name = $this->getFieldName($name, false);

		// Auto-loading for special references
		if (array_key_exists($name, $this->references)) {
			if (isset($this->references[$name]['getter'])) {
				if (is_null($this->references[$name]['getter']))
					throw new Exception(':name is write only!', array(':name' => $name));
				elseif (is_string($this->references[$name]['getter']))
					return call_user_func(array($this, $this->references[$name]['getter']), $name);
				else
					return call_user_func(array($this, $this->references[$name]['getter']), $this, $name);
			}

			if (!isset($this->relatedObjects[$name])) {
				$model = isset($this->references[$name]['model'])
					? $this->references[$name]['model']
					: $name;

				$foreign_field = isset($this->references[$name]['foreign_field'])
					? $this->references[$name]['foreign_field']
					: false;

				if ($foreign_field) {
					$this->relatedObjects[$name] = static::factory($model)
						->getCollection(true)
						->find($foreign_field, $this->_id);

					return $this->relatedObjects[$name];
				}

				$id_field = isset($this->references[$name]['field'])
					? $this->references[$name]['field']
					: "_{$name}";

				$value = $this->__get($id_field);

				if (!empty($this->references[$name]['multiple']))
				{
					$this->relatedObjects[$name] = static::factory($model)
						->getCollection(true)
						->find(array('_id' => array('$in' => (array) $value)));
				}
				else
				{
					// Extract just id if value is a DBRef
					if (is_array($value) && isset($value['$id']))
					{
						$value = $value['$id'];
					}

					$this->relatedObjects[$name] = static::factory($model, $value);
				}
			}

			return $this->relatedObjects[$name];
		}

		$this->lazyLoad($name);

		return isset($this->object[$name])
			? $this->object[$name]
			: null;
	}

	/**
	 * Magic method for setting the value of a field
	 *
	 * In order to set the value of a nested field,
	 * you must use the [\Gleez\Mango\Document::set] method, not the magic method.
	 *
	 * Examples:<br>
	 * <code>
	 * // Works
	 * $doc->set('address.city', 'Visakhapatnam');
	 *
	 * // Does not work
	 * $doc->address['city'] = 'Visakhapatnam';
	 * </code>
	 *
	 * @param   string  $name   The name of the property being interacted with
	 * @param   mixed   $value  The value the $name'ed property should be set to
	 *
	 * @return  mixed|null
	 *
	 * @throws  \Gleez\Mango\Exception
	 */
	public function __set($name, $value)
	{
		$name = $this->getFieldName($name, false);

		// Automatically save references to other Document objects
		if (array_key_exists($name, $this->references)) {
			if (isset($this->references[$name]['setter'])) {
				if (is_null($this->references[$name]['setter']))
					throw new Exception(':name is read only!', array(':name' => $name));
				elseif (is_string($this->references[$name]['setter']))
					return call_user_func(array($this, $this->references[$name]['setter']), $value, $name);
				else
					return call_user_func(array($this, $this->references[$name]['setter']), $value, $this, $name);
			}
			if (!$value instanceof Document)
				throw new Exception('Cannot set reference to object that is not a Document');

			$this->relatedObjects[$name] = $value;

			if (isset($value->_id)) {
				$id_field = isset($this->references[$name]['field']) ? $this->references[$name]['field'] : "_{$name}";
				$this->__set($id_field, $value->_id);
			}

			return null;
		}

		// Do not save sets that result in no change
		$value = $this->cast($name, $value);

		if (isset($this->object[$name]) && $this->object[$name] === $value)
			return null;

		$this->object[$name]  = $value;
		$this->changed[$name] = true;
	}

	/**
	 * Current magic methods supported:
	 *
	 * + find_<search>() - Perform predefined search (using key from $searches)
	 *
	 * @param string  $name       The name of the method being called
	 * @param array   $arguments  Enumerated array containing the parameters passed to the $name'ed method
	 *
	 * @return \Gleez\Mango\Collection|\Gleez\Mango\Document
	 *
	 * @throws \Gleez\Mango\Exception
	 */
	public function __call($name, $arguments)
	{
		// Workaround Reserved Keyword 'unset'
		if ($name == 'unset')
			return $this->unsetKey($arguments[0]);

		$parts = explode('_', $name, 2);

		if (!isset($parts[1]))
			throw new Exception('Method :name not found for :class.', array(
					':name'  => $name,
					':class' => get_class($this)
			));

		switch ($parts[0]) {
			case 'find':
				$search = $parts[1];

				if (!isset($this->searches[$search]))
					throw new Exception('Predefined search :search not found for :class', array(
							':search' => $search,
							':class'  => get_class($this)
					));

				return static::factory($this->searches[$search]['model'])
							->getCollection(true)
							->find(array($this->searches[$search]['field'] => $this->_id));
				break;
			default:
				throw new Exception('Method :method not found for :class.', array(
						':method' => $name,
						':class'  => get_class($this)
				));
				break;
		}
	}

	/**
	 * Override to cast values when they are set with untrusted data
	 *
	 * @param   mixed  $field  The field name being set
	 * @param   mixed  $value  The value being set
	 *
	 * @return  mixed|\MongoId|string
	 */
	protected function cast($field, $value)
	{
		switch($field) {
			case '_id':
				// Cast _id strings to MongoIds if they convert back and forth without changing
				if ($value instanceof MongoId)
					return $value;
				if ((is_string($value) || ctype_xdigit($value) || (is_object($value) && method_exists($value, '__toString'))) && strlen($value) == 24) {
					$id = new MongoId($value);

					if ((string) $id == $value)
						return $id;
				}
		}

		return $value;
	}

	/**
	 * Unset a key
	 *
	 * [!!] Note: unset() method call for unsetKey() is defined in __call() method
	 *      since 'unset' method name is reserved in PHP.
	 *      (Requires PHP > 5.2.3. See: http://php.net/manual/en/reserved.keywords.php)
	 *
	 * @param  string   $name     The key of the data to update (use dot notation for embedded objects)
	 * @param  boolean  $emulate  true will emulate the database function for eventual consistency, false will not change the object until save & reload.
	 *
	 * @see    $emulate
	 *
	 * @return \Gleez\Mango\Document
	 */
	public function unsetKey($name, $emulate = null)
	{
		$name = $this->getFieldName($name);
		$this->operations['$unset'][$name] = 1;

		if (false === $emulate OR (is_null($emulate) && false === $this->emulate))
			return $this->setDirty($name);
		else {
			static::unsetNamedReference($this->object, $name);

			return $this;
		}
	}

	/**
	 * Clear the document data
	 *
	 * @return  \Gleez\Mango\Document
	 */
	public function clear()
	{
		$this->object = $this->changed = $this->operations = $this->dirty = $this->relatedObjects = array();
		$this->loaded = null;

		return $this;
	}

	/**
	 * Reload document only if there is need for it
	 *
	 * @param   string  $name  Name of the field to check for (no dot notation)
	 *
	 * @return  boolean
	 */
	protected function lazyLoad($name)
	{
		// Reload when retrieving dirty data
		if ($this->loaded && empty($this->operations) && ! empty($this->dirty[$name]))
			return $this->load();
		// Lazy loading!
		elseif (is_null($this->loaded) && isset($this->object['_id']) && ! isset($this->changed['_id']) && $name != '_id')
			return $this->load();
		else
			return false;
	}

	/**
	 * Load the document from the database
	 *
	 * The first parameter may be one of:
	 *
	 * + a false value - the object data will be used to construct the query
	 * + a JSON string - will be parsed and used for the query
	 * + an non-array value - the query will be assumed to be for an _id of this value
	 * + an array - the array will be used for the query
	 *
	 * @param   string|array  $criteria  Specify additional criteria [Optional]
	 * @param   array         $fields    Specify the fields to return [Optional]
	 *
	 * @return  boolean  true if the load succeeded
	 *
	 * @throws  \Gleez\Mango\Exception
	 *
	 * @uses    \JSON::decode
	 */
	public function load($criteria = array(), array $fields = array())
	{
		$keepId = null;

		if (is_string($criteria) && $criteria[0] == "{")
			$criteria = JSON::decode($criteria, true);
		elseif ($criteria && !is_array($criteria)) {
			// in case if we won't load it, we should set this object to this id
			$keepId   = $criteria;
			$criteria = array('_id' => $criteria);
		} elseif (isset($this->object['_id'])) {
			// in case if we won't load it, we should set this object to this id
			$keepId   = $this->object['_id'];
			$criteria = array('_id' => $this->object['_id']);
		} elseif (isset($criteria['id'])) {
			// in case if we won't load it, we should set this object to this id
			$keepId   = $criteria['id'];
			$criteria = array('_id' => $criteria['id']);
		} elseif (!$criteria)
			$criteria = $this->object;

		if (!$criteria)
			throw new Exception('Cannot find :class without _id or other search criteria.', array(':class' => get_class($this)));

		// Cast query values to the appropriate types and translate aliases
		$new = array();

		foreach ($criteria as $key => $value) {
			$key       = $this->getFieldName($key);
			$new[$key] = $this->cast($key, $value);
		}

		$criteria = $new;

		// Translate field aliases
		$fields = array_map(array($this, 'getFieldName'), $fields);
		$values = $this->getCollection()->__call('findOne', array($criteria, $fields));

		// Only clear the object if necessary
		if (!is_null($this->loaded) OR $this->changed OR $this->operations)
			$this->clear();

		$this->loadValues($values ?: array(), true);

		if (!$this->loaded && $keepId)
			// restore the id previously set on this object...
			$this->_id = $keepId;

		return $this->loaded;
	}

	/**
	 * Whether the document is loaded?
	 *
	 * @since   1.0.0
	 *
	 * @return  bool
	 */
	public function isLoaded()
	{
		if (null === $this->loaded)
			$this->load();

		return $this->loaded;
	}

	/**
	 * Update assumed existing document
	 *
	 * @since   1.0.0
	 *
	 * @return  \Gleez\Mango\Document
	 * @throws  \Gleez\Mango\Exception
	 */
	public function update()
	{
		$this->beforeSave(static::UPDATE);

		if ($this->changed) {
			foreach ($this->changed as $name => $changed)
				$this->operations['$set'][$name] = $this->object[$name];
		}

		if ($this->operations) {
			if (!$this->getCollection()->update(array('_id' => $this->object['_id']), $this->operations)) {
				$err = $this->getClientInstance()->lastError();
				throw new Exception('Update of :class failed: :err', array(':class' => get_class($this), ':err' => $err['err']));
			}
		}

		$this->changed = $this->operations = array();
		$this->afterSave(static::UPDATE);

		return $this;
	}

	/**
	 * Insert new record.
	 *
	 * For newly created documents the _id will be retrieved.
	 *
	 * @since   1.0.0
	 *
	 * @param   array $options Insert options [Optional]
	 *
	 * @return  \Gleez\Mango\Document
	 * @throws  \Gleez\Mango\Exception
	 */
	public function create(array $options = array())
	{
		$this->beforeSave(static::INSERT);

		// Prepare options before insert
		$writeConcern = $this->getClientInstance()->getWriteConcern();
		$w = $writeConcern['w'] == 0 ? 1 : $writeConcern['w'];
		$wtimeout = $writeConcern['wtimeout'];

		if (isset($options['safe'])) {
			$options['w'] = (int) $options['safe'];
			$options['j'] = (bool) $options['safe'];
			unset($options['safe']);
		}

		if (!isset($options['w']))
			$options['w'] = $w;

		if (!isset($options['wtimeout']))
			$options['wtimeout'] = $wtimeout;

		$values = array();
		foreach($this->changed as $name => $changed)
			$values[$name] = $this->object[$name];

		if (empty($values))
			throw new Exception('Cannot insert empty array.');

		$result = $this->getCollection()->insert($values, $options);

		if ($result['err'] && (!empty($options['w']) || !empty($options['j'])))
			throw new Exception('Unable to insert :class: :err', array(':class' => get_class($this), ':err' => $result['err']));

		if (!isset($this->object['_id'])) {
			// Store (assigned) MongoID in object
			$this->object['_id'] = $values['_id'];
			$this->loaded = true;
		}

		// Save any additional operations
		if ($this->operations) {
			if (!$this->getCollection()->update(array('_id' => $this->object['_id']), $this->operations)) {
				$err = $this->getClientInstance()->lastError();
				throw new Exception('Save :class failed: :err', array(':class' => get_class($this), ':err' => $err['err']));
			}
		}

		$this->changed = $this->operations = array();
		$this->afterSave(static::INSERT);

		return $this;
	}

	/**
	 * Document is new?
	 *
	 * @since   1.0.0
	 *
	 * @return  bool
	 */
	public function isNew()
	{
		// if no _id or _id was set by user
		return (!isset($this->object['_id']) || isset($this->changed['_id']));
	}

	/**
	 * Field has been changed?
	 *
	 * No parameter returns true if there are *any* changes.
	 *
	 * @param   string $name Field name [Optional]
	 *
	 * @since   1.0.0
	 *
	 * @return  bool
	 */
	public function isChanged($name = null)
	{
		if(is_null($name))
			return ($this->changed || $this->operations);
		else
			return isset($this->changed[$this->getFieldName($name)]) || isset($this->dirty[$this->getFieldName($name)]);
	}

	/**
	 * Updates or Creates and save the Document depending on isNew()
	 *
	 * @since   1.0.0
	 *
	 * @param   array|bool $options Insert options [Optional]
	 *
	 * @return  \Gleez\Mango\Document
	 * @throws  \Gleez\Mango\Exception
	 */
	public function save(array $options = array())
	{
		// Update references to referenced models
		$this->updateReferences();

		return $this->isNew() ? $this->create($options) : $this->update();
	}

	/**
	 * Delete the current document using the current data.
	 *
	 * The document does not have to be loaded.
	 * Use <code>$doc->getCollection()->remove($criteria)</code> to delete multiple documents.
	 *
	 * @since   1.0.0
	 *
	 * @return  \Gleez\Mango\Document
	 * @throws  \Gleez\Mango\Exception
	 */
	public function delete()
	{
		if (!isset($this->object['_id']))
			throw new Exception('Cannot delete new document :class', array(':class' => get_class($this)));

		$this->beforeDelete();

		$criteria = array('_id' => $this->object['_id']);

		if (!$this->getCollection()->remove($criteria, array('justOne' => true)))
			throw new Exception('Failed to delete :class', array(get_class($this)));

		$this->clear();
		$this->afterDelete();

		return $this;
	}

	/**
	 * Load all of the values in an associative array
	 *
	 * Ignores all fields not in the model.
	 *
	 * @since   0.0.1
	 * @since   1.0.0 load_values → loadValues
	 *
	 * @param   array    $values  field => value pairs
	 * @param   boolean  $clean   Values are clean (from database)? [Optional]
	 *
	 * @return  \Gleez\Mango\Document
	 */
	public function loadValues(array $values, $clean = false)
	{
		if ($clean === true) {
			$this->beforeLoad();

			$this->object = $values;
			$this->loaded = ! empty($this->object);

			$this->afterLoad();
		} else
			foreach ($values as $field => $value)
				$this->__set($field, $value);

		return $this;
	}

	/**
	 * Set dirty
	 *
	 * @param   string  $name  Field name
	 *
	 * @return  \Gleez\Mango\Document
	 */
	protected function setDirty($name)
	{
		if ($pos = strpos($name, '.'))
			$name = substr($name, 0, $pos);

		$this->dirty[$name] = true;

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

		foreach ($keys as $i => $key) {
			if (isset($data[$key])) {
				if ($i == count($keys) - 1) {
					unset($data[$key]);
					return;
				}

				$data = &$data[$key];
			}
			else
				return;
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
	public function get($name, $default = null)
	{
		$name   = $this->getFieldName($name);
		$dotPos = strpos($name, '.');

		if (!$dotPos && is_null($default))
			return $this->__get($name);

		$this->lazyLoad($dotPos ? substr($name, 0, $dotPos) : $name);

		$ref = $this->getFieldReference($name, false, $default);

		return $ref;
	}

	/**
	 * Returns direct reference to a field, using dot notation
	 *
	 * @param   string   $name     Name with dot notation
	 * @param   boolean  $create   true to create a field if it's missing [Optional]
	 * @param   mixed    $default  Use default value to create missing fields, or return it if $create == false [Optional]
	 *
	 * @return  mixed
	 */
	public function getFieldReference($name, $create = false, $default = null)
	{
		return static::getNamedReference($this->object, $name, $create, $default);
	}

	/**
	 * Returns direct reference to a field, using dot notation
	 *
	 * @param   array    $arr      Array with data
	 * @param   string   $name     Dot notation name
	 * @param   boolean  $create   true to create a field if it's missing [Optional]
	 * @param   mixed    $default  Use default value to create missing fields, or return it if $create == false [Optional]
	 *
	 * @return  mixed
	 */
	public static function getNamedReference($arr, $name, $create = false, $default = null)
	{
		$keys = explode('.', $name);
		$data = $arr;

		foreach ($keys as $i => $key) {
			if (!isset($data[$key])) {
				if ($create)
					$data[$key] = $i == count($keys) - 1 ? $default : array();
				else {
					$nil = $default;
					return $nil;
				}
			}

			$data = $data[$key];
		}

		return $data;
	}

	/**
	 * Return the \Gleez\Mango\Client reference (proxy to the collection's getClientInstance() method)
	 *
	 * @since   1.0.0
	 *
	 * @return  \Gleez\Mango\Client
	 */
	public function getClientInstance()
	{
		return $this->getCollection()->getClientInstance();
	}

	/**
	 * Get a corresponding collection singleton
	 *
	 * @param   boolean  $new  Pass true if you don't want to get the singleton instance [Optional]
	 *
	 * @return  \Gleez\Mango\Collection
	 *
	 * @uses    \Gleez\Mango\Client::$default
	 */
	public function getCollection($new = false)
	{
		if ($new) {
			if ($this->name) {
				if (is_null($this->db))
					$this->db = Client::$default;

				return new Collection($this->name, $this->db, $this->gridFS, get_class($this));
			} else {
				$className = $this->getCollectionClass();

				return new $className(null, null, null, get_class($this));
			}
		}

		if ($this->name) {
			$name = "{$this->db}.{$this->name}.{$this->gridFS}";

			if (!isset(static::$collections[$name])) {
				if (is_null($this->db))
					$this->db = Client::$default;

				static::$collections[$name] = new Collection($this->name, $this->db, $this->gridFS, get_class($this));
			}

			return static::$collections[$name];
		} else {
			$name = $this->getCollectionClass();

			if (!isset(static::$collections[$name]))
				static::$collections[$name] = new $name(null, null, null, get_class($this));

			return static::$collections[$name];
		}
	}

	/**
	 * Gets the collection name
	 *
	 * @return  string
	 */
	public function getCollectionClass()
	{
		return Client::instance()->getCollectionClass();
	}

	/**
	 * This function translates an alias to a database field name
	 *
	 * Aliases are defined in [\Gleez\Mango\Document::$aliases], and `id` is always aliased to `_id`.
	 * You can override this to disable aliases or define your own aliasing technique.
	 *
	 * @param   string   $name  The aliased field name
	 * @param   boolean  $dot   Use false if a dot is not allowed in the field name for better performance [Optional]
	 *
	 * @return  string   The field name used within the database
	 */
	public function getFieldName($name, $dot = true)
	{
		if ($name == 'id' || $name == '_id')
			return '_id';

		if (!$dot || ! strpos($name, '.'))
			return (isset($this->aliases[$name]) ? $this->aliases[$name] : $name);

		$parts    = explode('.', $name, 2);
		$parts[0] = $this->getFieldName($parts[0], false);

		return implode('.', $parts);
	}

	/**
	 * Updates references but does not save models to avoid infinite loops
	 */
	protected function updateReferences()
	{
		foreach ($this->references as $name => $ref) {
			if (isset($this->relatedObjects[$name]) && $this->relatedObjects[$name] instanceof Document) {
				$model = $this->relatedObjects[$name];
				$idField = isset($ref['field']) ? $ref['field'] : "_$name";

				if (!$this->__isset($idField) || $this->__get($idField) != $model->_id) {
					$this->__set($idField, $model->_id);
				}
			}
		}
	}

	/**
	 * Override this method to take certain actions before the data is saved
	 *
	 * @param  string  $action  The type of save action, one of \Gleez\Mango\Document::SAVE_*
	 */
	protected function beforeSave($action) {}

	/**
	 * Override this method to take actions after data is saved
	 *
	 * @param  string  $action  The type of save action, one of \Gleez\Mango\Document::SAVE_*
	 */
	protected function afterSave($action) {}

	/**
	 * Override this method to take actions before the values are loaded
	 */
	protected function beforeLoad() {}

	/**
	 * Override this method to take actions after the values are loaded
	 */
	protected function afterLoad() {}

	/**
	 * Override this method to take actions before the document is deleted
	 */
	protected function beforeDelete() {}

	/**
	 * Override this method to take actions after the document is deleted
	 */
	protected function afterDelete() {}
}
