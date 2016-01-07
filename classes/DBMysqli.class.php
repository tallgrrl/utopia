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
class DBMysqli extends API implements Database
{
    /**
* The host URL where the mysql server resides.
* can be 'localhost'
*
* This information is found at
* @link /ini/sitevars.inc under the apropriate domain (MYSQL_HOST)
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
* Used to spit out the queries at run time when desired.
*
* This information is found at
* @link /ini/sitevars.inc under the apropriate domain (MYSQL_DEBUG)
*/
     
        private $debug = "";

    /**
* STATIC $dbh. This array will ensure connections will not step on each other
* if more than one API is being called within a request cycle.
*
*/
        protected $dbh = array();
    
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
        public $sitevars;

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
                $tmp = parse_ini_file(RequestHandler::GetBaseDirectory() . "/ini/sitevars.ini", true);
        //print_r($tmp);
        // if the request is coming through the browser, use the variables found under the specific domain name
                if(isset($_SERVER['HTTP_HOST']))
                {
                    //print $_SERVER['HTTP_HOST'];
                        $this->sitevars = $tmp[$_SERVER['HTTP_HOST']];
                }
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
                        $this->dbh[$this->dbh_id] = mysqli_connect($this->host, $this->user, $this->pass, $this->db);
                
        // set utf-8
                $this->dbh[$this->dbh_id]->query("SET character_set_results=utf8");
                $this->dbh[$this->dbh_id]->query("SET character_set_client=utf8");
                $this->dbh[$this->dbh_id]->query("SET character_set_connection=utf8");
                
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
                
                return $this->dbh[$this->dbh_id]->select_db($db);
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
            API::log_error($functionName, $query, $this->debug);
            
