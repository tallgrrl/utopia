<?php

/**
 * @file ObjectFactory
 * 
 * Factory class to return objects
 * 
 * static functions designed to return an apropriate objects based on database results
 * 
 * example call : ObjectFactory::makeObject('stuff', $databaseRow);
 *
 */
Class ObjectFactory
{
    /**
     * function makeObject()
     * SUMMARY: Only public method. each object calls this function with aproprite object type required.
     * 
     * @param $type
     * String of object Type required
     * @param $row
     * Record row od data to populate object with
     * 
     * @return object
     * object of programs choice. objects defined in /objects folder
     */

	public static function makeObject($type, $row)
	{
	    // if there is now data, no object can be returned
		if(!$row || count($row) < 1)
			return null;
		
        // determine which object to return. $type is case sensitive.
		switch($type)
		{
			case 'stuff': return self::stuff($row); break;
		}
	}
    
    /**
     * function stuff
     * SUMMARY return new Stuff() object (/object/Stuff.object.php)
     * 
     * @param $row
     * Array of file entry data
     * 
     * @return Stuff object
     */
	private static function stuff($row)
	{
		$o = new Stuff();

		$o->id                      = $row['stuffid'];
		$o->mongo_id                = $row['mongo_id'];
		$o->user_id                 = $row['user_id'];
		$o->body                    = $row['body'];
		$o->public                  = $row['public'];
		
		return $o;
	}
   
}

?>
