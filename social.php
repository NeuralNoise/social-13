<?php
$GLOBALS['actions_for_pickup']=array();
$GLOBALS['pages_for_pickup']=array();

//Include config
require_once("./config.php");

//Include several scripts
foreach(array('service', 'db', 'tables', 'files', 'templates', 'auth') as $empty=>$script){
	require_once('./includes/'.$script.".php");
}


//Initialize COOKIE session
block("init_session");

//Connect to database
db_connect();

//Check authentication
block("auth");

//HEADER
$html.=template_get('header');

$html.=template_get('intranet/main');

//FOOTER
$html.=template_get('footer');

echo $html;
?>