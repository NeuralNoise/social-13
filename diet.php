<?php
//Инклуды - зацеп
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort');

//Действия - зацеп
pickup('actions/diet', 'add_low_fodmap', 'delete_low_fodmap');

//Инициализируем сессию COOKIE
block("init_session");

//Подключаемся к базе данных
db_connect();

//prepared_uri
$prepared_uri=array();

//Подключаем шапку
$html.=template_get('header');

//Подключаем верхнее меню
$html.= menu_top();

//Выполняем действия
start_actions();

//Имя таблицы
$html.="<br/>".h1('FODMAP диета')."<br/>";
$html.="<h2 style='font-size:14pt;'>Справочник продуктов</h2>";
$html.="<a href='".uri_make(array('UriScript'=>'diet.php', 'add_low_fodmap'=>'yes'))."'>Добавить продукт с низким FODMAP</a><br/><br/>";

//Коннект к базе
db_connect("diet");
$q=db_query("SELECT * FROM `low_fodmaps`");
while($low_fodmap=db_fetch($q)){
	$html.=$low_fodmap['name']."<span style='padding:0 10px;'></span>"."<a href='".uri_make(array('UriScript'=>'diet.php', 'delete_low_fodmap'=>'yes', 'low_fodmap'=>$low_fodmap['id']))."' onClick=\"if(!confirm('Удалить?')) return false;\"><img src='/_content/img/remove-icon.png' style='width:8px;height:8px;'/></a><br/>";
}

//Подключаем подвал
$html.=template_get('footer');

//Хтмл - в браузер
echo $html;

//Выполняет действия
function start_actions(){
	foreach($GLOBALS['actions_for_pickup'] as $id=>$file){
		$file="action_".$file;
		$file();
	}
}

//Цепляет файл
function pickup(){
	//Получаем список подцепляемых файлов и директорию
	$files=func_get_args();
	$folder=$files[0];
	unset($files[0]);
	
	if(preg_match("/actions/", $folder)){
		$GLOBALS['actions_for_pickup']=$files;
	}
	
	//Цепляем полученные файлы
	foreach($files as $id=>$file){
		require_once($_SERVER['DOCUMENT_ROOT']."/".$folder."/".$file.".php");
	}
}

?>