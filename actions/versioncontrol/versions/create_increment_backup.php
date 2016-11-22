<?php
//������� ������������ �������� ����� �� ���� ������ �������
function create_increment_backup(){
	//�������� ���������� ����������
	global $project_id;
	global $project_dir;
	global $fetched_files;

	//���������� ����������
	$version_id=$_GET['version'];
	$previous_version_id=$_GET['previous_version'];
	$html="";
	$different=array();
	$new=array();
	$deleted=array();
	
	//������ � ����
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	$project_dir=db_short_easy("SELECT `dir` FROM `projects` WHERE `id`=$project_id");
	
	/*���������, ��� ��� ������ ����������� ������ �������*/
	if($project_id!=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$previous_version_id")){
		return "������. ��� ������ ������ ������������ ������ �������.";
	}
	
	/*���������, ��� ���������� ������ �������� ������, �� ������� ����� ��������� ������������*/
	if(!file_exists("/backups/full/projects/$project_id/versions/$version_id/files/") || !file_exists("/backups/full/projects/$project_id/versions/$previous_version_id/files/")){
		return "������. ��� ����� ������ ������ ������������ ���������� � ������� ��������� ��������.";
	}
	
	/*�������� ������ version_files*/
	get_files_array("/backups/full/projects/$project_id/versions/$version_id/files/", "/backups/full/projects/$project_id/versions/$version_id/files/", $version_id);
	
	/*�������� ������ previous_version_files*/
	get_files_array("/backups/full/projects/$project_id/versions/$previous_version_id/files/", "/backups/full/projects/$project_id/versions/$previous_version_id/files/", $previous_version_id);
	
	/*������� ������������ ��������� ����� ������*/
	increment_file_backup($version_id, $previous_version_id);
	
	/*������� ������������ ��������� ����� ��� ������*/
	increment_database_backup($version_id, $previous_version_id);
	
	/*�������� ���������� ������ ��� $version_id*/
	db_query("UPDATE `versions` SET `previous_version_id`=$previous_version_id WHERE `id`=$version_id");
	
	//���������� HTTP ���������
	header("location: /versions.php?action=show_version&project=$project_id&version=$version_id");
}

//���������� �������� ������ ���� ������ ����������, � ����� �� md5 �����.
//�������� � ����� ������� � ���������� ������
function get_files_array($dir, $f, $version_id){
	//�������� ���������� ����������
	global $fetched_files;
	
	if(is_dir($f) && !preg_match("/.*\.$/", $f)){
		$dp=opendir($f);
		while($f_new = readdir($dp)){
			if(is_dir($f) && !preg_match("/.*\.$/", $f)){
				$f=correct_path($f);
			}
			get_files_array($dir, $f.$f_new, $version_id);
		}
	}elseif(!preg_match("/.*\.$/", $f)){
		$f_short=substr($f, strlen($dir));
		$md5=md5_file($f);
		$fetched_files[$version_id]['all'][$f_short]['md5']=$md5;
	}
}

//������������ ����� ������
function increment_file_backup($version_id, $previous_version_id){
	//�������� ���������� ����������
	global $project_id;
	global $fetched_files;
	
	//���������� ����������
	$version_files=$fetched_files[$version_id]['all'];
	$previous_version_files=$fetched_files[$previous_version_id]['all'];
	
	/*������� ����� ��� �������� ������������� ��������� ������*/
	exec("mkdir -p /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files");
	
	/*���������� �����*/
	foreach($previous_version_files as $name=>$previous_version_file){
		if(isset($version_files[$name]) && $version_files[$name]['md5']!=$previous_version_file['md5']){
			$different[$name]['md5']=$version_files[$name]['md5'];
			db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='different', `md5`='".$version_files[$name]['md5']."'");
			exec("mkdir -p /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files/".substr($name, 0, strlen($name)-strlen(end(explode('/', $name)))));
			exec("cp -R /backups/full/projects/$project_id/versions/$version_id/files/$name /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files/$name");
		}
	}
	
	/*����� �����*/
	foreach($version_files as $name=>$version_id_file){
		if(!isset($previous_version_files[$name])){
			$new[$name]['md5']=$version_files[$name]['md5'];
			db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='new', `md5`='".$version_files[$name]['md5']."'");
			exec("mkdir -p /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files/".substr($name, 0, strlen($name)-strlen(end(explode('/', $name)))));
			exec("cp -R /backups/full/projects/$project_id/versions/$version_id/files/$name /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files/$name");
		}
	}
	
	/*��������� �����*/
	foreach($previous_version_files as $name=>$previous_version_file){
		if(!isset($version_files[$name])){
			$deleted[$name]['md5']=$version_files[$name]['md5'];
			db_query("INSERT INTO `filesdiff` SET `previous_version_id`=$previous_version_id, `version_id`=$version_id, `name`='$name', `type`='deleted', `md5`=''");
		}
	}
}

