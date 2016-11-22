<?php
function show_project(){
	//Получаем глобальные переменные
	global $project_id;
	global $project_first_version_id;

	//Определяем переменные
	$html="";

	/*Получаем характеристики проекта*/
	if(isset($_GET['project'])){
		$project_id=$_GET['project'];
	}else{
		//Отправляем HTTP заголовок
		header("location: /projects.php?project=".db_short_easy("SELECT `id` FROM `projects` ORDER BY `sort` ASC"));
		exit;
	}
	
	$project_name=db_short_easy("SELECT `name` FROM `projects` WHERE `id`=".$project_id);
	$project_first_version_id=db_short_easy("SELECT `first_version_id` FROM `projects` WHERE `id`=".$project_id);
	
	//Подключаем верхнее меню
	$html.= versioncontrol_menu().project_menu();

	
	//Заголовок страницы
	$html.="<h1 style='margin:20px 0 10px 0;'>Инфо по проекту</h1>";

	/*Структура версий*/
	$html.=versions_structure()."<br/>";

	//Хтмл - в браузер
	return template_get('header').menu_top().$html.template_get('footer');

}

//------Блок вспомогательных функций для этого действия------//

/*Структура версий*/
function versions_structure(){
	//Получаем глобальные переменные
	global $project_id;
	global $project_first_version_id;

	/*Оценка проекта*/
	$html="<b>Оценка проекта</b><br/>";
	//IF
	if($project_first_version_id==0){
		$html.="<span style='color:red;'>Не задана базовая версия</span><br/>";
	}else{
		$html.="<span style='color:green;'>Базовая версия задана</span><br/>";
		if(!file_exists("/backups/full/projects/{$project_id}/versions/{$project_first_version_id}/")){
			$html.="<span style='color:red;'>Базовая версия не имеет полной резервной копии</span><br/>";
		}else{
			$html.="<span style='color:green;'>У базовой версии есть полная резервная копия</span><br/>";
		}
	}
	
	//Определяем переменные
	$html.="<br/><b>Расшифровка обозначений</b><br/>
			Базовая - базовая версия<br/>
			Связанная - для данной версии определена предыдущая и она существует<br/>
			ПолныйБэкап - полная резервная копия файлов и базы<br/>
			ИнкрементныйБэкап - инкрементная резервная копия файлов и базы<br/>
			<br/>";
	
	
	//Запрос к базе
	$versionsRES=db_query("SELECT * FROM `versions` WHERE `project_id`=$project_id ORDER BY `id` ASC");

	//Определяем переменные
	$iWHILE=1;
	$current_version_id=-1;
	
	//IF
	if(db_count($versionsRES)>0){
		//Цикл
		while($versionWHILE=db_fetch($versionsRES)){
			$previous_version_id=$current_version_id;
			$current_version_id=$versionWHILE['id'];
			
			//IF
			if($project_first_version_id==$versionWHILE['id']){
				$first_version='Базовая';
				$linked='';
				$html.="<br/><hr/><br/>";
			}else{
				//Определяем переменные
				$first_version='';
				
				//IF
				if(($versionWHILE['previous_version_id']!=0 && db_easy_count("SELECT * FROM `versions` WHERE `id`=".$versionWHILE['previous_version_id'])>0) || $project_first_version_id==$versionWHILE['id']){
					$linked='Связанная';
				}else{
					$linked='';
				}
				
				//IF
				if($previous_version_id==$versionWHILE['previous_version_id']){
					$html.="<img src='/_content/img/link.gif' style='display:block;margin:3px 8px;'/>";
				}else{
					$html.="<img src='/_content/img/nolink.gif' style='display:block;margin:3px 8px;'/>";
				}
				
				//Получаем информацию о хранении сведений о версии в виде файлов в директории .versioncontrol
				$hidden_dir="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$current_version_id/.versioncontrol";
				if(file_exists($hidden_dir."/info") && file_exists($hidden_dir."/new_files") && file_exists($hidden_dir."/different_files") && file_exists($hidden_dir."/deleted_files")){
					$store_info_in_files="<b style='color:green;'>Хранение в файлах налажено</b>";
				}else{
					$store_info_in_files="<b style='color:red;'>Хранение в файлах отсутствует</b>";
				}

			}

			//IF	
			file_exists("/backups/full/projects/{$project_id}/versions/{$versionWHILE['id']}/files") ? $full_backup='ПолныйБэкапФайлы' : $full_backup='';

			//IF	
			file_exists("/backups/full/projects/{$project_id}/versions/{$versionWHILE['id']}/databases") ? $full_backup_bases='ПолныйБэкапБД' : $full_backup_bases='';
			
			//IF	
			file_exists("/backups/increment/projects/{$project_id}/versions/{$versionWHILE['previous_version_id']}-to-{$versionWHILE['id']}/") ? $increment_backup='ИнкрементныйБэкап' : $increment_backup='';
			
			//Определяем переменные
			$html.="<a href='/versions.php?action=show_version&project={$project_id}&version=".$versionWHILE['id']."' style='font-size:11pt;padding-right:10px;'>".$versionWHILE['id']."</a>
					<span style='font-size:8pt;font-weight:bold;font-style:italic;'>$first_version $linked $full_backup $full_backup_bases $increment_backup $store_info_in_files</span><br/> ";
			
			//IF
			//if($iWHILE%40==0) $html.="<br/>";
			
			//Определяем переменные
			//$iWHILE++;
		}
	}else{
		//Определяем переменные
		$html.="В данном проекте еще нет версий";
	}
	
	//Возвращаем значение функции
	return $html;
}
?>