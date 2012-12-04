<?


$core = new Core();
$data = array();

$tmp = preg_split('[/]', $_SERVER['REQUEST_URI']);
$AJAX_ACTION = null;

if(count($tmp) < 2)
	header('Location: /home');   /* redirect if there is no action */

$AJAX_ACTION = $tmp[2];
$AJAX_PARAMS = array();

for($i=3;$i<count($tmp);$i++)
{
	$AJAX_PARAMS[] = mysql_escape_string(urldecode($tmp[$i]));
}


switch($AJAX_ACTION)
{
	case 'delete': 
		/** /ajax/delete/stuffId */
		$test = new TestApi();
		$test->deleteStuff($AJAX_PARAMS[0])
	break;

}

?>