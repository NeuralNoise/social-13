<?php
//Инклуды - зацеп
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort', 'versions');

//Инициализируем сессию COOKIE
block("init_session");

/*Чистим uri*/
uri_clean('add_version', 'version', 'delete_version');

/*Проверка аутентификации*/
block("auth");

/*Задаем временную зону*/
date_default_timezone_set('Europe/Moscow');

/*Определяем переменную - HTML всей страницы*/
$html="";

/*Задаем обработчик ошибок*/
if(isset($_GET['dbg'])){
	set_error_handler("my_error_handler");
}

/*Используется при добавлении новой версии*/
$fetched_files=array();

/*Подключаемся к базе данных*/
db_connect('versioncontrol');

/*Запускаем действие*/
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

/*----Блок Функций----*/

//Цепляет файл
function pickup(){
	//Получаем глобальные переменные
	$actions_for_pickup=&$GLOBALS['actions_for_pickup'];
	
	//Получаем список подцепляемых файлов и директорию
	$files=func_get_args();
	$folder=$files[0];
	unset($files[0]);
	
	if($folder=='actions'){
		$actions_for_pickup=$files;
	}
	
	//Цепляем полученные файлы
	foreach($files as $id=>$file){
		require_once($_SERVER['DOCUMENT_ROOT']."/".$folder."/".$file.".php");
	}
}


?>