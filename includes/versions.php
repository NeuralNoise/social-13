<?php
//Меню сверху
function versioncontrol_menu(){
	//Получаем глобальные переменные
	global $project_id;
	
	//Определяем переменные
	$html="";
	
	//Запрос к базе
	$q=db_query("SELECT * FROM `projects` ORDER BY `sort` ASC");
	
	//Определяем переменные
	$phpself=substr($_SERVER['PHP_SELF'], 1);
	
	//Цикл
	while($projectWHILE=db_fetch($q)){
		//IF
		if($projectWHILE['id']==$project_id){
			$class="no_underlined";
			$size=trim(explode("/", exec("du -h --summarize /backups/full/projects/$project_id/"))[0]);
			$size=str_replace("G", "Гб", str_replace("K", "Кб", str_replace("M", "Мб", $size)));
			$size="<span style='font-size:10pt;font-style:italic;'>({$size})</span>";
		//ElSE
		}else{
			$class="underlined";
			$size="";
		}
		
		//Определяем переменные
		if(preg_match("/^show\_/", $_GET['action'])){
			$action=$_GET['action'];
		}else{
			$action='show_version';
		}
		$html.="<a href='/versions.php?action=$action&project=".$projectWHILE['id']."' class='$class'>".$projectWHILE['name'].$size."</a><span class='divider'></span>";
	}
	
	$html.="<a href='/versions.php?action=add_project' class='$class'><img src='/_content/img/add-icon.png' /></a><span class='divider'></span>";
	
	//Возвращаем значение функции
	return template_get('versioncontrol/menu_top', array(
															'items'=>"<br/>Проекты:<span class='divider'></span>$html",
															'project_id'=>$project_id));
}

//Меню проекта
function project_menu(){
	//Получаем глобальные переменные
	global $project_id;

	//Определяем переменные
	$html="";

	//Возвращаем значение функции
	return template_get('versioncontrol/project_menu', array(
															'project_id'=>$project_id));
}
?>