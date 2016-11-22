<?php
//������� - �����
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort', 'versions');

//�������������� ������ COOKIE
block("init_session");

/*������ uri*/
uri_clean('add_version', 'version', 'delete_version');

/*�������� ��������������*/
block("auth");

/*������ ��������� ����*/
date_default_timezone_set('Europe/Moscow');

/*���������� ���������� - HTML ���� ��������*/
$html="";

/*������ ���������� ������*/
if(isset($_GET['dbg'])){
	set_error_handler("my_error_handler");
}

/*������������ ��� ���������� ����� ������*/
$fetched_files=array();

/*������������ � ���� ������*/
db_connect('versioncontrol');

/*��������� ��������*/
$action=$_GET['action'];
$dir=$_SERVER['DOCUMENT_ROOT']."actions/versioncontrol";
$dp = opendir($dir);
while($subdir = readdir($dp)){
	if($subdir != '.' && $subdir != '..' && is_dir($dir."/".$subdir)){
		$action_file=$dir."/".$subdir."/".$action.".php";
		if(file_exists($action_file)){
			require_once($action_file);
			$html.=$action();
			break;
		}
	}
}
echo $html;

/*----���� �������----*/

//������� ����
function pickup(){
	//�������� ���������� ����������
	$actions_for_pickup=&$GLOBALS['actions_for_pickup'];
	
	//�������� ������ ������������ ������ � ����������
	$files=func_get_args();
	$folder=$files[0];
	unset($files[0]);
	
	if($folder=='actions'){
		$actions_for_pickup=$files;
	}
	
	//������� ���������� �����
	foreach($files as $id=>$file){
		require_once($_SERVER['DOCUMENT_ROOT']."/".$folder."/".$file.".php");
	}
}


?>