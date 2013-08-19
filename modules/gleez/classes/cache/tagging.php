<?php
/**
 * Gleez [Cache](api/Cache) Tagging Interface
 *
 * @package    Gleez\Cache\Base
 * @author     Kohana Team
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2009-2012 Kohana Team
 * @copyright  (c) 2012-2013 Gleez Technologies
 * @license    http://kohanaphp.com/license
 * @license    http://gleezcms.org/license Gleez CMS License
 */
interface Cache_Tagging {

	/**
	 * Set a value based on an id. Optionally add tags.
	 *
	 * [!!] Note: Some caching engines don't support tagging
	 *
	 * @param   string   $id        ID of cache entry
	 * @param   mixed    $data      Data to set to cache
	 * @param   integer  $lifetime  Lifetime in seconds [Optional]
	 * @param   array    $tags      Tags [Optional]
	 *
	 * @return  boolean
	 */
	public function set_with_tags($id, $data, $lifetime = NULL, array $tags = NULL);

	/**
	 * Delete cache entries based on a tag
	 *
	 * @param   string  $tag  Tag of cache entry
	 */
	public function delete_tag($tag);

	/**
	 * Find cache entries based on a tag
	 *
	 * @param   string  $tag  Tag of cache entry
	 *
	 * @return  array
	 */
	public function find($tag);
}
