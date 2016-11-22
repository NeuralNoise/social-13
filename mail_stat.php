<?
//Инклуды - зацеп
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort');


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
$html.="<br/>".h1("Статистика Postfix")."<br/>";

//Статистика
/*начало: меню*/
$html.="<a href='?show_log=yes'>Показать лог</a>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<a href='?show_emails=yes'>Показать email-ы</a> (<a href='?show_emails=yes&domain=acoustic.ru'>acoustic.ru</a>&nbsp;&nbsp;или&nbsp;&nbsp;<a href='?show_emails=yes&domain=dekotech.ru'>dekotech.ru</a>)
		&nbsp;&nbsp;&nbsp;&nbsp;
		<a href='?show_spf_fails=yes'>Некорректные SPF</a> 
		&nbsp;&nbsp;&nbsp;&nbsp;
		<a href='?show_ptr_fails=yes'>Некорректные PTR</a> 
		<br/><br/>";
/*конец: меню*/

$text=file_easy_read("./mail_stat/mail.log");
$lines=explode(PHP_EOL, $text);

if(@$_GET['show_log']=='yes'){
	/*начало: отображение файла лога*/
	$html.="<b>Количество строк:</b> ".count($lines)."<br/><br/>";
	foreach($lines as $key=>$line){
		$line = htmlspecialchars($line);
		$html.=$line."<br/>";
	}
	/*конец: отображение файла лога*/
}
	
if(@$_GET['show_emails']=='yes'){	
	$search="@";
	if(isset($_GET['domain'])){
		$domain=$_GET['domain'];
		$search.=$domain;
	}
	/*начало: подсчет всех email-ов в логе*/
	$emails=array();
	preg_match_all('/<.*?>/', $text, $matches); 
	foreach ($matches[0] as $match) { 
		$match=substr($match, 1, strlen($match)-2);
		if(strpos($match, $search)!==false) $emails[]=htmlspecialchars($match); 
	} 
	$emails=array_unique($emails);
	$html.="<b>Количество email-ов:</b> ".count($emails)."<br/><br/>";
	foreach($emails as $key=>$email){
		$html.=$email."<br/>";
	}
	/*конец: подсчет всех email-ов в логе*/
}

if(@$_GET['show_spf_fails']=='yes'){
	/*начало: подсчет некорректных spf*/
	$spf_fails_temp=array();$spf_fails=array();
	foreach($lines as $key=>$line){
		if(strpos($line, "SPF fail - not authorized")!==false) $spf_fails_temp[]=$line;
	}
	foreach($spf_fails_temp as $key=>$spf_fail_temp){
		$spf_fails[$key]['line']=$spf_fail_temp;
	}
	$html.="<b>Некорректных SPF:</b> ".count($spf_fails)."<br/><br/>";
	foreach($spf_fails as $key=>$spf_fail){
		preg_match('/([a-zA-Z]{3}) ([0-9]{2}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2}) .* from\=<(.*)> to\=<(.*)> /', $spf_fail['line'], $matches); 
		$spf_fails[$key]['year']=$year=date("Y");
		$spf_fails[$key]['month']=$month=date("m", strtotime(htmlspecialchars($matches[1])));
		$spf_fails[$key]['day']=$day=htmlspecialchars($matches[2]);
		$spf_fails[$key]['hour']=$hour=htmlspecialchars($matches[3]);
		$spf_fails[$key]['minute']=$minute=htmlspecialchars($matches[4]);
		$spf_fails[$key]['second']=$second=htmlspecialchars($matches[5]);
		$spf_fails[$key]['from']=$from=htmlspecialchars($matches[6]);
		$spf_fails[$key]['to']=$to=htmlspecialchars($matches[7]);
		$html.="$year/$month/$day $hour:$minute:$second <b>От:</b> ".$spf_fails[$key]['from']." <b>Кому: </b>".$to."<br/>";
		//echo "<br/><br/>";
		//$html.=$matches[2]."<br/><br/>";
		//$html.=htmlspecialchars($spf_fail)."<br/><br/>";
	}
	/*конец: подсчет некорректных spf*/
}

if(@$_GET['show_ptr_fails']=='yes'){
	/*начало: подсчет некорректных ptr*/
	$ptr_fails_temp=array();$ptr_fails=array();
	foreach($lines as $key=>$line){
		if(strpos($line, "Client host rejected: cannot find your hostname")!==false) $ptr_fails_temp[]=$line;
	}
	foreach($ptr_fails_temp as $key=>$ptr_fail_temp){
		$ptr_fails[$key]['line']=$ptr_fail_temp;
	}
	$html.="<b>Некорректных ptr:</b> ".count($ptr_fails)."<br/><br/>";
	foreach($ptr_fails as $key=>$ptr_fail){
		preg_match('/([a-zA-Z]{3}) ([0-9]{2}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2}) .* from\=<(.*)> to\=<(.*)> /', $ptr_fail['line'], $matches); 
		$ptr_fails[$key]['year']=$year=date("Y");
		$ptr_fails[$key]['month']=$month=date("m", strtotime(htmlspecialchars($matches[1])));
		$ptr_fails[$key]['day']=$day=htmlspecialchars($matches[2]);
		$ptr_fails[$key]['hour']=$hour=htmlspecialchars($matches[3]);
		$ptr_fails[$key]['minute']=$minute=htmlspecialchars($matches[4]);
		$ptr_fails[$key]['second']=$second=htmlspecialchars($matches[5]);
		$ptr_fails[$key]['from']=$from=htmlspecialchars($matches[6]);
		$ptr_fails[$key]['to']=$to=htmlspecialchars($matches[7]);
		$html.="$year/$month/$day $hour:$minute:$second <b>От:</b> ".$ptr_fails[$key]['from']." <b>Кому: </b>".$to."<br/>";
		//echo "<br/><br/>";
		//$html.=$matches[2]."<br/><br/>";
		//$html.=htmlspecialchars($ptr_fail)."<br/><br/>";
	}
	/*конец: подсчет некорректных ptr*/
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