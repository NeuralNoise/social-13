<?php
echo "exit()";
exit;

//Инклуды - зацеп
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort');

//Инициализируем сессию COOKIE
block("init_session");

for($page=1;$page<=86;$page++){

	$file=file_get_contents("http://loveread.ws/read_book.php?id=10479&p=$page");

	$pattern="/\<div class\=\"MsoNormal\" style\=\"margin\:15px\; text\-align\:left\; width\:800px; color\:\#393939\;\"\>(.*)\<\/div\>\r\n\t\t\t\t\t\t\<div class\=\'navigation\' align\=\'center\'/s";

	preg_match($pattern, $file, $matches);

	$content=trim($matches[1]);

	if(file_easy_write("templates/books/ubit_peresmeshnika/$page.php", $content)!==false){
		echo $page." - OK<br/>";
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