<?

$core = new Core();
$data = array();
$data['core'] = $core;  /*<< allow for recursive templates */


$core->display($data, "home.html");

?>
