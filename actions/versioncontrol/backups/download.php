<?php
function download(){
	//IF
	if(isset($_GET['version'])){
		//IF
		if(preg_match("/^[0-9]{1,10}$/", $_GET['version'])){
			$version_id=$_GET['version'];
		//ELSE
		}else{
			return "������. ������������ ���� ���������������� ������ (����� ������).";
		}
	//ELSE
	}else{
		return "������. �� ������ ����� ������.";
	}
	
	//������ � ����
	$project_id=db_short_easy("SELECT `project_id` FROM `versions` WHERE `id`=$version_id");
	

	//IF
	if(@$_GET['mode']=='increment'){
		//������ � ����
		$previous_version_id=db_short_easy("SELECT `previous_version_id` FROM `versions` WHERE `id`=$version_id");
		
		//���������� ����������
		$file="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id.tgz";
		
		//���������� ����������
		$dir="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id";
		
		//���������� ����������
		$header_file="$previous_version_id-to-$version_id.tgz";
	}
		
	//IF
	if(@$_GET['mode']=='full'){
		//���������� ����������
		$file="/backups/full/projects/$project_id/versions/$version_id.tgz";
		
		//���������� ����������
		$dir="/backups/full/projects/$project_id/versions/$version_id";
		
		//���������� ����������
		$header_file="$version_id.tgz";
	}
		
	//��������� ������� � shell
	exec("tar -czf $file $dir");
		
	//���������� HTTP ���������
	header('Content-type:  application/zip');
	header('Content-Length: ' . filesize($file));
	header("Content-Disposition: attachment; filename='$header_file'");
		
	/*������ ���� ������������*/
	readfile($file);
		
	/*���� ���� ������������ ������� �������, ������ �������� ������*/
	ignore_user_abort(true);
	
	//������� ����
	unlink($file);
}
?>