//������������ ����� ���� ������
function increment_database_backup($version_id, $previous_version_id){
	//���������� ���������� ����������
	global $project_id;
	
	//���������� ����������
	$full_dir="/backups/full/projects/$project_id/versions/$version_id/databases";
	$previous_full_dir="/backups/full/projects/$project_id/versions/$previous_version_id/databases";
	$increment_dir="/backups/increment/projects/$project_id/versions/".$previous_version_id."-to-".$version_id."/databases";
	
	/*������� ���������� ��� ������������ ����� ���� ������*/
	exec("mkdir -p ".$increment_dir);
	
	//������ � ����
	$q=db_query("SELECT * FROM `project_databases` WHERE `project_id`=$project_id");

	//����
	while($databaseWHILE=db_fetch($q)){
		if(trim($databaseWHILE['name'])!=""){
			if(file_exists($previous_full_dir."/".$databaseWHILE['name'].".sql")){
				/*������� ��������� ��, ���� ������������*/
				db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$version_id}`");
				db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$previous_version_id}`");
				
				/*������� ��������� ��*/
				db_query("CREATE DATABASE `z-{$databaseWHILE['name']}-{$version_id}`");
				db_query("CREATE DATABASE `z-{$databaseWHILE['name']}-{$previous_version_id}`");
				
				/*�������� ������ ����� �� � ������ ��� ��������� ����*/
				exec("mysql -uilya -plocal z-{$databaseWHILE['name']}-{$version_id} < ".$full_dir."/".$databaseWHILE['name'].".sql");
				exec("mysql -uilya -plocal z-{$databaseWHILE['name']}-{$previous_version_id} < ".$previous_full_dir."/".$databaseWHILE['name'].".sql");
			}

			/*SchemaSync ������� ������ � �������� ����-������� ����� ������*/
			exec("schemasync --output-directory=$increment_dir mysql://ilya:local@localhost:3306/z-{$databaseWHILE['name']}-{$version_id} mysql://ilya:local@localhost:3306/z-{$databaseWHILE['name']}-{$previous_version_id}");
			
			/*��������������� �����*/
			exec("mv $increment_dir/z-{$databaseWHILE['name']}-{$previous_version_id}.".date("Ymd").".patch.sql $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
			exec("mv $increment_dir/z-{$databaseWHILE['name']}-{$previous_version_id}.".date("Ymd").".revert.sql $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
			
			//IF
			if(file_exists("$increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql")){
				if(count(file("$increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql"))==9){
					exec("rm $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
					exec("rm $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
				//ElSE
				}else{
					exec("sed 1,9d $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql > $increment_dir/sed_temp_file.txt");
					exec("cat $increment_dir/sed_temp_file.txt > $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.patch.sql");
					exec("sed 1,9d $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql > $increment_dir/sed_temp_file.txt");
					exec("cat $increment_dir/sed_temp_file.txt > $increment_dir/{$databaseWHILE['name']}.{$previous_version_id}-to-{$version_id}.revert.sql");
					exec("rm $increment_dir/sed_temp_file.txt");
				}
			}
			
			/*������� ���� ����� schemasync*/
			exec("rm $increment_dir/schemasync.log");
			
			/*������� ��������� ��*/
			db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$version_id}`");
			db_query("DROP DATABASE IF EXISTS `z-{$databaseWHILE['name']}-{$previous_version_id}`");
		}
	}
}
?>