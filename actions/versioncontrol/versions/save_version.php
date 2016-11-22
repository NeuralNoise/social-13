<?php
//Сохраняет версию
function save_version(){
	//Определяем переменные
	$version_id=(int)$_GET['version'];
	
	//Запрос к базе
	$previous_version_id=db_short_easy("SELECT `previous_version_id` FROM `versions` WHERE `id`=$version_id");
	
	//Запрос к базе
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	//Определяем переменную
	$hidden_dir="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/.versioncontrol";

	//Создаем директорию
	exec("mkdir -p $hidden_dir");
	
	//IF
	if(isset($_POST['comment'])){
		//Запрос к базе
		$q=db_query("UPDATE `versions` SET `comment`='".$_POST['comment']."' WHERE `id`=".$version_id);
		$comment_text="\r\nComment:\r\n".$_POST['comment'];
	}else{
		$comment_text="";
	}
	
	
	//НАЧАЛО: Берем список файлов версии из БД и записываем в файл
	//if(db_short_easy("SELECT `in_files` FROM `versions` WHERE `id`=$version_id")==1){
		//Этот список в виде массива получаем из БД (таблицы `files`
		$files=versions_compare_before_save($previous_version_id, $version_id);
		
		//show($files);
		
		foreach(array('new', 'different', 'deleted') as $empty=>$type){
			//Определяем переменные
			$files_text="";
			
			//FOREACH
			foreach($files[$type] as $name=>$file){
				$files_text.="$name:{$file['md5']}\r\n";
			}
			
			//Записываем в файл
			file_easy_write($hidden_dir."/{$type}_files", $files_text);
		}
	//}
	//КОНЕЦ: Берем список файлов версии из БД и записываем в файл
	
	//Запрос к базе
	db_query("UPDATE `versions` SET `in_files`=1 WHERE `id`=$version_id");
	
	//Определяем переменную
	$file_text="Version: $version_id\r\nPrevious version: $previous_version_id$comment_text\r\n";
	
	//Записываем в файл
	file_easy_write($hidden_dir."/info", $file_text);

	//Отправляем HTTP заголовок
	header("location: /versions.php?action=show_version&project=$project_id&version=$version_id");
}

//Сравнивает версии
function versions_compare_before_save($previous_version_id, $version_id){
	//Определяем переменные
	$different=array();
	$new=array();
	$deleted=array();
	$version_files=array();
	$previous_version_files=array();
	
	/*НАЧАЛО: Получаем список файлов текущей версии*/
	//Запрос к базе
	$version_filesRES=db_query("SELECT * FROM `files` WHERE `version_id`=".$version_id);
	
	//Цикл
	while($r=db_fetch($version_filesRES)){
		$version_files[$r['name']]['md5']=$r['md5'];
	}
	/*КОНЕЦ: Получаем список файлов текущей версии*/
	
	
	/*НАЧАЛО: Получаем список файлов предыдущей версии*/
	//Запрос к базе
	$previous_version_filesRES=db_query("SELECT * FROM `files` WHERE `version_id`=".$previous_version_id);
	
	//Цикл
	while($r=db_fetch($previous_version_filesRES)){
		$previous_version_files[$r['name']]=$r;
	}
	/*КОНЕЦ: Получаем список файлов предыдущей версии*/
	
	//Цикл
	foreach($previous_version_files as $name=>$previous_version_file){
		if(isset($version_files[$name]) && $version_files[$name]['md5']!=$previous_version_file['md5']){
			$different[$name]['md5']=$version_files[$name]['md5'];
		}
	}
	
	//Цикл
	foreach($version_files as $name=>$version_id_file){
		if(!isset($previous_version_files[$name])){
			$new[$name]['md5']=$version_files[$name]['md5'];
		}
	}
	
	//Цикл
	foreach($previous_version_files as $name=>$previous_version_file){
		if(!isset($version_files[$name])){
			$deleted[$name]['md5']=$version_files[$name]['md5'];
		}
	}
	
	//Возвращаем значение функции
	return array('different'=>$different, 'new'=>$new, 'deleted'=>$deleted);
}
?>