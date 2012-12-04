<?
/**
 * TestAPI
 *
 * TestAPI is a example test API class to describe how to build the API layer.
 * This API is extending the DBPostgres class.  It could just as easily extend DBLite
 * or DBMysql.
 *
 * 
 */

Class TestApi extends DBPostgres
{
    private $debugQuery;
    private $hashLength = 10;
    function __construct()
    {
        parent::__construct($debugQuery = false);
        $this->debugQuery = $debugQuery;
        $this->queries = parse_ini_file(RequestHandler::GetBaseDirectory() . "/ini/test.ini");
    }

	function getStuff($userId, $page=1, $num=50)
	{
		$query = $this->queries['getSomeThings'];
		$query = parent::addPaging($query, $page, $num);
		
		$query = API::replace("/__ID__/", $userId, $query);
		
		if($this->debugQuery)
			print $query;
		
		$things = parent::getResult($query);

		if($things)
		{
			$stu = array();
			foreach($things as $index => $x)
			{
				$stu[] = ObjectFactory::makeObject('stuff', $x);
			}
			return $stu;
		}	
		else {
			return null;
		}
	}
	
	
	function addStuff($mongo, $userId, $body, $public)
	{
		$query = $this->queries['addStuff'];
		$query = API::replace("/__MONGO__/", $mongo, $query); 
		$query = API::replace("/__ID__/", $userId, $query); 
		$query = API::replace("/__BODY__/", $body, $query); 
		$query = API::replace("/__PUB__/", $public, $query); 
		
		if($this->debugQuery)
			print $query;
		
		$id = parent::insert($query);
		return $this->getStuff($userId);
	}
	
	function editStuff($stuffId, $mongo, $userId, $body, $public)
	{
		$query = $this->queries['editStuff'];
		$query = API::replace("/__MONGO__/", $mongo, $query); 
		$query = API::replace("/__ID__/", $userId, $query); 
		$query = API::replace("/__BODY__/", $body, $query); 
		$query = API::replace("/__PUB__/", $public, $query); 
		$query = API::replace("/__STID__/", $stuffId, $query); 
		
		if($this->debugQuery)
			print $query;
			
		parent::update($query);
		return $this->getStuff($userId);
	}
	
	function deleteStuff($stuffId)
	{
		$query = $this->queries['deleteStuff'];
		$query = API::replace("/__ID__/", $stuffId, $query);
		 
		if($this->debugQuery)
			print $query;
			
		parent::delete($query);
	}
	
	function deleteAllStuff($userId)
	{
		$query = $this->queries['deleteAllStuff'];
		$query = API::replace("/__ID__/", $userId, $query); 
		
		if($this->debugQuery)
			print $query;
			
		parent::delete($query);
	}
}
?>