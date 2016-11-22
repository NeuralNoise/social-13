<?
require_once($_SERVER['DOCUMENT_ROOT']."/includes/uris.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/templates.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/files.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/auth.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/service.php");

//�������������� ������ COOKIE
block("init_session");

//Include config
require_once("./config.php");

//������������ � ���� ������
db_connect();

//������� � index.php
$default_page="/social.php";

if(isset($_SESSION['user'])){
	if(@$_GET['action']=="logout"){
		unset($_SESSION['user']);
		unset($_SESSION['search_string']);
		echo login_form();
	}else{
		//���� ������������ ��� �����������
		header("location: $default_page");
	}
}elseif(@$_GET['action']=='login'){
	//��� ������ ���������������
	if(check_login()){
		//� ���� ������ ����� � ������
		$_SESSION['user']=@$_POST['user'];
		header("location: $default_page");
	}else{
		//��� ��� ��� ���� �������
		echo login_form("<span style='color:red'>������ � ������ ��� ������!</span><br/>");
	}
}else{
	//������������� �� ����� ����� � �������
	//echo generate_hash("", "");
	echo login_form();
}
?>
