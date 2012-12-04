<?php
/**
 * @file
 * Postgres DB connector
 * 
 * All API's extend this class if they need to connect to a Postgres database
 * 
 * Extension:
 * @link: /classes/Api
 * 
 * @author: Tracy Lauren for Heimdall Networks July, 2012
 */
 
class DBPostgres extends API implements Database
{
    /**
     * The Username needed to connect to postgres with permissions to read.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (POSTGRES_USER)
     */
    private $user = "";
    /**
     * The password needed to connect to postgres with permissions to read.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (POSTGRES_PASS)
     */
    private $pass = "";
    /**
     * The host URL where the postgres server resides.
     * can be 'localhost'
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain  (POSTGRES_HOST)
     */
    private $host = "";
     /**
     * The Our postgres server is sitting on a non standard port. This is configured here
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain  (POSTGRES_PORT)
     */
    private $port = "";
    
    /**
     * The Default Database to connect to on the mysql server. Username/Password must 
     * have at least read permissions to connect to this database.
     * 
     * This information is found at
     * @link /ini/sitevars.inc under the apropriate domain (POSTGRES_REPORT_DBNAME)
     */
    private $db = "";
    
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
     * The name of the sequence table, alters with each query
     */
    private $tablename;
 /**
     * Constructor
     * 
     * Summary: just creating this class will establish a connection that can be used to connect to a postgres 
     * server.
     * 
     * @return void
     * does not return anything
     * 
     */
     
