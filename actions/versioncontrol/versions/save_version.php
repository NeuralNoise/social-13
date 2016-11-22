<?php
//��������� ������
function save_version(){
	//���������� ����������
	$version_id=(int)$_GET['version'];
	
	//������ � ����
	$previous_version_id=db_short_easy("SELECT `previous_version_id` FROM `versions` WHERE `id`=$version_id");
	
	//������ � ����
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	//���������� ����������
	$hidden_dir="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/.versioncontrol";

	//������� ����������
	exec("mkdir -p $hidden_dir");
	
	//IF
	if(isset($_POST['comment'])){
		//������ � ����
		$q=db_query("UPDATE `versions` SET `comment`='".$_POST['comment']."' WHERE `id`=".$version_id);
		$comment_text="\r\nComment:\r\n".$_POST['comment'];
	}else{
		$comment_text="";
	}
	
	
	//������: ����� ������ ������ ������ �� �� � ���������� � ����
	//if(db_short_easy("SELECT `in_files` FROM `versions` WHERE `id`=$version_id")==1){
		//���� ������ � ���� ������� �������� �� �� (������� `files`
		$files=versions_compare_before_save($previous_version_id, $version_id);
		
		//show($files);
		
		foreach(array('new', 'different', 'deleted') as $empty=>$type){
			//���������� ����������
			$files_text="";
			
			//FOREACH
			foreach($files[$type] as $name=>$file){
				$files_text.="$name:{$file['md5']}\r\n";
			}
			
			//���������� � ����
			file_easy_write($hidden_dir."/{$type}_files", $files_text);
		}
	//}
	//�����: ����� ������ ������ ������ �� �� � ���������� � ����
	
	//������ � ����
	db_query("UPDATE `versions` SET `in_files`=1 WHERE `id`=$version_id");
	
	//���������� ����������
	$file_text="Version: $version_id\r\nPrevious version: $previous_version_id$comment_text\r\n";
	
	//���������� � ����
	file_easy_write($hidden_dir."/info", $file_text);

	//���������� HTTP ���������
	header("location: /versions.php?action=show_version&project=$project_id&version=$version_id");
}

//���������� ������
function versions_compare_before_save($previous_version_id, $version_id){
	//���������� ����������
	$different=array();
	$new=array();
	$deleted=array();
	$version_files=array();
	$previous_version_files=array();
	
	/*������: �������� ������ ������ ������� ������*/
	//������ � ����
	$version_filesRES=db_query("SELECT * FROM `files` WHERE `version_id`=".$version_id);
	
	//����
	while($r=db_fetch($version_filesRES)){
		$version_files[$r['name']]['md5']=$r['md5'];
	}
	/*�����: �������� ������ ������ ������� ������*/
	
	
	/*������: �������� ������ ������ ���������� ������*/
	//������ � ����
	$previous_version_filesRES=db_query("SELECT * FROM `files` WHERE `version_id`=".$previous_version_id);
	
	//����
	while($r=db_fetch($previous_version_filesRES)){
		$previous_version_files[$r['name']]=$r;
	}
	/*�����: �������� ������ ������ ���������� ������*/
	
	//����
	foreach($previous_version_files as $name=>$previous_version_file){
		if(isset($version_files[$name]) && $version_files[$name]['md5']!=$previous_version_file['md5']){
			$different[$name]['md5']=$version_files[$name]['md5'];
		}
	}
	
	//����
	foreach($version_files as $name=>$version_id_file){
		if(!isset($previous_version_files[$name])){
			$new[$name]['md5']=$version_files[$name]['md5'];
		}
	}
	
	//����
	foreach($previous_version_files as $name=>$previous_version_file){
		if(!isset($version_files[$name])){
			$deleted[$name]['md5']=$version_files[$name]['md5'];
		}
	}
	
	//���������� �������� �������
	return array('different'=>$different, 'new'=>$new, 'deleted'=>$deleted);
}
?>