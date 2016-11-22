<?php
//Удаляет версию и все ее файлы
function delete_version(){
	//Получаем глобальные переменные
	$version_id=$_GET['version'];
	
	//Запрос к базе
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	//Определяем переменные
	$project_first_version_id=db_short_easy("SELECT `first_version_id` FROM `projects` WHERE `id`=$project_id");
	
	//Запрос к базе
	$database_name=db_short_easy("SELECT `name` FROM `project_databases` WHERE `project_id`=$project_id");
	
	/*Сначала удаляем файлы*/
	delete_files($version_id);
	
	/*И только теперь удаляем записи из базы*/
	
	//Запрос к базе
	db_query("DELETE FROM `versions` WHERE `id`=$version_id");
	
	//Запрос к базе
	if($project_first_version_id==$version_id){
		db_query("UPDATE `projects` SET `first_version_id`=0 WHERE `id`=$project_id");
	}
	
	//Запрос к базе
	db_query("DELETE FROM `files` WHERE `version_id`=$version_id");
	
	//Запрос к базе
	db_query("DELETE FROM `filesdiff` WHERE `version_id`=$version_id");
	
	//Запрос к базе
	db_query("DROP DATABASE IF EXISTS `z-{$database_name}-$version_id");
	
	//Отправляем HTTP заголовок
	header("location: /versions.php?action=show_version&project=$project_id");
}

//------Блок вспомогательных функций для этого действия------//

//Удаляет файлы
function delete_files($version_id){
	//Запрос к базе
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	//Запрос к базе
	$previous_version_id=db_short_easy("SELECT `previous_version_id` FROM `versions` WHERE `id`=$version_id");
	
	//Выполнение из командной строки
	exec("rm -R /backups/increment/projects/".$project_id."/versions/".$previous_version_id."-to-".$version_id);
	exec("rm -R /backups/full/projects/".$project_id."/versions/".$version_id);
}
?>