<?php
//������� ������ � ��� �� �����
function delete_version(){
	//�������� ���������� ����������
	$version_id=$_GET['version'];
	
	//������ � ����
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	//���������� ����������
	$project_first_version_id=db_short_easy("SELECT `first_version_id` FROM `projects` WHERE `id`=$project_id");
	
	//������ � ����
	$database_name=db_short_easy("SELECT `name` FROM `project_databases` WHERE `project_id`=$project_id");
	
	/*������� ������� �����*/
	delete_files($version_id);
	
	/*� ������ ������ ������� ������ �� ����*/
	
	//������ � ����
	db_query("DELETE FROM `versions` WHERE `id`=$version_id");
	
	//������ � ����
	if($project_first_version_id==$version_id){
		db_query("UPDATE `projects` SET `first_version_id`=0 WHERE `id`=$project_id");
	}
	
	//������ � ����
	db_query("DELETE FROM `files` WHERE `version_id`=$version_id");
	
	//������ � ����
	db_query("DELETE FROM `filesdiff` WHERE `version_id`=$version_id");
	
	//������ � ����
	db_query("DROP DATABASE IF EXISTS `z-{$database_name}-$version_id");
	
	//���������� HTTP ���������
	header("location: /versions.php?action=show_version&project=$project_id");
}

//------���� ��������������� ������� ��� ����� ��������------//

//������� �����
function delete_files($version_id){
	//������ � ����
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	//������ � ����
	$previous_version_id=db_short_easy("SELECT `previous_version_id` FROM `versions` WHERE `id`=$version_id");
	
	//���������� �� ��������� ������
	exec("rm -R /backups/increment/projects/".$project_id."/versions/".$previous_version_id."-to-".$version_id);
	exec("rm -R /backups/full/projects/".$project_id."/versions/".$version_id);
}
?>