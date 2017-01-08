<?
require_once($_SERVER['DOCUMENT_ROOT']."/includes/uris.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/templates.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/files.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/db.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/auth.php");
require_once($_SERVER['DOCUMENT_ROOT']."/includes/service.php");

//Инициализируем сессию COOKIE
block("init_session");

//Include config
require_once("./config.php");

//Подключаемся к базе данных
db_connect();

//Переход с index.php
$default_page="/social.php";

if(isset($_SESSION['user'])){
	if(@$_GET['action']=="logout"){
		unset($_SESSION['user']);
		unset($_SESSION['search_string']);
		echo login_form();
	}else{
		//Если пользователь уже авторизован
		header("location: $default_page");
	}
}elseif(@$_GET['action']=='login'){
	//Или только авторизовыается
	if(check_login()){
		//И ввел верный логин и пароль
		$_SESSION['user']=@$_POST['user'];
		header("location: $default_page");
	}else{
		//Или они все таки неверны
		echo login_form("<span style='color:red'>Ошибка в логине или пароле!</span><br/>");
	}
}else{
	//Перебрасываем на форму входа в систему
	//echo generate_hash("", "");
	echo login_form();
}
?>
