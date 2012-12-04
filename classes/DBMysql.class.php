<?php
/**
 * @file
 * mysql DB connector
 * 
 * All API's extend this class if they need to connect to a mysql database
 * 
 * Extension:
 * @link: /classes/Api
 * 
 * @author: Tracy Lauren for Heimdall Networks April, 2012
 */
class DBMysql extends API implements Database
{
    /**
     * The host URL where the mysql server resides.
     * can be 'localhost'
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain  (MYSQL_HOST)
     */
     
	private $host = "";
    
     /**
     * The Username needed to connect to the mysql server with permissions to read.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (MYSQL_USER)
     */
     
	private $user = "";
    
    /**
     * The Password needed to connect to the mysql server related to username 
     * with permissions to read.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (MYSQL_PASS)
     */
     
	private $pass = "";
    
    /**
     * The Default Database to connect to on the mysql server. Username/Password must 
     * have at least read permissions to connect to this database.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (MYSQL_DB)
     */
     
     
	private $db = "";
    
    /**
     * Debug Flag. 
     *  Used to spit out the queries at run time when desired.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (MYSQL_DEBUG)
     */
     
	private $debug = "";

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
     * Summary: just creating this class will establish a connection that can be used to connect to a mysql 
     * server.
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
        
        // explicitly set the values
		$this->host = $this->sitevars['MYSQL_HOST'];
		$this->user = $this->sitevars['MYSQL_USER'];
		$this->pass = $this->sitevars['MYSQL_PASS'];
		$this->db = $this->sitevars['MYSQL_DB'];
		$this->debug = ($this->sitevars['MYSQL_DEBUG'] == '1' ? true : false);

		// NOTE: THIS ENSURES THAT WE ONLY CONNECT TO THE SAME DATABASE ONCE PER CONNECTION
		// NOTE: WITHOUT THIS THERE WOULD BE MULTIPLE OPEN HANDLES/CONNECTIONS TO THE SAME DB FOR NO REASON
		// NOTE: THIS WOULD DEFINITELY NOT SCALE WELL AND CAUSE MUCH PAIN ON DB SERVER IF BUSY
		$this->dbh_id = md5(implode('|', array($this->host, $this->user, $this->pass, $this->db)));
        
        
        // create the connection if it does not yet exist
		if (!isset($this->dbh[$this->dbh_id]))
			$this->dbh[$this->dbh_id] = mysql_connect($this->host, $this->user, $this->pass);

        // set database if the connection succeeds
		if($this->dbh[$this->dbh_id])
		{
			mysql_select_db($this->db, $this->dbh[$this->dbh_id]);
		}
		else
		{
		    /**
             * if the database connection cannot be made, there is no reason to continue. 
             * More errors will happen
		    */
			API::fatalError("Could not connect to " . $this->host . " database " . $this->db);
		}
        
        // set utf-8 
		mysql_query("SET character_set_results=utf8", $this->dbh[$this->dbh_id]);
		mysql_query("SET character_set_client=utf8", $this->dbh[$this->dbh_id]);
		mysql_query("SET character_set_connection=utf8", $this->dbh[$this->dbh_id]); 

	}
    public function addPaging($query, $page, $num)
	{
		return $query . " LIMIT " . ($page-1 * $num) . ", $num";
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
	public function setDb($db)
	{
		$this->db = $db;
		return mysql_select_db($db, $this->dbh[$this->dbh_id]);
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
		mysql_query("set $name = $val",$this->dbh[$this->dbh_id]);
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
            API::error('Invalid query: ' . mysql_error());
            
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
	    // make sure connection is sound
		$this->databasePing();

        // run the query against current connection
		$res = mysql_query($query, $this->dbh[$this->dbh_id]);
        //var_dump($res);

		if($res)
		{
		    // if there is a result, return the result converted into an array
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
		// make sure connection is sound
        $this->databasePing();
        
		// run the query against current connection
        $res = mysql_query($query, $this->dbh[$this->dbh_id]);
        //var_dump($res);
        
		$arr = array();

		if($res)
		{
		    // if there is a result, return the result converted into an array
			$arr = $this->convertRecordtoArray($res);

			if(count($arr) >= 1)
			{
			    // return first element in the array regardless of how many there are
				return $arr[0];   
			}
			else
			{
			    // no elements to the array, return null
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
		// make sure connection is sound
        $this->databasePing();
        
        // run the query against current connection
        $res = mysql_query($query, $this->dbh[$this->dbh_id]);

		if(!$res)
		{
		    // else result is not good, throw error
            $this->handleError('insert()', $query);
		}  
		else
        {
            // get the last insert ID
			return $this->lastInsertID();
        }
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
		// make sure connection is sound
        $this->databasePing();
        
        // run the query against current connection
        $res = mysql_query($query, $this->dbh[$this->dbh_id]);

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
		// make sure connection is sound
        $this->databasePing();
        
        // run the query against current connection
        $res = mysql_query($query, $this->dbh[$this->dbh_id]);

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
	    // if hte ping returns false, the connection is closed.
		if(!mysql_ping($this->dbh[$this->dbh_id])) 
		{
		    // refresh connection
			$this->dbh[$this->dbh_id] = mysql_connect($this->host, $this->user, $this->pass);
            
            // reset DB
			$this->setDb($this->db);
		}
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
	    // mysql built in function
		return mysql_insert_id($this->dbh[$this->dbh_id]);
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
	    // new fresh array
		$arr = array();
        
        // loop through each result set, and push the record into $arr
		while(($arr[] = mysql_fetch_assoc($res)) || array_pop($arr)); 
       
       // be nice and free up the memory. 
	   mysql_free_result($res);
        // return the new array;
		return $arr;
	}
    
    /**
     * Destructor()
     * 
     * Depricated .... 
     * 
     */
    public function __destruct()
    {
        /*
        // THIS SHOULDN'T BE DONE HERE.  PHP WILL CLOSE IT AUTOMATICALLY AT END OF REQUEST UNLESS WE USE mysql_pconnect (persistant-connect)
        if($this->dbh[$this->dbh_id])
            @mysql_close($this->dbh[$this->dbh_id]);
        */
    }
}

?>
