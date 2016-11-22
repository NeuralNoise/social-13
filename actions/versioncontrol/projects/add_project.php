<?php
function add_project(){
	//Определяем переменные
	$html="";
	
	//Подключаем верхнее меню
	$html.= versioncontrol_menu();

	//Заголовок страницы
	$html.="<h1 style='margin:20px 0 10px 0;'>Добавить проект</h1>";
	
	if(isset($_POST['name'])){
		$project_name=$_POST['name'];
	}else{
		$html.=template_get('versioncontrol/projects/add_project', array());
	}
	
	//Возвращаем значение функции
	return template_get('header').menu_top().$html.template_get('footer');
}
?>