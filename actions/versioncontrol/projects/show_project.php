<?php
function show_project(){
	//�������� ���������� ����������
	global $project_id;
	global $project_first_version_id;

	//���������� ����������
	$html="";

	/*�������� �������������� �������*/
	if(isset($_GET['project'])){
		$project_id=$_GET['project'];
	}else{
		//���������� HTTP ���������
		header("location: /projects.php?project=".db_short_easy("SELECT `id` FROM `projects` ORDER BY `sort` ASC"));
		exit;
	}
	
	$project_name=db_short_easy("SELECT `name` FROM `projects` WHERE `id`=".$project_id);
	$project_first_version_id=db_short_easy("SELECT `first_version_id` FROM `projects` WHERE `id`=".$project_id);
	
	//���������� ������� ����
	$html.= versioncontrol_menu().project_menu();

	
	//��������� ��������
	$html.="<h1 style='margin:20px 0 10px 0;'>���� �� �������</h1>";

	/*��������� ������*/
	$html.=versions_structure()."<br/>";

	//���� - � �������
	return template_get('header').menu_top().$html.template_get('footer');

}

//------���� ��������������� ������� ��� ����� ��������------//

/*��������� ������*/
function versions_structure(){
	//�������� ���������� ����������
	global $project_id;
	global $project_first_version_id;

	/*������ �������*/
	$html="<b>������ �������</b><br/>";
	//IF
	if($project_first_version_id==0){
		$html.="<span style='color:red;'>�� ������ ������� ������</span><br/>";
	}else{
		$html.="<span style='color:green;'>������� ������ ������</span><br/>";
		if(!file_exists("/backups/full/projects/{$project_id}/versions/{$project_first_version_id}/")){
			$html.="<span style='color:red;'>������� ������ �� ����� ������ ��������� �����</span><br/>";
		}else{
			$html.="<span style='color:green;'>� ������� ������ ���� ������ ��������� �����</span><br/>";
		}
	}
	
	//���������� ����������
	$html.="<br/><b>����������� �����������</b><br/>
			������� - ������� ������<br/>
			��������� - ��� ������ ������ ���������� ���������� � ��� ����������<br/>
			����������� - ������ ��������� ����� ������ � ����<br/>
			����������������� - ������������ ��������� ����� ������ � ����<br/>
			<br/>";
	
	
	//������ � ����
	$versionsRES=db_query("SELECT * FROM `versions` WHERE `project_id`=$project_id ORDER BY `id` ASC");

	//���������� ����������
	$iWHILE=1;
	$current_version_id=-1;
	
	//IF
	if(db_count($versionsRES)>0){
		//����
		while($versionWHILE=db_fetch($versionsRES)){
			$previous_version_id=$current_version_id;
			$current_version_id=$versionWHILE['id'];
			
			//IF
			if($project_first_version_id==$versionWHILE['id']){
				$first_version='�������';
				$linked='';
				$html.="<br/><hr/><br/>";
			}else{
				//���������� ����������
				$first_version='';
				
				//IF
				if(($versionWHILE['previous_version_id']!=0 && db_easy_count("SELECT * FROM `versions` WHERE `id`=".$versionWHILE['previous_version_id'])>0) || $project_first_version_id==$versionWHILE['id']){
					$linked='���������';
				}else{
					$linked='';
				}
				
				//IF
				if($previous_version_id==$versionWHILE['previous_version_id']){
					$html.="<img src='/_content/img/link.gif' style='display:block;margin:3px 8px;'/>";
				}else{
					$html.="<img src='/_content/img/nolink.gif' style='display:block;margin:3px 8px;'/>";
				}
				
				//�������� ���������� � �������� �������� � ������ � ���� ������ � ���������� .versioncontrol
				$hidden_dir="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$current_version_id/.versioncontrol";
				if(file_exists($hidden_dir."/info") && file_exists($hidden_dir."/new_files") && file_exists($hidden_dir."/different_files") && file_exists($hidden_dir."/deleted_files")){
					$store_info_in_files="<b style='color:green;'>�������� � ������ ��������</b>";
				}else{
					$store_info_in_files="<b style='color:red;'>�������� � ������ �����������</b>";
				}

			}

			//IF	
			file_exists("/backups/full/projects/{$project_id}/versions/{$versionWHILE['id']}/files") ? $full_backup='����������������' : $full_backup='';

			//IF	
			file_exists("/backups/full/projects/{$project_id}/versions/{$versionWHILE['id']}/databases") ? $full_backup_bases='�������������' : $full_backup_bases='';
			
			//IF	
			file_exists("/backups/increment/projects/{$project_id}/versions/{$versionWHILE['previous_version_id']}-to-{$versionWHILE['id']}/") ? $increment_backup='�����������������' : $increment_backup='';
			
			//���������� ����������
			$html.="<a href='/versions.php?action=show_version&project={$project_id}&version=".$versionWHILE['id']."' style='font-size:11pt;padding-right:10px;'>".$versionWHILE['id']."</a>
					<span style='font-size:8pt;font-weight:bold;font-style:italic;'>$first_version $linked $full_backup $full_backup_bases $increment_backup $store_info_in_files</span><br/> ";
			
			//IF
			//if($iWHILE%40==0) $html.="<br/>";
			
			//���������� ����������
			//$iWHILE++;
		}
	}else{
		//���������� ����������
		$html.="� ������ ������� ��� ��� ������";
	}
	
	//���������� �������� �������
	return $html;
}
?>