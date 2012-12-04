<?php
/**
 * @file
 * SQLite DB connector
 * 
 * All API's extend this class if they need to connect to a SQLite database
 * 
 * Extension:
 * @link: /classes/Api
 * 
 * @author: Tracy Lauren for Heimdall Networks June, 2012
 */
 
class DBLite extends API implements Database
{
    /**
     * The fully qualifies path to the sqlite file.
     * can be relative, but it is not safe
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain  (SQLITE_FILEPATH + SQLITE_FILENAME)
     */
	private $sqlFile = "";
    
     /**
     * The Username needed to connect to sqlite with permissions to read.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (MYSQL_USER)
     */
     
	private $user = "";
    
     /**
     * The password needed to connect to sqlite with permissions to read.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (MYSQL_PASS)
     */
	private $pass = "";
    
    /**
     * Debug Flag. 
     *  Used to spit out the queries at run time when desired.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (MYSQL_DEBUG)
     */
	private $debug = false;

    /**
     * STATIC $dbh.  This array will ensure connections will not step on each other 
     * if more than one API is being called within a request cycle.
     * 
     */
	static $dbh = array();

    /**
     * The actual if for the connection
     */
    protected $dbh_id;
    
    /**
     * The array for all of the connection information found in 
     * @link /ini/sitevars.inc
     * 
     * 
     */
    protected $sitevars;

    /**
     * Constructor
     * 
     * Summary: just creating this class will establish a connection that can be used to connect to sqlite file
     * 
     * @return void
     * does not return anything
     * 
     */

	public function __construct()
	{
	    // get all values from sitevars file
		$tmp = parse_ini_file(RequestHandler::GetBaseDirectory() . "/ini/sitevars.inc", true);
        
        
		 // if the request is coming through the browser, use the variables found under the specific domain name
        if(isset($_SERVER['HTTP_HOST']))
			$this->sitevars = $tmp[$_SERVER['HTTP_HOST']];
		else
        {
            // if this variable does not exists, it is because the connection is being used in a cron job.
          $this->sitevars = $tmp['cmdline'];
        }
        
        //get file name
		$this->sqlFile = $this->sitevars['SQLITE_FILEPATH'] . $this->sitevars['SQLITE_FILENAME'];
		$this->user = $this->sitevars['MYSQL_USER'];
		$this->pass = $this->sitevars['MYSQL_PASS'];
		
        // set debug
		$this->debug = ($this->sitevars['MYSQL_DEBUG'] == '1' ? true : false);

		// NOTE: THIS ENSURES THAT WE ONLY CONNECT TO THE SAME DATABASE ONCE PER CONNECTION
		// NOTE: WITHOUT THIS THERE WOULD BE MULTIPLE OPEN HANDLES/CONNECTIONS TO THE SAME DB FOR NO REASON
		// NOTE: THIS WOULD DEFINITELY NOT SCALE WELL AND CAUSE MUCH PAIN ON DB SERVER IF BUSY
		//print "connect to db";
		$this->setDb($this->sitevars['SQLITE_FILENAME']);
        //trigger_error("constructor Complete");
	}
    /**
     * function toggleDebug()
     * 
     * Summary: Toggle the debug function on or off
     * 
     * @return void
     * This function does not return anything
     */
	public function toggleDebug()
	{
	    // if debug is set, turn it off.
		if($this->debug)
			$this->debug=false;
		else
			$this->debug=true;
	}
    
    /**
     * function setDb()
     * 
     * Summary: Change the current set Database
     * 
     * @param: $db
     * String of name of new database.
     * 
     * This function will change the current connected database.
     * 
     */
	public function setDb($str)
	{
	    // get new file path
		$str = $this->sitevars['SQLITE_FILEPATH'] . $str;
        
        //create new dbh_id
		$this->dbh_id = md5($str . $this->user .$this->pass );
		

		if (!isset(DBLite::$dbh[$this->dbh_id]))
		{
		    /**
             * if the program is here, the connection does not exist
             */
		      
			DBLite::$dbh[$this->dbh_id] = new SQLite3($str);

		}
		else
		{
		     /**
             * if the program is here, the connection exists, and I am trying to reconnect to same db
             */
              
		    API::error("HANDLE to " . $str . " ALREADY EXISTS.");
			//API::fatalError("SQLITE ERROR: " . $sqliteerror);
			//die("Could not connect to ".$sqliteerror);
		}

	}
    
    /**
     * function printQuery()
     * 
     * Summary: Spit out query into error_log in a format that can be found even in a busy log file.
     * 
     * @param $name
     * Name of query found in inc file.
     * @param $query
     * The query itself.
     * 
     * location of error_log is defined in php.ini file
     * 
     * @todo: format the query string so it does not comeout as single line, and can be read easier.
     * 
     */
	public function printQuery($name, $query)
	{
		if ($this->debug)
		{
			trigger_error("-----------------------------------------------------", E_USER_NOTICE);
			trigger_error("MYSQL QUERY: >>".$name."<< --".$query, E_USER_NOTICE);
			trigger_error("-----------------------------------------------------", E_USER_NOTICE);
		}
	}

