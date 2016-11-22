<?php
//Удаляет инкрементный бэкап
function delete_increment_backup(){
	//Определяем переменные
	$version_id=$_GET['version'];
	$previous_version_id=$_GET['previous_version'];
	
	/*Получаем id проекта*/
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	/*Удаляем директорию с инкрементной резервной копией*/
	exec("rm -R /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id");
	
	/*Удаляем данные об инкрементной резервной попии из базы данных*/
	db_query("DELETE FROM `filesdiff` WHERE `version_id`=$version_id AND `previous_version_id`=$previous_version_id");
	
	/*Помечаем предыдущую версию для $version_id равной нулю*/
	db_query("UPDATE `versions` SET `previous_version_id`=0 WHERE `id`=$version_id");
	
	header("location: /versions.php?action=show_version&project=$project_id&version=$version_id");
}
?>