<?php


//������� ������������ �������� ����� �� ���� ������ �������
function compare_directories(){
	//���������� ���������� ����������
	global $fetched_files;
	global $direxclusions;
	
	//���������� ����������
	$html="";
	
	/*���������� ������� ����*/
	$html.= versioncontrol_menu();

	/*������� ��������� ��������*/
	$html.="<h1 style='margin:20px 0 10px 0;'>�������� ����������</h1>";

	//IF
	if(isset($_POST['dir'])){
		/*��������� ������ �� ������������*/
		if(preg_match("/^[A-z0-9\_\-\.\/]{1,500}$/", $_POST['dir'])){
			$dir=$_POST['dir'];
		}else{
			return "������. ������������ ������ ������ (dir) �� ������������.";
		}

		/*��������� ������ �� ������������*/
		if(preg_match("/^[A-z0-9\_\-\.\/]{1,500}$/", $_POST['previous_dir'])){
			$previous_dir=$_POST['previous_dir'];
		}else{
			return "������. ������������ ������ ������ (previous_dir) �� ������������.";
		}
		
		//���������� ����������
		$different=array();
		$new=array();
		$deleted=array();
		
		/*��������� ������������ ����������*/
		if(!file_exists($dir)){
			return "������. ���������� (dir) �� ����������.";
		}

		/*��������� ������������ ����������*/
		if(!file_exists($previous_dir)){
			return "������. ���������� (previous_dir) �� ����������.";
		}
		
		/*������������ ����*/
		$dir=correct_path($dir);
		$previous_dir=correct_path($previous_dir);
		
		/*��������� ������ �� ������������*/
		if(is_array($_POST['direxclusions'])){
			$direxclusions=$_POST['direxclusions'];
		}
		
		/*������� "������" ����������-����������*/
		foreach($direxclusions as $empty=>$direxclusion){
			if(trim($direxclusion)=="") unset($direxclusions[$empty]);
		}
		
		/*��������� ������ �� ���������*/
		save_default_data($dir, $previous_dir, $direxclusions);
		
		/*������� ������ � �����������*/
		$html.="����������, ������� ����������: $dir<br/>";
		$html.="����������, � ������� ����������: $previous_dir<br/>";
		if(is_array($direxclusions)){
			foreach($direxclusions as $empty=>$direxclusion){
				$html.="����������-���������� �{$empty}: {$direxclusion}<br/>";
			}
		}
		$html.="<br/>";
		
		/*�������� ������ version_files*/
		get_files_array($dir, $dir);
		
		/*�������� ������ previous_version_files*/
		get_files_array($previous_dir, $previous_dir);
		
		/*������� ������������ ��������� ����� ������*/
		$html.=show_differences($dir, $previous_dir);
	//ELSE
	}else{
		//���������� ����������
		$default_direxclusions_html="";
		
		//������� � ����
		$default_dir=db_short_easy("SELECT `value` FROM `compare_directories_defaults` WHERE `name`='dir'");
		$default_previous_dir=db_short_easy("SELECT `value` FROM `compare_directories_defaults` WHERE `name`='previous_dir'");
		
		//����
		for($i=1;$i<=5;$i++){
			$default_direxclusionRES=db_query("SELECT `value` FROM `compare_directories_defaults` WHERE `name`='direxclusion{$i}'");
			db_count($default_direxclusionRES)>0 ? $default_direxclusion=db_fetch($default_direxclusionRES)['value'] : $default_direxclusion="";
			$default_direxclusions_html.="<tr><td>����������-���������� �{$i}:</td><td><input type='text' name='direxclusions[{$i}]' value='$default_direxclusion' style='width:300px;' /></td></tr>";
		}
		
		/*���������� ������*/
		$html.=template_get("versioncontrol/compare_directories", array('default_dir'=>$default_dir, 'default_previous_dir'=>$default_previous_dir, 'default_direxclusions'=>$default_direxclusions_html));
	}
	
	//���������� �������� �������
	return template_get('header').menu_top().$html.template_get('footer');
}

