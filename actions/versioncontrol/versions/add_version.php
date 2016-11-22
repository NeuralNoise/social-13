<?php
//Создает новую версию
function add_version(){
	//Получаем глобальные переменные
	global $project_id;
	global $project_dir;
	
	//Определяем переменные
	$project_id=$_GET['project'];
	
	//Запрос к базе
	$project_dir=db_short_easy("SELECT `dir` FROM `projects` WHERE `id`=$project_id");
	
	/*Получаем id предыдущей версии*/
	//Запрос к базе
	$previous_versionRES=db_query("SELECT * FROM `versions` WHERE `project_id`=$project_id ORDER BY `id` DESC LIMIT 1");
	if(db_count($previous_versionRES)>0){
		$previous_version_id=db_fetch($previous_versionRES)['id'];
	}else{
		$previous_version_id=0;
	}

	//Запрос к базе
	$q=db_query("INSERT INTO `versions` SET `project_id`=$project_id, `previous_version_id`=$previous_version_id, `date`='".date("Y-m-d H:i:s")."'");
	
	//Получаем id записи, вставленной в БД
	$version_id=db_insert_id($q);
	
	/*Помечаем данную версию, как первую в проекте, если она таковая*/
	if(db_short_easy("SELECT `first_version_id` FROM `projects` WHERE `id`=$project_id")==0 && db_easy_count("SELECT * FROM `versions` WHERE `project_id`=$project_id AND `id`!=$version_id")==0){
		db_query("UPDATE `projects` SET `first_version_id`=$version_id WHERE `id`=$project_id");
		
		/*Резервируем файлы*/
		copy_files($version_id);
	}
	
	/*Добавляем информацию о файлах в базу*/
	insert_files($project_dir, $version_id);
	
	/*Резервируем базы*/
	save_databases($version_id);
	
	/*Добавляем файлы, которые изменились, а также инфо о них в БД*/
	save_file_diff($previous_version_id, $version_id);
	
	//Отправляем HTTP заголовок
	header("location: /versions.php?action=show_version&project=$project_id&version=$version_id");
}

//------Блок вспомогательных функций для этого действия------//

