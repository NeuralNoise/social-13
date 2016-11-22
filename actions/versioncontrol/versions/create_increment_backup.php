<?php
//Создает инкрементный файловый бэкап из двух полных бэкапов
function create_increment_backup(){
	//Получаем глобальные переменные
	global $project_id;
	global $project_dir;
	global $fetched_files;

	//Определяем переменные
	$version_id=$_GET['version'];
	$previous_version_id=$_GET['previous_version'];
	$html="";
	$different=array();
	$new=array();
	$deleted=array();
	
	//Запрос к базе
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	$project_dir=db_short_easy("SELECT `dir` FROM `projects` WHERE `id`=$project_id");
	
	/*Проверяем, что обе версии принадлежат одному проекту*/
	if($project_id!=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$previous_version_id")){
		return "Ошибка. Обе версии должны принадлежать одному проекту.";
	}
	
	/*Проверяем, что существуют полные файловые бэкапы, из которых будем создавать инкрементный*/
	if(!file_exists("/backups/full/projects/$project_id/versions/$version_id/files/") || !file_exists("/backups/full/projects/$project_id/versions/$previous_version_id/files/")){
		return "Ошибка. Для обеих версий должны существовать директории с полными файловыми бэкапами.";
	}
	
	/*Получаем массив version_files*/
	get_files_array("/backups/full/projects/$project_id/versions/$version_id/files/", "/backups/full/projects/$project_id/versions/$version_id/files/", $version_id);
	
	/*Получаем массив previous_version_files*/
	get_files_array("/backups/full/projects/$project_id/versions/$previous_version_id/files/", "/backups/full/projects/$project_id/versions/$previous_version_id/files/", $previous_version_id);
	
	/*Создаем инкрементную резервную копию файлов*/
	increment_file_backup($version_id, $previous_version_id);
	
	/*Создаем инкрементную резервную копию баз данных*/
	increment_database_backup($version_id, $previous_version_id);
	
	/*Помечаем предыдущую версию для $version_id*/
	db_query("UPDATE `versions` SET `previous_version_id`=$previous_version_id WHERE `id`=$version_id");
	
	//Отправляем HTTP заголовок
	header("location: /versions.php?action=show_version&project=$project_id&version=$version_id");
}

//Рекурсивно получает список всех файлов директории, а также их md5 сумму.
//Сведения о файле заносит в глобальный массив
function get_files_array($dir, $f, $version_id){
	//Получаем глобальные переменные
	global $fetched_files;
	
	if(is_dir($f) && !preg_match("/.*\.$/", $f)){
		$dp=opendir($f);
		while($f_new = readdir($dp)){
			if(is_dir($f) && !preg_match("/.*\.$/", $f)){
				$f=correct_path($f);
			}
			get_files_array($dir, $f.$f_new, $version_id);
		}
	}elseif(!preg_match("/.*\.$/", $f)){
		$f_short=substr($f, strlen($dir));
		$md5=md5_file($f);
		$fetched_files[$version_id]['all'][$f_short]['md5']=$md5;
	}
}

//Инкрементный бэкап файлов
function increment_file_backup($version_id, $previous_version_id){
	//Получаем глобальные переменные
	global $project_id;
	global $fetched_files;
	
	//Определяем переменные
	$version_files=$fetched_files[$version_id]['all'];
	$previous_version_files=$fetched_files[$previous_version_id]['all'];
	
	/*Создаем папку для хранения инкрементного файлового бэкапа*/
	exec("mkdir -p /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files");
	
	/*Измененные файлы*/
	foreach($previous_version_files as $name=>$previous_version_file){
		if(isset($version_files[$name]) && $version_files[$name]['md5']!=$previous_version_file['md5']){
			$different[$name]['md5']=$version_files[$name]['md5'];
			db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='different', `md5`='".$version_files[$name]['md5']."'");
			exec("mkdir -p /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files/".substr($name, 0, strlen($name)-strlen(end(explode('/', $name)))));
			exec("cp -R /backups/full/projects/$project_id/versions/$version_id/files/$name /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files/$name");
		}
	}
	
	/*Новые файлы*/
	foreach($version_files as $name=>$version_id_file){
		if(!isset($previous_version_files[$name])){
			$new[$name]['md5']=$version_files[$name]['md5'];
			db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='new', `md5`='".$version_files[$name]['md5']."'");
			exec("mkdir -p /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files/".substr($name, 0, strlen($name)-strlen(end(explode('/', $name)))));
			exec("cp -R /backups/full/projects/$project_id/versions/$version_id/files/$name /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files/$name");
		}
	}
	
	/*Удаленные файлы*/
	foreach($previous_version_files as $name=>$previous_version_file){
		if(!isset($version_files[$name])){
			$deleted[$name]['md5']=$version_files[$name]['md5'];
			db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='deleted', `md5`=''");
		}
	}
}

