<?php

/**
 *	index.php. alll
 */
$GLOBALS['SITE_PARAMS'] = preg_split('[/]', $_SERVER['REQUEST_URI']);

$path = $GLOBALS['SITE_PARAMS'][1];
$_REQUEST['__select'] = '/'.$path;

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
