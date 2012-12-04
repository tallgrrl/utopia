<?php

class DisplayHelper
{
	public static function formatDate($formatString, $timeOrString)
	{
		if (preg_match('/^[0-9]$/', $timeOrString))
			return date($formatString, $timeOrString);
		else
			return date($formatString, strtotime($timeOrString));
	}

	public static function gossamerPath($path, $gossamerString)
	{
		static $sitevars;

		if (!isset($sitevars))
		{
			// this only occurs once per request, as $sitevars is a static variable
			$tmp = parse_ini_file(RequestHandler::GetBaseDirectory() . "/ini/sitevars.ini", true);

			if(isset($_SERVER['HTTP_HOST']))
				$sitevars = $tmp[$_SERVER['HTTP_HOST']];
			else
				$sitevars = $tmp['cmdline'];
		}
			
		/// Check if Path Exists
		if ($path && ($path = trim($path)))
		{
			if (substr($path, 0, 4) != 'http')
				$path	= 'http' . ((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 's' : '') . '://'
						. $sitevars['GOSSAMER_HOST'] . $path;

			/// Dont rebuild image if servlet string exists already
			if (strpos($path, $gossamerString) !== false)
				return $path;
 			
 			/// Find Last instance of "."
			$ind = self::lastIndexOf($path, ".");
 
			if($ind > 0)
			{
				/// if "." exists, split here
				$suffix = substr($path, $ind+1, strlen($path));
				$p = substr($path, 0,  $ind);

				/// return new string
				return $p . ":" . $gossamerString ."." . $suffix;
			}
			else
				return $path;
		}
 
		return null;
	}

	private static function lastIndexOf($string,$item)
	{  
	    $index = strpos(strrev($string), strrev($item));  

	    if ($index)
		{  
	        $index = strlen($string)-strlen($item)-$index;  
	        return $index;  
	    }  
		else  
	        return -1;  
	} 

	public function possessive($name)
	{
		if (empty($name))
			return $name;

		if (strtolower($name[strlen($name)-1]) != 's')
			return $name . "'s";

		return $name . "'";
	}

	public function replaceActivityFeedToken($feed, $for, $about)
	{
		$feed = preg_replace('/__FOR__/', $for, $feed);
		$feed = preg_replace('/__ABOUT__/', $about, $feed);

		return $feed;
	}
}

?>
