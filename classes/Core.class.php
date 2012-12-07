<?php
/******
 * core.class.php
 * Written by Tracy Lauren Dec 17, 2010, 11:16 am
 * Additions by Herbert Molenda 2011-04-01 11:52:33
 * 
 ******/ 
 Class Core 
 {
    public $sitevars;  
    public $pageconfig; 
    public function __construct()
    {
        $tmp = parse_ini_file(RequestHandler::GetBaseDirectory() . "/ini/sitevars.ini", true);
			
		$this->sitevars = $tmp[$_SERVER['HTTP_HOST']];
		
		if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
		{
			$this->sitevars['REMOTE_ADDRESS'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$this->sitevars['REMOTE_ADDRESS'] = $_SERVER['REMOTE_ADDR'];
		}
    }

    public function __destruct()
	{
		if(isset($_SESSION) && isset($_SERVER['HTTP_REFERER']))
		{
			$_SESSION['REFERER'] = $_SERVER['HTTP_REFERER'];
		}    
    }

    public function setPageConfig($page)
    {
		if(file_exists("../ini/".$page.".ini"))
		{
			$this->pageconfig = parse_ini_file(RequestHandler::GetBaseDirectory() . "/ini/" . $page . ".ini");
			return true;
		}
		else
		{
			$this->logWarning("Config File for ". $page . " does not exist");
			return false;
		}
    }

    public function logError($msg)
    {
		trigger_error("*********************************************" . $msg, E_USER_ERROR);
		trigger_error("ERROR ----> " . $msg, E_USER_ERROR);
		trigger_error("*********************************************" . $msg, E_USER_ERROR);
    }

    public function logWarning($msg)
    {
		trigger_error("////////////////////////////////////////////////" . $msg, E_USER_WARNING);
		trigger_error("WARNING ----> " . $msg, E_USER_WARNING);
		trigger_error("////////////////////////////////////////////////" . $msg, E_USER_WARNING);
    }

    public function trace($msg)
    {
		trigger_error("Trace Line ----> " . $msg, E_USER_WARNING);
    }
	
	public function randomString($length=10, $caps=true, $nums=false, $symbols=false)
	{
		$chars = "abcdefghijklmnopqrstuvwxyz-_";
		if($caps)
			$chars .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if($nums)
			$chars .= "1234567890";
		if($symbols)
			$chars .= "!@#$%^&*(),.;:";
		
		$str = "";
		for($i=0;$i<$length;$i++)
		{
			$str .= $chars[rand(0, strlen($chars)-1)];
		}
		
		return $str;
	}
    public function siteVar($type)
    {
		if($this->sitevars[$type])
		{
			return $this->sitevars[$type];
		}
		else
			return "";
    }
	public function jsonify($data)
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 2014 05:00:00 GMT');
		header('Content-type: application/json');
		print json_encode ($data);
        exit();
	}
	public function incl($data, $template)
	{
		$templateChain = array('default');

		$browser = new Browser();

		if (in_array($browser->getBrowser(), array(
					Browser::BROWSER_IPHONE,
					Browser::BROWSER_IPOD,
					Browser::BROWSER_IPAD
				)))
		{
			$templateChain[] = 'mobile';
		}

		extract($data);

		foreach (array_reverse($templateChain) as $templateRoot)
		{
			if (file_exists(RequestHandler::GetBaseDirectory() . 'templates/' . $templateRoot . '/' . $template))
			{
				include(RequestHandler::GetBaseDirectory() . 'templates/' . $templateRoot . '/' . $template);

				// only include the first template that was found in the chain
				break;
			}
		}
	}
	public function display($data, $template)
	{
		header("generatedBy: Tracy Lauren");
		header("Framework: Utopia Framework");
		 
		if (!isset($_REQUEST['__outmode']))
			$_REQUEST['__outmode'] = 'render';

		switch ($_REQUEST['__outmode'])
		{
			case 'xml':
				// this requires the pear XML_Serializer package to be installed
				// to install run the following command: "pear install XML_Serializer-beta"
				require_once ("XML/Serializer.php"); 

				header('Content-Type: text/xml');

				$serializer = new XML_Serializer(array(
						"indent"    => "\t",
						"linebreak" => "\n",
						"typeHints" => false,
						"addDecl"   => true,
						"encoding"  => "UTF-8",
						"rootName"   => "result",
						"defaultTagName" => "item"
					));

				$result = $serializer->serialize($data); 

				if ($result === true)
					echo $serializer->getSerializedData();
				else
					throw new BadRequestException("Failed to serialize data to XML");

				break;

			case 'json':
				header('Content-Type: text/plain');

				$__json = json_encode($data);

				if (json_last_error() != JSON_ERROR_NONE && strlen($__json) == 0)
					throw new BadRequestException("Failed to serialize data to JSON");

				print $__json;
				break;

			default:
				$templateChain = array('default');

				$browser = new Browser();

				if (in_array($browser->getBrowser(), array(
							Browser::BROWSER_IPHONE,
							Browser::BROWSER_IPOD,
							Browser::BROWSER_IPAD
						)))
				{
					$templateChain[] = 'mobile';
				}

				extract($data);

				foreach (array_reverse($templateChain) as $templateRoot)
				{
					if (file_exists(RequestHandler::GetBaseDirectory() . 'templates/' . $templateRoot . '/' . $template))
					{
						include(RequestHandler::GetBaseDirectory() . 'templates/' . $templateRoot . '/' . $template);

						// only include the first template that was found in the chain
						break;
					}
				}

				break;
		}

		// this function is only allowed to be called once, and is the final step in a request
		exit;
	}
	function getIdFromUrl($page)
	{
		$tmp = preg_split('/\//', $_SERVER['REQUEST_URI']);
		$i = 0;
		foreach($tmp as $ind => $val)
		{
			if($val == $page){$i = $ind;}
		}
		$i++;
		return $tmp[$i];
	}
	function getVarsFromUrl($page)
	{
		$tmp = preg_split('/\//', $_SERVER['REQUEST_URI']);
		$i = 0;
		$found = false;
		$out = array();
		$out2 = array();
		foreach($tmp as $ind => $val)
		{
			if($val == $page){
				
				$found = true;
			}
			if($found)
			{
				array_push($out, $val);
			}
		}
		for($i=1;$i<count($out);$i++)
		{
			array_push($out2, $out[$i]);
		}
		return $out2;
	}
}

?>
