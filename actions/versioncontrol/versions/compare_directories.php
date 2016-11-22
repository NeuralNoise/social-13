<?php


//Создает инкрементный файловый бэкап из двух полных бэкапов
function compare_directories(){
	//Определяем глобальные переменные
	global $fetched_files;
	global $direxclusions;
	
	//Определяем переменные
	$html="";
	
	/*Подключаем верхнее меню*/
	$html.= versioncontrol_menu();

	/*Выводим заголовок страницы*/
	$html.="<h1 style='margin:20px 0 10px 0;'>Сравнить директории</h1>";

	//IF
	if(isset($_POST['dir'])){
		/*Проверяем данные от пользователя*/
		if(preg_match("/^[A-z0-9\_\-\.\/]{1,500}$/", $_POST['dir'])){
			$dir=$_POST['dir'];
		}else{
			return "Ошибка. Недопустимый формат данных (dir) от пользователя.";
		}

		/*Проверяем данные от пользователя*/
		if(preg_match("/^[A-z0-9\_\-\.\/]{1,500}$/", $_POST['previous_dir'])){
			$previous_dir=$_POST['previous_dir'];
		}else{
			return "Ошибка. Недопустимый формат данных (previous_dir) от пользователя.";
		}
		
		//Определяем переменные
		$different=array();
		$new=array();
		$deleted=array();
		
		/*Проверяем сущестование директории*/
		if(!file_exists($dir)){
			return "Ошибка. Директория (dir) не существует.";
		}

		/*Проверяем сущестование директории*/
		if(!file_exists($previous_dir)){
			return "Ошибка. Директория (previous_dir) не существует.";
		}
		
		/*Корректируем пути*/
		$dir=correct_path($dir);
		$previous_dir=correct_path($previous_dir);
		
		/*Проверяем данные от пользователя*/
		if(is_array($_POST['direxclusions'])){
			$direxclusions=$_POST['direxclusions'];
		}
		
		/*Убираем "пустые" директории-исключения*/
		foreach($direxclusions as $empty=>$direxclusion){
			if(trim($direxclusion)=="") unset($direxclusions[$empty]);
		}
		
		/*Сохраняем данные по умолчанию*/
		save_default_data($dir, $previous_dir, $direxclusions);
		
		/*Выводим данные о директориях*/
		$html.="Директория, которую сравниваем: $dir<br/>";
		$html.="Дериктория, с которой сравниваем: $previous_dir<br/>";
		if(is_array($direxclusions)){
			foreach($direxclusions as $empty=>$direxclusion){
				$html.="Дериктория-исключение №{$empty}: {$direxclusion}<br/>";
			}
		}
		$html.="<br/>";
		
		/*Получаем массив version_files*/
		get_files_array($dir, $dir);
		
		/*Получаем массив previous_version_files*/
		get_files_array($previous_dir, $previous_dir);
		
		/*Создаем инкрементную резервную копию файлов*/
		$html.=show_differences($dir, $previous_dir);
	//ELSE
	}else{
		//Определяем переменные
		$default_direxclusions_html="";
		
		//Запросы к базе
		$default_dir=db_short_easy("SELECT `value` FROM `compare_directories_defaults` WHERE `name`='dir'");
		$default_previous_dir=db_short_easy("SELECT `value` FROM `compare_directories_defaults` WHERE `name`='previous_dir'");
		
		//Цикл
		for($i=1;$i<=5;$i++){
			$default_direxclusionRES=db_query("SELECT `value` FROM `compare_directories_defaults` WHERE `name`='direxclusion{$i}'");
			db_count($default_direxclusionRES)>0 ? $default_direxclusion=db_fetch($default_direxclusionRES)['value'] : $default_direxclusion="";
			$default_direxclusions_html.="<tr><td>Директория-исключение №{$i}:</td><td><input type='text' name='direxclusions[{$i}]' value='$default_direxclusion' style='width:300px;' /></td></tr>";
		}
		
		/*Подключаем шаблон*/
		$html.=template_get("versioncontrol/compare_directories", array('default_dir'=>$default_dir, 'default_previous_dir'=>$default_previous_dir, 'default_direxclusions'=>$default_direxclusions_html));
	}
	
	//Возвращаем значение функции
	return template_get('header').menu_top().$html.template_get('footer');
}

