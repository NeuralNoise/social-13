<?php
//������� ����� ������
function add_version(){
	//�������� ���������� ����������
	global $project_id;
	global $project_dir;
	
	//���������� ����������
	$project_id=$_GET['project'];
	
	//������ � ����
	$project_dir=db_short_easy("SELECT `dir` FROM `projects` WHERE `id`=$project_id");
	
	/*�������� id ���������� ������*/
	//������ � ����
	$previous_versionRES=db_query("SELECT * FROM `versions` WHERE `project_id`=$project_id ORDER BY `id` DESC LIMIT 1");
	if(db_count($previous_versionRES)>0){
		$previous_version_id=db_fetch($previous_versionRES)['id'];
	}else{
		$previous_version_id=0;
	}

	//������ � ����
	$q=db_query("INSERT INTO `versions` SET `project_id`=$project_id, `previous_version_id`=$previous_version_id, `date`='".date("Y-m-d H:i:s")."'");
	
	//�������� id ������, ����������� � ��
	$version_id=db_insert_id($q);
	
	/*�������� ������ ������, ��� ������ � �������, ���� ��� �������*/
	if(db_short_easy("SELECT `first_version_id` FROM `projects` WHERE `id`=$project_id")==0 && db_easy_count("SELECT * FROM `versions` WHERE `project_id`=$project_id AND `id`!=$version_id")==0){
		db_query("UPDATE `projects` SET `first_version_id`=$version_id WHERE `id`=$project_id");
		
		/*����������� �����*/
		copy_files($version_id);
	}
	
	/*��������� ���������� � ������ � ����*/
	insert_files($project_dir, $version_id);
	
	/*����������� ����*/
	save_databases($version_id);
	
	/*��������� �����, ������� ����������, � ����� ���� � ��� � ��*/
	save_file_diff($previous_version_id, $version_id);
	
	//���������� HTTP ���������
	header("location: /versions.php?action=show_version&project=$project_id&version=$version_id");
}

//------���� ��������������� ������� ��� ����� ��������------//