//Делает полную резервную копию файлов
function copy_files($version_id){
	//Запрос к базе
	$project=db_easy("SELECT * FROM `projects` WHERE `id`=".db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id"));
	
	//Вычисляем переменную
	$files_dir="/backups/full/projects/{$project['id']}/versions/$version_id/files";
	
	/*Создаем директорию для файлов версии*/
	exec("mkdir -p ".$files_dir);
	
	/*Копируем файлы в эту директорию*/
	exec("cp -R ".$project['dir']."* ".$files_dir);
}

//Резервируем базы данных
function save_databases($version_id){
	//Запрос к базе
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	//Запрос к базе
	$previous_version_id=db_short_easy("SELECT `previous_version_id` FROM `versions` WHERE `id`=$version_id");
	
	//Определяем переменную
	$full_dir="/backups/full/projects/$project_id/versions/$version_id/databases";

	//Определяем переменную
	$previous_full_dir="/backups/full/projects/$project_id/versions/$previous_version_id/databases";
	
	//Запрос к базе
	$q=db_query("SELECT * FROM `project_databases` WHERE `project_id`=$project_id");
	
	//Цикл
	while($databaseWHILE=db_fetch($q)){
		if(trim($databaseWHILE['name'])!=""){
			/*Создаем служебную БД, которая необходима для создания инкрементной копии*/
			if(file_exists($previous_full_dir."/".$databaseWHILE['name'].".sql")){
				db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$previous_version_id}`");
				db_query("CREATE DATABASE `z-{$databaseWHILE['name']}-{$previous_version_id}`");
				exec("mysql -uilya -plocal z-{$databaseWHILE['name']}-{$previous_version_id} < ".$previous_full_dir."/".$databaseWHILE['name'].".sql");
			}
		
			/*Инкрементная копия БД*/
			if($previous_version_id){
				$increment_dir="/backups/increment/projects/$project_id/versions/".$previous_version_id."-to-".$version_id."/databases";

				/*Создаем директорию*/
				exec("mkdir -p ".$increment_dir);
				
				/*Делаем полный бэкап БД в инкрементную директорию*/
				exec("mysqldump -uilya -plocal {$databaseWHILE['name']} > ".$increment_dir."/".$databaseWHILE['name'].".sql");
				
				exec("schemasync --output-directory=$increment_dir mysql://ilya:local@localhost:3306/{$databaseWHILE['name']} mysql://ilya:local@localhost:3306/z-{$databaseWHILE['name']}-{$previous_version_id}");
				exec("mv $increment_dir/z-{$databaseWHILE['name']}-{$previous_version_id}.".date("Ymd").".patch.sql $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
				exec("mv $increment_dir/z-{$databaseWHILE['name']}-{$previous_version_id}.".date("Ymd").".revert.sql $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
				if(count(file("$increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql"))==9){
					exec("rm $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
					exec("rm $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
				}else{
					exec("sed 1,9d $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql > $increment_dir/sed_temp_file.txt");
					exec("cat $increment_dir/sed_temp_file.txt > $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
					exec("sed 1,9d $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql > $increment_dir/sed_temp_file.txt");
					exec("cat $increment_dir/sed_temp_file.txt > $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
					exec("rm $increment_dir/sed_temp_file.txt");
				}
				exec("rm $increment_dir/schemasync.log");
			}
			
			/*Удаляем служебную БД*/
			db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$previous_version_id}`");
			
			/*Полная копия БД*/
			exec("mkdir -p ".$full_dir);
			exec("mysqldump -uilya -plocal {$databaseWHILE['name']} > ".$full_dir."/".$databaseWHILE['name'].".sql");
		}
	}
}

//Записывает изменения файлов
function save_file_diff($previous_version_id, $version_id){
	//Получаем глобальные переменные
	global $fetched_files;
	global $project_id;
	
	//Определяем переменную
	$hidden_dir="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/.versioncontrol";
	
	//Создаем директорию
	exec("mkdir -p $hidden_dir");
	
	//Записываем в файл
	file_easy_write($hidden_dir."/info", "Version: $version_id\nPrevious version: $previous_version_id");
	
	//Определяем переменные
	$files=versions_compare_with_files($previous_version_id, $version_id);
	
	//Определяем переменную
	$file_text="";
	
	//Цикл
	foreach($files['new'] as $name=>$file){
		$file_text.="";
		db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='new', `md5`='{$file['md5']}'");
		copy_diff_file($previous_version_id, $version_id, $name);
	}
	
	//Цикл
	foreach($files['different'] as $name=>$file){
		db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='different', `md5`='{$file['md5']}'");
		copy_diff_file($previous_version_id, $version_id, $name);
	}

	//Цикл
	foreach($files['deleted'] as $name=>$file){
		db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='deleted', `md5`='{$file['md5']}'");
		copy_diff_file($previous_version_id, $version_id, $name);
	}
}

//Резервирует один файл
function copy_diff_file($previous_version_id, $version_id, $file){
	//Получаем глобальные переменные
	global $project_id;
	global $project_dir;
	
	//Определяем переменные
	$version_dir=correct_path("/backups/increment/projects/".$project_id."/versions/".$previous_version_id."-to-".$version_id);
	$file_dir=$version_dir."files/".substr($file, 0, strlen($file)-strlen(end(explode('/', $file))));
	
	/*Создаем директорию для файлов версии*/
	exec("mkdir -p ".$file_dir);
	exec("cp ".$project_dir.$file." ".$version_dir."files/".$file);
}

//Рекурсивно получает список всех файлов директории, а также их md5 сумму.
//Если найден файл, то записывает его сведения о нем в базу
function insert_files($f, $version_id){
	//Получаем глобальные переменные
	global $project_dir;
	global $fetched_files;
	
	if(is_dir($f) && !preg_match("/.*\.$/", $f)){
		$dp=opendir($f);
		while($f_new = readdir($dp)){
			if(is_dir($f) && !preg_match("/.*\.$/", $f)){
				$f=correct_path($f);
			}
			insert_files($f.$f_new, $version_id);
		}
	}elseif(!preg_match("/.*\.$/", $f)){
		$f_short=substr($f, strlen($project_dir));
		$md5=md5_file($f);
		db_query("INSERT INTO `files` SET `version_id`=$version_id, `name`='$f_short', `md5`='".$md5."'");
		$fetched_files[$version_id]['all'][$f_short]['md5']=$md5;
	}
}

//Сравнивает версии
function versions_compare_with_files($previous_version_id, $version_id){
	//Получаем глобальные переменные
	global $fetched_files;

	//Определяем переменные
	$version_files=$fetched_files[$version_id]['all'];
	
	//Определяем переменные
	$different=array();
	$new=array();
	$deleted=array();
	$previous_version_files=array();
	
	//Запрос к базе
	$previous_version_filesRES=db_query("SELECT * FROM `files` WHERE `version_id`=".$previous_version_id);
	
	//Цикл
	while($r=db_fetch($previous_version_filesRES)){
		$previous_version_files[$r['name']]=$r;
	}
	
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