    /**
     * function setVar()
     * 
     * Summary set mysql variables. usually used within procedures
     * 
     * @param $name
     * String name of variable to be set
     * @param $val
     * ... any type variable value
     * 
     * @return void
     * this function does not retun a value
     * 
     * NOTE: Not sure if any query is currently using this function.
     */
	public function setVar($name, $val)
	{
	    
		sqlite_query(DBLite::$dbh[$this->dbh_id], "set $name = $val");
	}

/**
     * 
     * function handleError()
     * 
     * @param $functionName
     * The name of the function calling the query
     * @param $query
     * The actual Query
     * 
     * @return void
     * This function does not return anything
     * 
     * Even an empty result set is a result.
     * If the program is here, there was an issue with the query.
     */
    public function handleError($functionName, $query)
     {
            API::error('Invalid query: ' . sqlite_error_string (sqlite_last_error($dbh[$this->dbh_id])));
            
            // print the query
            $this->printQuery($functionName, $query);
            
            // kill the program
            API::fatalError('Program Ends.');
     }
    /**
     * function getResult()
     * 
     * Summary: returns array recordset for any query requesting more than 1 record from the database.
     * 
     * @param $query
     * Sting fully qualified query to be run against the db
     * 
     * @return Array
     * The record set returned as an array.
     * 
     */
	public function getResult($query)
	{
		$err = "";
		$res = DBLite::$dbh[$this->dbh_id]->query($query); 
	//	$res = sqlite_query(DBLite::$dbh[$this->dbh_id], $query);
//		var_dump($res);

		if($res)
		{
			return $this->convertRecordtoArray($res);
		}
		else
		{
			 // else result is not good, throw error
            $this->handleError('getResult()', $query);
		}
	}
    /**
     * function getUnique()
     * 
     * Summary: returns single row assoc array recordset for any query 
     * if query returns multiple records, getUnique() returns the first one (index[0]).
     * 
     * @param $query
     * Sting fully qualified query to be run against the db
     * 
     * @return Array
     * The record set returned as an assoc array.
     * 
     */
	public function getUnique($query)
	{
		
		$err = "";
		$res = DBLite::$dbh[$this->dbh_id]->query($query); 
		//$res = sqlite_query(DBLite::$dbh[$this->dbh_id], $query);
		$arr = array();
		
		if($res)
		{
			$arr = $this->convertRecordtoArray($res);

			if(count($arr) >= 1)
			{
				return $arr[0];   
			}
			else
			{
				return null;
			}
		}
		else
		{
			 // else result is not good, throw error
            $this->handleError('getUnique()', $query);
		}
	}
    /**
     * function insert()
     * 
     * Summary: executes an insert query.  This query is not expecting a result set.
     * @param $query
     * String fully qualified query string
     * 
     * @return integer
     * The last inserted ID for the newly inserrted record.
     * 
     * 
     * 
     */
	public function insert($query)
	{
		$err = "";
		$res = DBLite::$dbh[$this->dbh_id]->exec($query); 
		//$res = sqlite_exec(DBLite::$dbh[$this->dbh_id], $query);

		if(!$res)
		{
			 // else result is not good, throw error
            $this->handleError('insert()', $query);
		}  
		else
			return $this->lastInsertID();
	}
    /**
     * function update()
     * 
     * Summary: executes an update query.  This query is not expecting a result set.
     * @param $query
     * String fully qualified query string
     * 
     * @return boolean
     * true if the update happened
     * 
     * @todo: should return the number of effected rows.
     * 
     */
	public function update($query)
	{
		$err = "";
		$res = DBLite::$dbh[$this->dbh_id]->exec($query); 
		//$res = sqlite_exec(DBLite::$dbh[$this->dbh_id], $query);

		if(!$res)
		{
			 // else result is not good, throw error
            $this->handleError('update()', $query);
		}  
		else
			return true;
	}

    /**
     * function delete()
     * 
     * Summary: executes a delete query.  This query is not expecting a result set.
     * @param $query
     * String fully qualified query string
     * 
     * @return boolean
     * true if the delete happened
     * 
     * @todo: should return the number of effected rows.
     * 
     */
	public function delete($query)
	{
		$err = "";
		$res = DBLite::$dbh[$this->dbh_id]->exec($query); 
		
		//$res = sqlite_exec(DBLite::$dbh[$this->dbh_id], $query);

		if(!$res)
		{
			 // else result is not good, throw error
            $this->handleError('delete()', $query);
		}  
		else
			return true;
	}
    
    /**
     * function databasePing()
     * 
     * Summary: check to see if the database connection is still fresh
     * 
     * @return void
     * This function does not return anything
     * 
     * If the connection to the database has closed for some reason, this ping will refresh the connection 
     * on its original connection ID.
     */
    public function databasePing()
    {
        // this functionality does not exist for sqlite
        
        return true;
    }

    /**
     * function lastInsertID()
     * 
     * Summary: Get the last insert ID from the insert that was last perfomed on this connection
     * 
     * @return Integer
     * primary auto-increment integer from last insert
     * 
     */
	public function lastInsertID()
	{
		return DBLite::$dbh[$this->dbh_id]->lastInsertRowID();
	//	return sqlite_last_insert_rowid(DBLite::$dbh[$this->dbh_id]);
	}
    
    /**
   * function convertRecordtoArray()
   * 
   * Summary: converts a valid mysql recordset into a list of associative arrays.
   * The Key's of this array are defined in the query executing
   * 
   * @param $res
   * The valid result object returned from a successful mysql_query() function.
   */
	public function convertRecordtoArray($res)
	{
		$arr = array();
		$fin = array();
		//$arr[] = sqlite_fetch_all($res); 
			
		/**
         * @todo free result
         */
		while($arr = $res->fetchArray(SQLITE3_ASSOC))
		{
			array_push($fin, $arr);
		}

		return $fin;
	}
    /**
     * Destructor()
     * 
     * Depricated .... 
     * 
     */
	public function __destruct()
    {

    }
}

?>
