<?
/**
 * @file
 * standardized API database connection
 * 
 * Makes sure all API's can connect to database of choice,
 * switching between mysql, postgres and sqlite is now seamless.
 * 
 * @author: Tracy Lauren for Heimdall Networks Oct, 2012
 * 
 */
interface Database
{
   public function __construct();
    
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
    public function printQuery($name, $query);
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
    public function setDb($db);
    
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
     * NOTE: Not sure if any query is currently using this function.
     */

    public function setVar($name, $val);
    
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
    public function handleError($functionName, $query);
    
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
     
    public function getResult($query);
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
    public function getUnique($query);
    
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
    public function insert($query);
    
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
    public function update($query);

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
    public function delete($query);
    
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
    public function databasePing();

    /**
     * function lastInsertID()
     * 
     * Summary: Get the last insert ID from the insert that was last perfomed on this connection
     * 
     * @return Integer
     * primary auto-increment integer from last insert
     * 
     */
    public function lastInsertID();
  
  /**
   * function convertRecordtoArray()
   * 
   * Summary: converts a valid mysql recordset into a list of associative arrays.
   * The Key's of this array are defined in the query executing
   * 
   * @param $res
   * The valid result object returned from a successful mysql_query() function.
   */
    public function convertRecordtoArray($res);
    
    /**
     * Destructor()
     * 
     * Depricated .... 
     * 
     */
    public function __destruct();
}


?>