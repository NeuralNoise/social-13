<?php
function download(){
	//IF
	if(isset($_GET['version'])){
		//IF
		if(preg_match("/^[0-9]{1,10}$/", $_GET['version'])){
			$version_id=$_GET['version'];
		//ELSE
		}else{
			return "Ошибка. Некорректный ввод пользовательских данных (номер версии).";
		}
	//ELSE
	}else{
		return "Ошибка. Не указан номер версии.";
	}
	
	//Запрос к базе
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	

	//IF
	if(@$_GET['mode']=='increment'){
		//Запрос к базе
		$previous_version_id=db_short_easy("SELECT `previous_version_id` FROM `versions` WHERE `id`=$version_id");
		
		//Определяем переменную
		$file="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id.tgz";
		
		//Определяем переменную
		$dir="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id";
		
		//Определяем переменную
		$header_file="$previous_version_id-to-$version_id.tgz";
	}
		
	//IF
	if(@$_GET['mode']=='full'){
		//Определяем переменную
		$file="/backups/full/projects/$project_id/versions/$version_id.tgz";
		
		//Определяем переменную
		$dir="/backups/full/projects/$project_id/versions/$version_id";
		
		//Определяем переменную
		$header_file="$version_id.tgz";
	}
		
	//Запускаем команду в shell
	exec("tar -czf $file $dir");
		
	//Отправляем HTTP заголовки
	header('Content-type:  application/zip');
	header('Content-Length: ' . filesize($file));
	header("Content-Disposition: attachment; filename='$header_file'");
		
	/*Отдаем файл пользователю*/
	readfile($file);
		
	/*Даже если пользователь прервал закачку, скрипт подолжит работу*/
	ignore_user_abort(true);
	
	//Удаляем файл
	unlink($file);
}
?>