<?php
//������� - �����
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort');

//�������� - �����
pickup('actions/diet', 'add_low_fodmap', 'delete_low_fodmap');

//�������������� ������ COOKIE
block("init_session");

//������������ � ���� ������
db_connect();

//prepared_uri
$prepared_uri=array();

//���������� �����
$html.=template_get('header');

//���������� ������� ����
$html.= menu_top();

//��������� ��������
start_actions();

//��� �������
$html.="<br/>".h1('FODMAP �����')."<br/>";
$html.="<h2 style='font-size:14pt;'>���������� ���������</h2>";
$html.="<a href='".uri_make(array('UriScript'=>'diet.php', 'add_low_fodmap'=>'yes'))."'>�������� ������� � ������ FODMAP</a><br/><br/>";

//������� � ����
db_connect("diet");
$q=db_query("SELECT * FROM `low_fodmaps`");
while($low_fodmap=db_fetch($q)){
	$html.=$low_fodmap['name']."<span style='padding:0 10px;'></span>"."<a href='".uri_make(array('UriScript'=>'diet.php', 'delete_low_fodmap'=>'yes', 'low_fodmap'=>$low_fodmap['id']))."' onClick=\"if(!confirm('�������?')) return false;\"><img src='/_content/img/remove-icon.png' style='width:8px;height:8px;'/></a><br/>";
}

//���������� ������
$html.=template_get('footer');

//���� - � �������
echo $html;

//��������� ��������
function start_actions(){
	foreach($GLOBALS['actions_for_pickup'] as $id=>$file){
		$file="action_".$file;
		$file();
	}
}

//������� ����
function pickup(){
	//�������� ������ ������������ ������ � ����������
	$files=func_get_args();
	$folder=$files[0];
	unset($files[0]);
	
	if(preg_match("/actions/", $folder)){
		$GLOBALS['actions_for_pickup']=$files;
	}
	
	//������� ���������� �����
	foreach($files as $id=>$file){
		require_once($_SERVER['DOCUMENT_ROOT']."/".$folder."/".$file.".php");
	}
}

?>