<?php
$GLOBALS['actions_for_pickup']=array();
$GLOBALS['pages_for_pickup']=array();

//������� - �����
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort');

//�������� - �����
pickup('actions/message', 'add_message', 'delete_message', 'edit_message');
pickup('actions/comments', 'add_comment', 'delete_comment', 'edit_comment');

//�������������� ������ COOKIE
block("init_session");

//������������ � ���� ������
db_connect();

//�������� ��������������
block("auth");

//prepared_uri
$prepared_uri=array();

//���������� �����
$html.=template_get('header');

//���������� ������� ����
$html.= menu_top();

//��������� ��������
start_actions();

//���������� ��������. ���� 1.
if(!isset($_GET['page'])){
	pickup("pages", 'index');
}else{
	pickup("pages", $_GET['page']);
}

//���������� ��������. ���� 2. (������ ������ ����� ����� 1)
$html.=start_pages();
	
//���� - � �������
echo $html;

//��������� ��������
function start_actions(){
	foreach($GLOBALS['actions_for_pickup'] as $id=>$file){
		$file="action_".$file;
		$file();
	}
}

//"���������" ��������
function start_pages(){
	foreach($GLOBALS['pages_for_pickup'] as $id=>$file){
		$file="page_".$file;
		return $file();
	}
}

//������� ����
function pickup(){
	//�������� ������ ������������ ������ � ����������
	$files=func_get_args();
	$folder=$files[0];
	unset($files[0]);
	
	if(preg_match("/actions/", $folder)){
		$GLOBALS['actions_for_pickup']=array_merge($GLOBALS['actions_for_pickup'], $files);
	}

	if(preg_match("/pages/", $folder)){
		$GLOBALS['pages_for_pickup']=array_merge($GLOBALS['pages_for_pickup'], $files);
	}
	
	//������� ���������� �����
	foreach($files as $id=>$file){
		require_once($_SERVER['DOCUMENT_ROOT']."/".$folder."/".$file.".php");
	}
}

?>