//Рекурсивно получает список всех файлов директории, а также их md5 сумму.
//Сведения о файле заносит в глобальный массив
function get_files_array($dir, $f){
	//Получаем глобальные переменные
	global $fetched_files;
	
	if(is_dir($f) && !preg_match("/.*\.$/", $f)){
		$dp=opendir($f);
		while($f_new = readdir($dp)){
			if(is_dir($f) && !preg_match("/.*\.$/", $f)){
				$f=correct_path($f);
			}
			get_files_array($dir, $f.$f_new);
		}
	}elseif(!preg_match("/.*\.$/", $f)){
		$f_short=substr($f, strlen($dir));
		$md5=md5_file($f);
		$fetched_files[$dir]['all'][$f_short]['md5']=$md5;
	}
}

//Инкрементный бэкап файлов
function show_differences($dir, $previous_dir){
	//Получаем глобальные переменные
	global $fetched_files;
	
	//Определяем переменные
	$html="";
	$new_html="";
	$deleted_html="";
	$different_html="";
	$new_number=0;
	$deleted_number=0;
	$different_number=0;
	
	//Определяем переменные
	$dir_files=$fetched_files[$dir]['all'];
	$previous_dir_files=$fetched_files[$previous_dir]['all'];
	
	/*Новые файлы*/
	foreach($dir_files as $name=>$version_id_file){
		if(!check_dir_exclusion($name)){	
			if(!isset($previous_dir_files[$name])){
				$new[$name]['md5']=$dir_files[$name]['md5'];
				$new_html.="<span style='color:green;'>".$name."</span><br/>";
				$new_number++;
			}
		}
	}
	
	/*Выводим заголовок*/	
	$html.="<h3>Новые файлы: $new_number</h3>".$new_html."<br/>";
	
	/*Удаленные файлы*/
	foreach($previous_dir_files as $name=>$previous_version_file){
		if(!check_dir_exclusion($name)){	
			if(!isset($dir_files[$name])){
				$deleted[$name]['md5']=$dir_files[$name]['md5'];
				$deleted_html.="<span style='color:red;'>".$name."</span><br/>";
				$deleted_number++;
			}
		}
	}
	
	/*Выводим заголовок*/	
	$html.="<h3>Удаленные файлы: $deleted_number</h3>".$deleted_html."<br/>";
	
	/*Измененные файлы*/
	foreach($previous_dir_files as $name=>$previous_version_file){
		if(!check_dir_exclusion($name)){
			if(isset($dir_files[$name]) && $dir_files[$name]['md5']!=$previous_version_file['md5']){
				$different[$name]['md5']=$dir_files[$name]['md5'];
				$different_html.="<span style='color:orange;'>".$name."</span><br/>";
				$different_number++;
			}
		}
	}
	
	/*Выводим заголовок*/	
	$html.="<h3>Изменилось содержимое: $different_number</h3>".$different_html."<br/>";
	
	//Возвращаем значение функции
	return $html;
}

//Проверяет, является ли директория исключением
function check_dir_exclusion($dir){
	//Получаем глобальные переменные
	global $direxclusions;
	
	//Определяем переменную
	$result=false;
	
	//Цикл
	foreach($direxclusions as $empty=>$exclusion){
		if(substr($dir, 0, strlen($exclusion))==$exclusion){
			$result=true;
		}
	}
	return $result;
}

/*Сохраняем данные по умолчанию*/
function save_default_data($dir, $previous_dir, $direxclusions){
	/*Директория*/
	if(db_easy_count("SELECT * FROM `compare_directories_defaults` WHERE `name`='dir'")==0){
		db_query("INSERT INTO `compare_directories_defaults` SET `name`='dir', value='$dir'");
	}else{
		db_query("UPDATE `compare_directories_defaults` SET value='$dir' WHERE `name`='dir'");
	}
	
	/*Исходная директория*/
	if(db_easy_count("SELECT * FROM `compare_directories_defaults` WHERE `name`='previous_dir'")==0){
		db_query("INSERT INTO `compare_directories_defaults` SET `name`='previous_dir', value='$previous_dir'");
	}else{
		db_query("UPDATE `compare_directories_defaults` SET value='$previous_dir' WHERE `name`='previous_dir'");
	}
	
	//Определяем переменную
	$i=1;
	
	/*Исключения*/
	foreach($direxclusions as $empty=>$direxclusion){
		if(db_easy_count("SELECT * FROM `compare_directories_defaults` WHERE `name`='direxclusion{$i}'")==0){
			db_query("INSERT INTO `compare_directories_defaults` SET `name`='direxclusion{$i}', value='$direxclusion'");
		}else{
			db_query("UPDATE `compare_directories_defaults` SET value='$direxclusion' WHERE `name`='direxclusion{$i}'");
		}
		
		/*Прибавляем счетчик*/
		$i++;
	}
}
?>