//���������� �������� ������ ���� ������ ����������, � ����� �� md5 �����.
//�������� � ����� ������� � ���������� ������
function get_files_array($dir, $f){
	//�������� ���������� ����������
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

//������������ ����� ������
function show_differences($dir, $previous_dir){
	//�������� ���������� ����������
	global $fetched_files;
	
	//���������� ����������
	$html="";
	$new_html="";
	$deleted_html="";
	$different_html="";
	$new_number=0;
	$deleted_number=0;
	$different_number=0;
	
	//���������� ����������
	$dir_files=$fetched_files[$dir]['all'];
	$previous_dir_files=$fetched_files[$previous_dir]['all'];
	
	/*����� �����*/
	foreach($dir_files as $name=>$version_id_file){
		if(!check_dir_exclusion($name)){	
			if(!isset($previous_dir_files[$name])){
				$new[$name]['md5']=$dir_files[$name]['md5'];
				$new_html.="<span style='color:green;'>".$name."</span><br/>";
				$new_number++;
			}
		}
	}
	
	/*������� ���������*/	
	$html.="<h3>����� �����: $new_number</h3>".$new_html."<br/>";
	
	/*��������� �����*/
	foreach($previous_dir_files as $name=>$previous_version_file){
		if(!check_dir_exclusion($name)){	
			if(!isset($dir_files[$name])){
				$deleted[$name]['md5']=$dir_files[$name]['md5'];
				$deleted_html.="<span style='color:red;'>".$name."</span><br/>";
				$deleted_number++;
			}
		}
	}
	
	/*������� ���������*/	
	$html.="<h3>��������� �����: $deleted_number</h3>".$deleted_html."<br/>";
	
	/*���������� �����*/
	foreach($previous_dir_files as $name=>$previous_version_file){
		if(!check_dir_exclusion($name)){
			if(isset($dir_files[$name]) && $dir_files[$name]['md5']!=$previous_version_file['md5']){
				$different[$name]['md5']=$dir_files[$name]['md5'];
				$different_html.="<span style='color:orange;'>".$name."</span><br/>";
				$different_number++;
			}
		}
	}
	
	/*������� ���������*/	
	$html.="<h3>���������� ����������: $different_number</h3>".$different_html."<br/>";
	
	//���������� �������� �������
	return $html;
}

//���������, �������� �� ���������� �����������
function check_dir_exclusion($dir){
	//�������� ���������� ����������
	global $direxclusions;
	
	//���������� ����������
	$result=false;
	
	//����
	foreach($direxclusions as $empty=>$exclusion){
		if(substr($dir, 0, strlen($exclusion))==$exclusion){
			$result=true;
		}
	}
	return $result;
}

/*��������� ������ �� ���������*/
function save_default_data($dir, $previous_dir, $direxclusions){
	/*����������*/
	if(db_easy_count("SELECT * FROM `compare_directories_defaults` WHERE `name`='dir'")==0){
		db_query("INSERT INTO `compare_directories_defaults` SET `name`='dir', value='$dir'");
	}else{
		db_query("UPDATE `compare_directories_defaults` SET value='$dir' WHERE `name`='dir'");
	}
	
	/*�������� ����������*/
	if(db_easy_count("SELECT * FROM `compare_directories_defaults` WHERE `name`='previous_dir'")==0){
		db_query("INSERT INTO `compare_directories_defaults` SET `name`='previous_dir', value='$previous_dir'");
	}else{
		db_query("UPDATE `compare_directories_defaults` SET value='$previous_dir' WHERE `name`='previous_dir'");
	}
	
	//���������� ����������
	$i=1;
	
	/*����������*/
	foreach($direxclusions as $empty=>$direxclusion){
		if(db_easy_count("SELECT * FROM `compare_directories_defaults` WHERE `name`='direxclusion{$i}'")==0){
			db_query("INSERT INTO `compare_directories_defaults` SET `name`='direxclusion{$i}', value='$direxclusion'");
		}else{
			db_query("UPDATE `compare_directories_defaults` SET value='$direxclusion' WHERE `name`='direxclusion{$i}'");
		}
		
		/*���������� �������*/
		$i++;
	}
}
?>