            // print the query
            $this->printQuery($functionName, $query);
            
            
     }
     
    function prepareQuery($sql, $typeDef = false, $params = false)
    {
        $multiQuery = false;
        if($stmt = mysqli_prepare($this->dbh[$this->dbh_id],$sql))
        {
           

            if(count($params) == count($params,1))
            {
                $params = array($params);
                $multiQuery = false;
            } 
            else 
            {
                $multiQuery = true;
            } 
$bindParams = array();
            if($typeDef)
            {
                   
                $bindParamsReferences = array();
                $bindParams = array_pad($bindParams,(count($params,1)-count($params))/count($params),"");        
                foreach($bindParams as $key => $value)
                {
                    $bindParamsReferences[$key] = &$bindParams[$key]; 
                }
                array_unshift($bindParamsReferences,$typeDef);
                $bindParamsMethod = new ReflectionMethod('mysqli_stmt', 'bind_param');
                @$bindParamsMethod->invokeArgs($stmt,$bindParamsReferences);
            }

            $result = array();
            foreach($params as $queryKey => $query)
            {
                foreach($bindParams as $paramKey => $value)
                {
                    $bindParams[$paramKey] = $query[$paramKey];
                }
                $queryResult = array();
                try
                {
                    if(mysqli_stmt_execute($stmt))
                    {
                        $resultMetaData = mysqli_stmt_result_metadata($stmt);
                        if($resultMetaData)
                        {                                                                              
                            $stmtRow = array();  
                            $rowReferences = array();
                            while ($field = mysqli_fetch_field($resultMetaData)) 
                            {
                                $rowReferences[] = &$stmtRow[$field->name];
                            }                               
                            mysqli_free_result($resultMetaData);
                            $bindResultMethod = new ReflectionMethod('mysqli_stmt', 'bind_result');
                            $bindResultMethod->invokeArgs($stmt, $rowReferences);
                            while(mysqli_stmt_fetch($stmt))
                            {
                                $row = array();
                                foreach($stmtRow as $key => $value)
                                {
                                    $row[$key] = $value;          
                                }
                                $queryResult[] = $row;
                            }
                            mysqli_stmt_free_result($stmt);
                        } 
                        else 
                        {
                            $queryResult[] = mysqli_stmt_affected_rows($stmt);
                        }
                    } 
                    else 
                    {
                        $queryResult[] = FALSE;
                    }
                    $result[$queryKey] = $queryResult;
                }
                catch(Exception $e)
                {
                    Throw new Exception("Error with Query");
                }
            }
            mysqli_stmt_close($stmt);  
        } 
        else 
        {
            $result = FALSE;
        }

        if($multiQuery){
            return $result;
        } 
        else 
        {
            return $result[0];
        }
    } 

    /*
    function refValues($arr){
        if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
        {
            $refs = array();
            foreach($arr as $key => $value)
                $refs[$key] = &$arr[$key];
            return $refs;
        }
        return $arr;
    }
    function prepareQuery($sql, $typeDef = false, $params = array())
    {
        $multiQuery = false;
        try {
            if($stmt = mysqli_prepare($this->dbh[$this->dbh_id],$sql))
            {
                $res = call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));
                trigger_error(print_r($res, true));
            }
            else
                Throw new Exception("Error with Query");
        }
        catch(Exception $e)
        {
            Throw new Exception("Error with Query");
        }

    mysqli_stmt_close($stmt);  
    return $result;
}
*/
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
        public function getResult($query, $typeDef='', $params = array())
        {
         // make sure connection is sound
                $this->databasePing();

            $res = $this->prepareQuery($query, $typeDef, $params);
            
            if($res)
            {
                 if(count($res) >= 1)
                {
                    return $res;
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
        public function getUnique($query, $typeDef='', $params = array())
        {

            $this->databasePing();

            $res = $this->prepareQuery($query, $typeDef, $params);
            
            if($res)
            {
                 if(count($res) >= 1)
                {
                    // return first element in the array regardless of how many there are
                    return $res[0];
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
    

    private function makeValuesReferenced($arr){
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;

    }

    private function statementExecute($query, $typeDef, $params)
    {
//trigger_error($query);
        $stmt = $this->dbh[$this->dbh_id]->prepare($query);

//        $args=Array($typedef,&$params);
        array_unshift($params, $typeDef);
//trigger_error(print_r($params, true));


        @call_user_func_array(array($stmt, 'bind_param'),  self::makeValuesReferenced($params));

        @$stmt->execute();

        /* close statement */
        $stmt->close();
        return $stmt;
    }
    /**
* function insert()
*
* Summary: executes an insert query. This query is not expecting a result set.
* @param $query
* String fully qualified query string
*
* @return integer
* The last inserted ID for the newly inserrted record.
*
*
*
*/
        public function insert($query, $typeDef='', $params = array())
        {

            // make sure connection is sound
            $this->databasePing();
        
            $statement = $this->statementExecute($query, $typeDef, $params);

            return $this->lastInsertID();
        }
    
    /**
* function update()
*
* Summary: executes an update query. This query is not expecting a result set.
* @param $query
* String fully qualified query string
*
* @return boolean
* true if the update happened
*
* @todo: should return the number of effected rows.
*
*/
        public function update($query, $typeDef='', $params = array())
        {
                // make sure connection is sound
            $this->databasePing();
        
            $statement = $this->statementExecute($query, $typeDef, $params);

            return $statement;
        }

    /**
* function delete()
*
* Summary: executes a delete query. This query is not expecting a result set.
* @param $query
* String fully qualified query string
*
* @return boolean
* true if the delete happened
*
* @todo: should return the number of effected rows.
*
*/
        public function delete($query, $typeDef='', $params = array())
        {
                // make sure connection is sound
            $this->databasePing();
        
            $statement = $this->statementExecute($query, $typeDef, $params);

            return $statement;
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

            if ($this->dbh[$this->dbh_id]->ping()) {
                // reset DB
                $this->setDb($this->db);

            } else {
                 // if the ping returns false, the connection is closed. need to reopen
                $this->dbh[$this->dbh_id] = new mysqli($this->host, $this->user, $this->pass, $this->db);
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
                return mysqli_insert_id($this->dbh[$this->dbh_id]);
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
// THIS SHOULDN'T BE DONE HERE. PHP WILL CLOSE IT AUTOMATICALLY AT END OF REQUEST UNLESS WE USE mysql_pconnect (persistant-connect)
if($this->dbh[$this->dbh_id])
@mysql_close($this->dbh[$this->dbh_id]);
*/
    }
}

?>