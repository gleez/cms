<?php
/**
 * A HTTP Response specific interface that adds the methods required
 * by HTTP responses
 *
 * Over and above [HTTP_Interaction], this interface provides status.
 *
 * @package    Gleez\HTTP
 * @author     Kohana Team
 * @author     Gleez Team
 * @copyright  (c) 2011-2013 Gleez Technologies
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    http://kohanaframework.org/license
 * @license    http://gleezcms.org/license  Gleez CMS License
 */
interface HTTP_Response extends HTTP_Message {

	/**
	 * Sets or gets the HTTP status from this response
	 *
	 * Example:
	 * ~~~
	 * // Set the HTTP status to 404 Not Found
	 * $response = Response::factory()
	 *                       ->status(404);
	 *
	 * // Get the current status
	 * $status = $response->status();
	 * ~~~
	 *
	 * @param   integer  $code  Status to set to this response [Optional]
	 *
	 * @return  mixed
	 */
	public function status($code = NULL);
}