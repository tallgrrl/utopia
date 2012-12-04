<?php
/**
 * @file
 * core class that all API's extend.
 * 
 * All functions that are common throughout the api's filter up to this class.
 * 
 * 
 * @author: Tracy Lauren for Heimdall Networks April, 2012
 */
Class API
{
    // boolean $debugFlag
    var $debugFlag = false;
    
    /**
     * Constructor
     * 
     * Summary: Initialize class
     * @param $debug
     * boolean defaulted to false;
     * 
     * @return void
     * this function returns nothing
     * 
     * The debug flag will turn debugging lines on or off allowing the 
     * developer to see the state of the code at any given point, and then 
     * turn debugging off without having to add multiple comments;
     * 
     */
     
	public function __construct($debug = false)
	{
        $this->debugFlag = $debug;
	}
	/**
     * function fatalError()
     * 
     * Summary: add reason for fatal Error
     * @param $msg
     * String to be added to error log
     * 
     * @return void
     * this function returns nothing
     * 
     * The string added to the fatalError() function will apear as a E_USER_ERROR
     * level error in the error_log specified in the php.ini file.  This error will kill
     * the current operation.  Since this specific error will never be caused by syntax,
     * this can be used for logic errors in the code base where is is dangerous for the 
     * program to continue.
     * 
     * It is important to note, that if debug flag is not set to true, calling this function 
     * will print nothing, however, it will still kill the process.
     * 
     */
	public function fatalError($msg)
	{
	    if($this->debugFlag)
		  trigger_error("ERROR!!!!!! ---->>>>" .$msg, E_USER_ERROR);
        
        die($msg);
	}
    
    /**
     * function error()
     * 
     * Summary: add reason for error
     * @param $msg
     * String to be added to error log
     * 
     * @return void
     * this function returns nothing
     * 
     * The string added to the error() function will apear as a E_USER_WARNING
     * level error in the error_log specified in the php.ini file.  This error will not
     * stop the program from continuing, so should be used rarely.  
     * 
     * It is important to note, that if debug flag is not set to true, calling this function 
     * will print nothing.
     * 
     */
	public function error($msg)
	{
	    if($this->debugFlag)
		  trigger_error("WARNING ---->>>>" .$msg, E_USER_WARNING);
	}
	
    /**
     * function logMessage()
     * 
     * Summary: add trace line to see state of code
     * @param $msg
     * String to be added to error log
     * 
     * @return void
     * this function returns nothing
     * 
     * The string added to the logMessage() function will apear as a E_USER_NOTICE
     * level error in the error_log specified in the php.ini file.  This error will not
     * stop the program from continuing, but is widely used to see the values of specific 
     * variables at run time.  
     * 
     * It is important to note, that if debug flag is not set to true, calling this function 
     * will print nothing.
     * 
     */
	public function logMessage($msg)
	{
		if($this->debugFlag)
          trigger_error("TRACE LINE: ".$msg, E_USER_NOTICE);
	}
	
    /**
     * function replace()
     * 
     * Summary: Internal replace function that allows bypassing the $ symbol for variables
     * @param $pattern
     * String to search for
     * @param $replacement
     * String to replace match with
     * @param $subject
     * String to be searched
     * 
     * @return String
     * This is the new and improved fully replaced string.
     * 
     * NOTE: This function will replace all instances of $pattern with $replacement.  There is no
     * built in ability to only replace the first, or last, etc.
     * 
     * NOTE: This function is WIDELY used throughout the system to replace tags with variables within 
     * the SQL.  !!CHANGE WITH CAUTION!!
     * 
     * @todo: update function to allow more option for preg_replace.
     * 
     */
	public function replace($pattern, $replacement, $subject)
	{
	    // escape the $
		$repl = str_replace('$',"\\$",$replacement);
		
        // return replaced string
		return preg_replace($pattern, addslashes($repl), $subject);
	}
	
    /**
     * 
     * function jsonify()
     * 
     * Summary: Take any array or object and return a json string of its contents.
     * 
     * @param $arr
     * Array OR Object (php does not require specific casting)
     * 
     * @return String
     * The resulting string is fully readable json and is interperatable accross multiple languages
     * 
     * NOTE: This function is WIDELY used throughout the system.  !!CHANGE WITH CAUTION!!
     * 
     */
	function jsonify($arr)
	{
	    // check if array exists
		if(isset($arr))
		{
		    /**
             * check if it is indeed an array. objects work here too, as well as basic variable types like String
             * 
             * @todo: Not sure what integer type will do. should be tested.
             */
		     
			if(is_array($arr))
            {
                // the easiest scenario
				return json_encode($arr);
            }
			else
			{
			    // shove whatever it is into an array
				$tmp = array();
				array_push($tmp, $arr);
				return json_encode($tmp);
			}
		}
		else
		{
		    // return empty json string
			return json_encode(array());
		}
	}
    
    /**
     * function createSitevars()
     * 
     * Summary: Not all classes require the Sitevars directly.
     * file is located at /ini/sitevars.ini
     * 
     * @return void
     * Nothing is returned from this function. However $this->sitevars is now available.
     * 
     * When they need the sitevars loaded into memory, this function is called
     */
	function createSitevars()
	{
	    /**
         * Create new core class
         * Core Class is located at 
         * 
         * @link: /classes/Core.class.php
         * 
         */
		$core = new Core();
        /**
         * Just creating this class will create a sitevars object within Core.
         * copy this object locally
         */
		$this->sitevars = $core->sitevars;
	}
    
    /**
     * function setjson()
     * 
     * Summary: sets a flag json to be set to true or false
     * 
     * @return void
     * nothing is returned from thhis function
     * 
     * @todo: I am not sure this is being used anymore.  grep the coed to see if/where.
     * 
     */
     
	public function setjson($bool)
	{
		$this->json = $bool;
	}
    
    /**
     * function doReturn()
     * Summary: this function allows the programmer to force the return of the object in whatever format is needed.
     * 
     * @param $arr
     * The raw return array
     * @param $boolean
     * The Switch to determine how to return
     * 
     * By setting (or unsetting) $this->json, this function allows the developer to return the required array in 
     * a specified format.
     * 
     */
     
	function doReturn($arr, $forceArray=false)
	{
	    // user wants to force the array.
		if($forceArray){ return $arr; }
        
        // user has previously decided they want the object returned as json
		if($this->json){ return $this->jsonify($arr); }
        
        // not sure what to do, return raw value;
		else{  return $arr; }
	}
    
    /**
     * function strongClean()
     * 
     * Summary: stand alone function designed to customize our cleaning strategy for user inputs.
     * Since the changing of database types has been so common, it was necessary to remove the 
     * mysql_escape_string() and its other counterparts, and try to manually clean the inputs in a way
     * that works accross all dbs
     * 
     * @param $str
     * The string to be cleaned.
     * 
     * @todo: update the clean
     * 
     */
	function strongClean($str)
 	{
		$str = preg_replace("/[\\s]+/", " ", $str);
    
		return addslashes($str);
	}
}
?>
