<?
$core = new Core();
$data = array();

if(count($GLOBALS['SITE_PARAMS']) < 2)
	header('Location: /home');   /* redirect if there is no action */

$AJAX_ACTION = $GLOBALS['SITE_PARAMS'][2];
$AJAX_PARAMS = array();

for($i=3;$i<count($GLOBALS['SITE_PARAMS']);$i++)
{
	$AJAX_PARAMS[] = mysql_escape_string(urldecode($GLOBALS['SITE_PARAMS'][$i]));
}

switch($AJAX_ACTION)
{
	case 'delete': 
		/** /ajax/delete/stuffId */
		$test = new TestApi();
		$test->deleteStuff($AJAX_PARAMS[0]);
	break;

}

?>