//Инкрементный бэкап базы данных
function increment_database_backup($version_id, $previous_version_id){
	//Определяем глобальные переменные
	global $project_id;
	
	//Определяем переменные
	$full_dir="/backups/full/projects/$project_id/versions/$version_id/databases";
	$previous_full_dir="/backups/full/projects/$project_id/versions/$previous_version_id/databases";
	$increment_dir="/backups/increment/projects/$project_id/versions/".$previous_version_id."-to-".$version_id."/databases";
	
	/*Создаем директорию для инкрементной копии базы данных*/
	exec("mkdir -p ".$increment_dir);
	
	//Запрос к базе
	$q=db_query("SELECT * FROM `project_databases` WHERE `project_id`=$project_id");

	//Цикл
	while($databaseWHILE=db_fetch($q)){
		if(trim($databaseWHILE['name'])!=""){
			if(file_exists($previous_full_dir."/".$databaseWHILE['name'].".sql")){
				/*Удаляем служебные БД, если существовали*/
				db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$version_id}`");
				db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$previous_version_id}`");
				
				/*Создаем служебные БД*/
				db_query("CREATE DATABASE `z-{$databaseWHILE['name']}-{$version_id}`");
				db_query("CREATE DATABASE `z-{$databaseWHILE['name']}-{$previous_version_id}`");
				
				/*Заливаем полный бэкап БД в только что созданные базы*/
				exec("mysql -uilya -plocal z-{$databaseWHILE['name']}-{$version_id} < ".$full_dir."/".$databaseWHILE['name'].".sql");
				exec("mysql -uilya -plocal z-{$databaseWHILE['name']}-{$previous_version_id} < ".$previous_full_dir."/".$databaseWHILE['name'].".sql");
			}

			/*SchemaSync создает прямой и обратный патч-разницу между базами*/
			exec("schemasync --output-directory=$increment_dir mysql://ilya:local@localhost:3306/z-{$databaseWHILE['name']}-{$version_id} mysql://ilya:local@localhost:3306/z-{$databaseWHILE['name']}-{$previous_version_id}");
			
			/*Переименовываем патчи*/
			exec("mv $increment_dir/z-{$databaseWHILE['name']}-{$previous_version_id}.".date("Ymd").".patch.sql $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
			exec("mv $increment_dir/z-{$databaseWHILE['name']}-{$previous_version_id}.".date("Ymd").".revert.sql $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
			
			//IF
			if(file_exists("$increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql")){
				if(count(file("$increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql"))==9){
					exec("rm $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
					exec("rm $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
				//ElSE
				}else{
					exec("sed 1,9d $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql > $increment_dir/sed_temp_file.txt");
					exec("cat $increment_dir/sed_temp_file.txt > $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
					exec("sed 1,9d $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql > $increment_dir/sed_temp_file.txt");
					exec("cat $increment_dir/sed_temp_file.txt > $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
					exec("rm $increment_dir/sed_temp_file.txt");
				}
			}
			
			/*Удаляем файл логов schemasync*/
			exec("rm $increment_dir/schemasync.log");
			
			/*Удаляем служебную БД*/
			db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$version_id}`");
			db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$previous_version_id}`");
		}
	}
}
?>