    public function __construct()
    {
         // get all values from sitevars file
        $tmp = parse_ini_file(RequestHandler::GetBaseDirectory() . "/ini/sitevars.ini", true);
        
        // if the request is coming through the browser, use the variables found under the specific domain name
        if(isset($_SERVER['HTTP_HOST']))
            $this->sitevars = $tmp[$_SERVER['HTTP_HOST']];
        else
        {
            // if this variable does not exists, it is because the connection is being used in a cron job.
          $this->sitevars = $tmp['cmdline'];
        }
        
        // explicitly set the values
        $this->user = $this->sitevars['POSTGRES_USER'];
        $this->host = $this->sitevars['POSTGRES_HOST'];
        $this->pass = $this->sitevars['POSTGRES_PASS'];
        $this->port = $this->sitevars['POSTGRES_PORT'];
        $this->db = $this->sitevars['POSTGRES_DBNAME'];
        $this->debug = ($this->sitevars['MYSQL_DEBUG'] == '1' ? true : false);

        // NOTE: THIS ENSURES THAT WE ONLY CONNECT TO THE SAME DATABASE ONCE PER CONNECTION
        // NOTE: WITHOUT THIS THERE WOULD BE MULTIPLE OPEN HANDLES/CONNECTIONS TO THE SAME DB FOR NO REASON
        // NOTE: THIS WOULD DEFINITELY NOT SCALE WELL AND CAUSE MUCH PAIN ON DB SERVER IF BUSY
        //print "connect to db";
        $this->setDb($this->db);
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
    function toggleDebug()
    {
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
        // create connection string
        $connectString = "host=".$this->host." port=".$this->port." dbname=".$str." user=".$this->user." password=".$this->pass;
        
        //print $connectString;
        // establish unique id
        $this->dbh_id = md5($connectString );

        // check to see if this connection exists
        if (!isset(self::$dbh[$this->dbh_id]))
        {
            // if it does not exist, create new
            self::$dbh[$this->dbh_id] = pg_connect($connectString);
        }
        
        if (!isset(self::$dbh[$this->dbh_id]))
        {
            // if it still does not exist throw error
           API::fatalError('Could not connect to Database at '.$this->host);
        }
        else
        {
           // if program is here connection has been made. congrats
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
        sqlite_query(self::$dbh[$this->dbh_id], "set $name = $val");
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
            API::error('Invalid query: ' . pg_last_error($dbh[$this->dbh_id]));
            
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
         // run the query against current connection
        if(!pg_send_query(self::$dbh[$this->dbh_id], $query))
        {
            // if query did not reun, throw error
            $this->handleError('getResult()', $query);
        }
        // get result
        $res = pg_get_result(self::$dbh[$this->dbh_id]);
        
        // check result
        if($res)
        {
            // if result is good, convert to array and return
            return $this->convertRecordtoArray($res);
        }
        else
        {
            // else result is not good, throw error
            $this->handleError('getResult()', $query);
        }
    }

    public function getUnique($query)
    {
        // run the query against current connection
        if(!pg_send_query(self::$dbh[$this->dbh_id], $query))
        {
            // if query did not reun, throw error
            $this->handleError('getUnique()', $query);
        }
        // get result
        $res = pg_get_result(self::$dbh[$this->dbh_id]);
        //$res = sqlite_query($this->dbh[$this->dbh_id], $query);
        $arr = array();
        
        // check result
        if($res)
        {
            // if result is good, convert to array
            $arr = $this->convertRecordtoArray($res);

            if(count($arr) >= 1)
            {
                // if record count is 1 or more, return first value as assoc array
                return $arr[0];   
            }
            else
            {
                // if no results, return null
                return null;
            }
        }
        else
        {
            //if invalid query, throw error
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
   
        // split query into individual words
        $tmp = preg_split("/[ ]/", $query);
        
        $this->tablename = "";
        // look for tablename. it will be the word following the 'into' as in 'insert into reportdata ...'
        foreach($tmp as $index => $str)
        {
            if(strtolower($str) == 'into')
			{
				$this->tablename = $tmp[$index+1]."_id";
        	
			}
               
		}
        // execute query against current connection
        
        $res = pg_query(self::$dbh[$this->dbh_id], $query); 
		
		
        if(!$res)
        {
           
            //if invalid query, throw error
            $this->handleError('insert()', $query);
        }  
        else
        {
        	//pg_query(self::$dbh[$this->dbh_id], "commit"); 
        	
            // else return last sequence
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
        // execute query against current connecction
        $res = pg_query(self::$dbh[$this->dbh_id], $query); 


        if(!$res)
        {
            //if invalid query, throw error
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
        // execute query against current connection
        $res = pg_query(self::$dbh[$this->dbh_id], $query); 
        
        if(!$res)
        {
             //if invalid query, throw error
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
     * NOTE: This functionality does not exists for postgres.  Empty function is here to satisfy interface.
     */
    public function databasePing()
    {
        return true;
    }
    /**
     * function lastInsertID()
     * 
     * Summary: Get the last insert ID from the insert that was last perfomed on this connection
     * 
     * @return Integer
     * returns last_value of sequence for this table
     * 
     * NOTE: all tables require a 'tablename'+'_seq' named sequece for this to work properly.
     * 
     * @TODO: This function should fail severely if the sequence is misnamed, or non existant
     */
    public function lastInsertID()
    {
        // set proper query with apropriatly named sequence
        $query = "select last_value  as num from  ".$this->tablename."_seq";
        
        // get unique value
        //print ">>>>>".$query."<<<<<<<";
        $res = $this->getUnique($query);
        
        // return apropriate value
        return $res['num'];
        
    }
  
  /**
   * function convertRecordtoArray()
   * 
   * Summary: converts a valid mysql recordset into a list of associative arrays.
   * The Key's of this array are defined in the query executing
   * 
   * @param $res
   * The valid result object returned from a successful mysql_query() function.
   * 
   * @TODO: free result
   */
    public function convertRecordtoArray($res)
    {
        // set up initial arrays
        $arr = array();
        $fin = array();
        
        // loop through result set

        while($arr = pg_fetch_assoc($res))
        {
            // push into array

            array_push($fin, $arr);

        }
        // return final array
        return $fin;
    }
    public function addPaging($query, $page, $num)
	{
		return $query . " OFFSET " . ($page-1) * $num . " LIMIT $num";
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