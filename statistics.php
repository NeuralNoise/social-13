<?
//Инклуды - зацеп
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort', 'windows');

//Действия - зацеп
pickup('actions', 'show_positions', 'show_histories');

//Инициализируем сессию COOKIE
block("init_session");

//Подключаемся к базе данных
db_connect();

//prepared_uri
$prepared_uri=array();

//цвета
$colors=array('red', 'green', 'blue', 'orange', 'brown', 'magenta', 'darkblue');

//Если вход не выполнен
if(!isset($_SESSION['user'])){
	echo login_form();
	exit;
}

//Подключаемся к базе данных
db_connect();

//Чистим uri
uri_clean('add_position', 'add_document', 'edit_document', 'delete_document', 'save_document', 'show_originals', 'delete_file', 'edit_file',
			'show_histories', 'search_document', 'search_string', 'show_contragents', 'show_positions', 'edit_position', 'add_position', 'delete_position',
			'add_tag');

//Html всей страницы
$html="";

//Подключаем шапку
$html.=template_get('header');

//Подключаем верхнее меню
$html.= menu_top();


//Имя таблицы
$html.="<br/>".h1("Статистика")."<br/>";

//Статистика
$search_phrases=array('<h>Ноуты и все к ним</h>', 'Ноутбук', 'Сумка', '',
						'<h>Компьютеры</h>', 'Материнская плата', 'Процессор', 'Модуль памяти', 'Жесткий диск', 'Оптический привод', 'Корпус', 'Блок питания', 'Устройство охлаждения', '',
						'<h>Мониторы и периферия</h>', 'Монитор', 'Мышь', 'Клавиатура', 'Наушники', 'Сетевой фильтр', '',
						'<h>Принтеры/МФУ/Расходники к ним</h>', 'Принтер', 'МФУ', 'Картридж', 'Тонер-картридж', '', 
						'<h>Маршрутизаторы/роутеры/модемы</h>', 'Маршрутизатор', '', 
						'<h>Телефония</h>', 'Телефон', '',
						'<h>Прочая мелочевка</h>', 'Адаптер питания', 'Кабель-удлинитель', 'Коннектор',
						'Оптический диск', 'Патч-корд', 'Сетевой адаптер', 'Термопаста',
						'Розетка компьютерная');
foreach($search_phrases as $id=>$phrase){
	if($phrase==""){
		$html.="<br/>";
	}elseif(strripos($phrase, "<h>")!==false){
		$html.="<b>".str_replace("</h>", '', str_replace("<h>", '', $phrase))."</b><br/>";
	}else{
		$html.="$phrase: ".db_easy_count("SELECT * FROM `positions` WHERE `name` LIKE '%".$phrase."%'")."&nbsp;&nbsp;&nbsp;<a href='".uri_make(array('show_positions'=>'yes', 'keyword'=>$phrase))."'>просмотреть</a><br/>";
	}
}

//Выполняем действия
start_actions();

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
	
	if($folder=='actions'){
		$GLOBALS['actions_for_pickup']=$files;
	}
	
	//Цепляем полученные файлы
	foreach($files as $id=>$file){
		require_once($_SERVER['DOCUMENT_ROOT']."/".$folder."/".$file.".php");
	}
}
?>