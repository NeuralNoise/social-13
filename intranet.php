<?php
$GLOBALS['actions_for_pickup']=array();
$GLOBALS['pages_for_pickup']=array();

//Инклуды - зацеп
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort');

//Действия - зацеп
pickup('actions/message', 'add_message', 'delete_message', 'edit_message');
pickup('actions/comments', 'add_comment', 'delete_comment', 'edit_comment');

//Инициализируем сессию COOKIE
block("init_session");

//Подключаемся к базе данных
db_connect();

//Проверка аутентификации
block("auth");

//prepared_uri
$prepared_uri=array();

//Подключаем шапку
$html.=template_get('header');

//Подключаем верхнее меню
$html.= menu_top();

//Выполняем действия
start_actions();

//Подключаем страницу. Блок 1.
if(!isset($_GET['page'])){
	pickup("pages", 'index');
}else{
	pickup("pages", $_GET['page']);
}

//Подключаем страницу. Блок 2. (должен стоять после Блока 1)
$html.=start_pages();
	
//Хтмл - в браузер
echo $html;

//Выполняет действия
function start_actions(){
	foreach($GLOBALS['actions_for_pickup'] as $id=>$file){
		$file="action_".$file;
		$file();
	}
}

//"Запускает" страницы
function start_pages(){
	foreach($GLOBALS['pages_for_pickup'] as $id=>$file){
		$file="page_".$file;
		return $file();
	}
}

//Цепляет файл
function pickup(){
	//Получаем список подцепляемых файлов и директорию
	$files=func_get_args();
	$folder=$files[0];
	unset($files[0]);
	
	if(preg_match("/actions/", $folder)){
		$GLOBALS['actions_for_pickup']=array_merge($GLOBALS['actions_for_pickup'], $files);
	}

	if(preg_match("/pages/", $folder)){
		$GLOBALS['pages_for_pickup']=array_merge($GLOBALS['pages_for_pickup'], $files);
	}
	
	//Цепляем полученные файлы
	foreach($files as $id=>$file){
		require_once($_SERVER['DOCUMENT_ROOT']."/".$folder."/".$file.".php");
	}
}

?>