//������ ������ ��������� ����� ������
function copy_files($version_id){
	//������ � ����
	$project=db_easy("SELECT * FROM `projects` WHERE `id`=".db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id"));
	
	//��������� ����������
	$files_dir="/backups/full/projects/{$project['id']}/versions/$version_id/files";
	
	/*������� ���������� ��� ������ ������*/
	exec("mkdir -p ".$files_dir);
	
	/*�������� ����� � ��� ����������*/
	exec("cp -R ".$project['dir']."* ".$files_dir);
}

//����������� ���� ������
function save_databases($version_id){
	//������ � ����
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	//������ � ����
	$previous_version_id=db_short_easy("SELECT `previous_version_id` FROM `versions` WHERE `id`=$version_id");
	
	//���������� ����������
	$full_dir="/backups/full/projects/$project_id/versions/$version_id/databases";

	//���������� ����������
	$previous_full_dir="/backups/full/projects/$project_id/versions/$previous_version_id/databases";
	
	//������ � ����
	$q=db_query("SELECT * FROM `project_databases` WHERE `project_id`=$project_id");
	
	//����
	while($databaseWHILE=db_fetch($q)){
		if(trim($databaseWHILE['name'])!=""){
			/*������� ��������� ��, ������� ���������� ��� �������� ������������ �����*/
			if(file_exists($previous_full_dir."/".$databaseWHILE['name'].".sql")){
				db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$previous_version_id}`");
				db_query("CREATE DATABASE `z-{$databaseWHILE['name']}-{$previous_version_id}`");
				exec("mysql -uilya -plocal z-{$databaseWHILE['name']}-{$previous_version_id} < ".$previous_full_dir."/".$databaseWHILE['name'].".sql");
			}
		
			/*������������ ����� ��*/
			if($previous_version_id){
				$increment_dir="/backups/increment/projects/$project_id/versions/".$previous_version_id."-to-".$version_id."/databases";

				/*������� ����������*/
				exec("mkdir -p ".$increment_dir);
				
				/*������ ������ ����� �� � ������������ ����������*/
				exec("mysqldump -uilya -plocal {$databaseWHILE['name']} > ".$increment_dir."/".$databaseWHILE['name'].".sql");
				
				exec("schemasync --output-directory=$increment_dir mysql://ilya:local@localhost:3306/{$databaseWHILE['name']} mysql://ilya:local@localhost:3306/z-{$databaseWHILE['name']}-{$previous_version_id}");
				exec("mv $increment_dir/z-{$databaseWHILE['name']}-{$previous_version_id}.".date("Ymd").".patch.sql $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
				exec("mv $increment_dir/z-{$databaseWHILE['name']}-{$previous_version_id}.".date("Ymd").".revert.sql $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
				if(count(file("$increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql"))==9){
					exec("rm $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
					exec("rm $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
				}else{
					exec("sed 1,9d $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql > $increment_dir/sed_temp_file.txt");
					exec("cat $increment_dir/sed_temp_file.txt > $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
					exec("sed 1,9d $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql > $increment_dir/sed_temp_file.txt");
					exec("cat $increment_dir/sed_temp_file.txt > $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
					exec("rm $increment_dir/sed_temp_file.txt");
				}
				exec("rm $increment_dir/schemasync.log");
			}
			
			/*������� ��������� ��*/
			db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$previous_version_id}`");
			
			/*������ ����� ��*/
			exec("mkdir -p ".$full_dir);
			exec("mysqldump -uilya -plocal {$databaseWHILE['name']} > ".$full_dir."/".$databaseWHILE['name'].".sql");
		}
	}
}

//���������� ��������� ������
function save_file_diff($previous_version_id, $version_id){
	//�������� ���������� ����������
	global $fetched_files;
	global $project_id;
	
	//���������� ����������
	$hidden_dir="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/.versioncontrol";
	
	//������� ����������
	exec("mkdir -p $hidden_dir");
	
	//���������� � ����
	file_easy_write($hidden_dir."/info", "Version: $version_id\nPrevious version: $previous_version_id");
	
	//���������� ����������
	$files=versions_compare_with_files($previous_version_id, $version_id);
	
	//���������� ����������
	$file_text="";
	
	//����
	foreach($files['new'] as $name=>$file){
		$file_text.="";
		db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='new', `md5`='{$file['md5']}'");
		copy_diff_file($previous_version_id, $version_id, $name);
	}
	
	//����
	foreach($files['different'] as $name=>$file){
		db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='different', `md5`='{$file['md5']}'");
		copy_diff_file($previous_version_id, $version_id, $name);
	}

	//����
	foreach($files['deleted'] as $name=>$file){
		db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='deleted', `md5`='{$file['md5']}'");
		copy_diff_file($previous_version_id, $version_id, $name);
	}
}

//����������� ���� ����
function copy_diff_file($previous_version_id, $version_id, $file){
	//�������� ���������� ����������
	global $project_id;
	global $project_dir;
	
	//���������� ����������
	$version_dir=correct_path("/backups/increment/projects/".$project_id."/versions/".$previous_version_id."-to-".$version_id);
	$file_dir=$version_dir."files/".substr($file, 0, strlen($file)-strlen(end(explode('/', $file))));
	
	/*������� ���������� ��� ������ ������*/
	exec("mkdir -p ".$file_dir);
	exec("cp ".$project_dir.$file." ".$version_dir."files/".$file);
}

//���������� �������� ������ ���� ������ ����������, � ����� �� md5 �����.
//���� ������ ����, �� ���������� ��� �������� � ��� � ����
function insert_files($f, $version_id){
	//�������� ���������� ����������
	global $project_dir;
	global $fetched_files;
	
	if(is_dir($f) && !preg_match("/.*\.$/", $f)){
		$dp=opendir($f);
		while($f_new = readdir($dp)){
			if(is_dir($f) && !preg_match("/.*\.$/", $f)){
				$f=correct_path($f);
			}
			insert_files($f.$f_new, $version_id);
		}
	}elseif(!preg_match("/.*\.$/", $f)){
		$f_short=substr($f, strlen($project_dir));
		$md5=md5_file($f);
		db_query("INSERT INTO `files` SET `version_id`=$version_id, `name`='$f_short', `md5`='".$md5."'");
		$fetched_files[$version_id]['all'][$f_short]['md5']=$md5;
	}
}

//���������� ������
function versions_compare_with_files($previous_version_id, $version_id){
	//�������� ���������� ����������
	global $fetched_files;

	//���������� ����������
	$version_files=$fetched_files[$version_id]['all'];
	
	//���������� ����������
	$different=array();
	$new=array();
	$deleted=array();
	$previous_version_files=array();
	
	//������ � ����
	$previous_version_filesRES=db_query("SELECT * FROM `files` WHERE `version_id`=".$previous_version_id);
	
	//����
	while($r=db_fetch($previous_version_filesRES)){
		$previous_version_files[$r['name']]=$r;
	}
	
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