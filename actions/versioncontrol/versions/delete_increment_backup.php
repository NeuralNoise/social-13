<?php
//������� ������������ �����
function delete_increment_backup(){
	//���������� ����������
	$version_id=$_GET['version'];
	$previous_version_id=$_GET['previous_version'];
	
	/*�������� id �������*/
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	
	/*������� ���������� � ������������ ��������� ������*/
	exec("rm -R /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id");
	
	/*������� ������ �� ������������ ��������� ����� �� ���� ������*/
	db_query("DELETE FROM `filesdiff` WHERE `version_id`=$version_id AND `previous_version_id`=$previous_version_id");
	
	/*�������� ���������� ������ ��� $version_id ������ ����*/
	db_query("UPDATE `versions` SET `previous_version_id`=0 WHERE `id`=$version_id");
	
	header("location: /versions.php?action=show_version&project=$project_id&version=$version_id");
}
?>