<?
//Инклуды - зацеп
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort');

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

$num_pages=86;
$book_id=1;
$user_id=4;

//Имя таблицы
$html.="<br/>".h1('Книги')."<br/>";
$html.="<h2 style='font-size:14pt;'>Убить пересмешника</h2>";
$html.="<a href='http://loveread.ws/read_book.php?id=10479&p=1'>Источник в интернете</a><br/>";
$html.="Страниц: $num_pages";

db_connect("books");
$q_str="SELECT `page_num` FROM `memory_pages` WHERE `user_id`=$user_id AND `book_id`=$book_id";
if(isset($_GET['page'])){
	$page=$_GET['page'];
}else{
	if(db_easy_count($q_str)==0){
		$page=1;
	}else{
		$page=db_short_easy($q_str);
	}
}

if(db_easy_count($q_str)==0){
	db_query("INSERT INTO `memory_pages` SET `user_id`=$user_id, `book_id`=$book_id, `page_num`=$page");
}else{
	db_query("UPDATE `memory_pages` SET `page_num`=$page WHERE `user_id`=$user_id AND `book_id`=$book_id");
}


if($page>1){$previous_page=$page-1;}else{$previous_page=$page;}
if($page<$num_pages){$next_page=$page+1;}else{$next_page=$page;}

$pages="";
for($i=1;$i<=$num_pages;$i++){
	if($page==$i){
		$pages.="<b>".$i."</b>&nbsp;&nbsp;";
	}else{
		$pages.="<a href='/books.php?page=$i'>".$i."</a>&nbsp;&nbsp;";
	}
}

$page_manager="<div style='margin:30px auto;width:200px;'><a href='/books.php?page={$previous_page}'><img width='15' height='13' src='_content/img/books/left.png'/></a><span style='padding:0 10px;font-size:15pt;'>$page</span><a href='/books.php?page={$next_page}'><img width='15' height='13' src='_content/img/books/right.png'/></a></div>";
$html.=$page_manager;
$html.="<div style='margin:30px 15%;padding:10px 5%;text-align:justify;border:2px solid #000;'><p>";

//Подключаем подвал
$html.=template_get('books/ubit_peresmeshnika/'.$page);

$html.="</div>";

$html.=$page_manager;

//Подключаем подвал
$html.=template_get('footer');

//Хтмл - в браузер
echo $html;


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