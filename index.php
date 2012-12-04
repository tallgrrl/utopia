<?php

$url = $_SERVER['REQUEST_URI'];
if(strpos($url,'http://') === 0) {
   $parts = parse_url($url);
   $path = $parts['path'];
} else {
	$path = $url;
}

//trigger_error("PATH=$path");

if($path == "/" || $path == "")
{
	header("Location: /home");  /* initialize to /handlers/home.php*/
}
else
{	
	require_once (dirname(__FILE__) . '/classes/CustomAutoLoader.class.php');

	$uri_selector = $_REQUEST['__select'];

	RequestHandler::ExecuteRequest();
}
