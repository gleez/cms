<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * An adaptation of handle path aliasing.
 *
 * @package	Gleez
 * @category	Path
 * @author	Sandeep Sangamreddi - Gleez
 * @copyright	(c) 2012 Gleez Technologies
 * @license	http://gleezcms.org/license
 */
class Gleez_Path {

        /**
	 * Get a path via its alias.
	 *
	 * @param   string   $alias  'about'
	 * @return  mixed    array/false
	 */
	public static function lookup( $alias )
        {
		$regex 	= "#(/p(?P<page>\d+))+$#uD"; 	//preg_match()
		$reg_ex = "#(/p\d+)+$#uD";		//preg_replace()
		$page 	= NULL; 			//default pager id is null
	
		// Save this value for pagination
		// @todo use preg_replace_callback to handle both set and replace
		if( @preg_match($regex, $alias, $matches) )
		{
			if( isset($matches['page']) ) $page = $matches['page'];
			//Message::debug( Debug::vars($matches) );
			unset($matches);
		}
	
		//Remove pagination ex /p1 /p2 etc
		$alias = @preg_replace($reg_ex, '', rtrim($alias, '/'));

		//check if it's a front page request and set <front> tag
		if( empty($alias) AND $alias == NULL ) $alias = '<front>';
	
                $result = Path::load( array('alias' => $alias) );
		if( !$result ) return false;
	
		//reset the <front> tag to '' orelse request fails
		if( $alias === '<front>' AND $result ) $result['alias'] = '';
	
		//$uri = $result['alias'];
		//if( !is_null($page)) $uri = $result['alias'] .'/p'. $page;
	
		return array(
				'directory'  => $result['route_directory'],
				'controller' => $result['route_controller'],
				'action'     => $result['route_action'],
				'id' 	     => $result['route_id'],
				'uri'	     => $result['alias'],
				'page'	     => $page
		);
	}
	
        /**
	 * Creates path alias.
	 *
	 * @param   array   $values  array('source' => 'page/1', 'alias' => 'about')
	 * @return  string
	 */
	public static function save( array $values)
	{
                try
		{
			if( isset($values['id']) AND is_numeric($values['id']) )
			{
				$path = ORM::factory('path', $values['id'] )->values($values)->save();
			}
			else
			{
				$path = ORM::factory('path')->values($values)->save();
			}
		}
		catch( Exception $e)
		{
			//log error and return, to avoid breaking process
			Kohana::$log->add(LOG::ERROR, 'Error: :error creating path alias!', array(':error' => $e->getMessage()) );
			return false;
		}
                
                return $path;
	}

        /**
	 * Deletes path alias.
	 *
	 * @param   $criteria  A number representing the pid or an array of criteria.
	 * @return  boolen     TRUE/FASLE
	 */
	public static function delete( $criteria )
	{
                try
		{
			$query = DB::delete('paths');
			
			if ( !is_array($criteria) )
			{
				$criteria = array('id' => $criteria);
			}
		
			foreach ($criteria as $field => $value)
			{
				$query->where($field, '=', $value);
			}
		
			$query->execute();
		}
		catch( Exception $e)
		{
			Kohana::$log->add(LOG::ERROR, 'Error: :error deleting path alias!', array(':error' => $e->getMessage()) );
			return false;
		}
                
                return TRUE;
	}

	/**
	 * Fetch a specific URL alias from the database.
	 *
	 * @param   mixed    A string representing the source, a number representing the id, or an array of query conditions.
	 * @return  mixed    FALSE if no alias was found or mysql result set
	 */
	public static function load( $conditions )
        {
                try
		{
			$path = DB::select()->from('paths');
		
			if( is_numeric($conditions))
			{
				$path->where('id', '=', $conditions);
			}
			elseif (is_string($conditions))
			{
				$path->where('source', '=', $conditions);
			}
			elseif( is_array($conditions))
			{
				foreach($conditions as $field => $value)
				{
					$path->where($field, '=', $value);
				}
			}
			else
			{
				return false;
			}
		
			$path = $path->execute()->current();
		}
		catch( Exception $e)
		{
			Kohana::$log->add(LOG::ERROR, 'Error: :error lookup path alias!', array(':error' => $e->getMessage()) );
			return false;
		}
                
                return $path;
	}

	/**
	 * Fetch a specific URL alias from the database.
	 *
	 * @param   string    A string representing the source.
	 * @return  string    if alias exists alias or source
	 */
	public static function alias( $source )
        {
                try
		{
			return DB::select('alias')->from('paths')
					->where('source', '=', $source)
					->limit(1)
					->execute()->get('alias', $source);
		}
		catch(Exception $e)
		{
			Kohana::$log->add(LOG::ERROR, 'Error: :error getting alias!', array(':error' => $e->getMessage()) );
			return $source;
		}
	}
	
	/**
	 * Clean up a string segment to be used in an URL alias.
	 *
	 * Performs the following possible alterations:
	 * - Remove all HTML tags.
	 * - Replace or remove punctuation with the separator character.
	 * - Remove back-slashes.
	 * - Replace non-ascii and non-numeric characters with the separator.
	 * - Remove common words.
	 * - Replace whitespace with the separator character.
	 * - Trim duplicate, leading, and trailing separators.
	 * - Convert to lower-case.
	 * - Shorten to a desired length and logical position based on word boundaries.
	 *
	 * @param    string	A string to clean
	 * @return   string	The cleaned string
	 */
	public static function clean($string)
	{
		$separator = '-';
	
		// Empty strings do not need any proccessing.
		if ($string === '' || $string === NULL)
		{
			return '';
		}
	
		// Remove all HTML tags from the string.
		$string = strip_tags($string);
	
		//Replace all characters that are not the separator, letters, numbers, or whitespace
		$string = preg_replace('![^'.preg_quote($separator).'\pL\pN\/\s]+!u', $separator, $string);
	
		// Replace all separator characters and whitespace by a single separator
		$string = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $string);
	
		//convert to lower case.
		$string = UTF8::strtolower($string);
	
		// Trim separators from the beginning and end
		return trim($string, $separator);
	}

	/**
	 * Check if a path matches any pattern in a set of patterns.
	 *
	 * @param $path
	 *   The path to match.
	 * @param $patterns
	 *   String containing a set of patterns separated by \n, \r or \r\n.
	 *
	 * @return
	 *   Boolean value: TRUE if the path matches a pattern, FALSE otherwise.
	*/
	public static function match_path($path, $patterns)
	{
			// Convert path settings to a regular expression.
			// Therefore replace newlines with a logical or, /* with asterisks and the <front> with the frontpage.
			$to_replace = array(
				'/(\r\n?|\n)/', // newlines
				'/\\\\\*/',     // asterisks
				'/(^|\|)\\\\<front\\\\>($|\|)/' // <front>
			);
			$replacements = array(
				'|',
				'.*',
				'\1' . preg_quote(URL::base(), '/') . '\2'
			);
			$patterns_quoted = preg_quote($patterns, '/');
			$regexps[$patterns] = '/^(' . preg_replace($to_replace, $replacements, $patterns_quoted) . ')$/';

		return (bool)preg_match($regexps[$patterns], $path);
	}
	
}