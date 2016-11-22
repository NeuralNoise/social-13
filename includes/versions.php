<?php
//���� ������
function versioncontrol_menu(){
	//�������� ���������� ����������
	global $project_id;
	
	//���������� ����������
	$html="";
	
	//������ � ����
	$q=db_query("SELECT * FROM `projects` ORDER BY `sort` ASC");
	
	//���������� ����������
	$phpself=substr($_SERVER['PHP_SELF'], 1);
	
	//����
	while($projectWHILE=db_fetch($q)){
		//IF
		if($projectWHILE['id']==$project_id){
			$class="no_underlined";
			$size=trim(explode("/", exec("du -h --summarize /backups/full/projects/$project_id/"))[0]);
			$size=str_replace("G", "��", str_replace("K", "��", str_replace("M", "��", $size)));
			$size="<span style='font-size:10pt;font-style:italic;'>({$size})</span>";
		//ElSE
		}else{
			$class="underlined";
			$size="";
		}
		
		//���������� ����������
		if(preg_match("/^show\_/", $_GET['action'])){
			$action=$_GET['action'];
		}else{
			$action='show_version';
		}
		$html.="<a href='/versions.php?action=$action&project=".$projectWHILE['id']."' class='$class'>".$projectWHILE['name'].$size."</a><span class='divider'></span>";
	}
	
	$html.="<a href='/versions.php?action=add_project' class='$class'><img src='/_content/img/add-icon.png' /></a><span class='divider'></span>";
	
	//���������� �������� �������
	return template_get('versioncontrol/menu_top', array(
															'items'=>"<br/>�������:<span class='divider'></span>$html",
															'project_id'=>$project_id));
}

//���� �������
function project_menu(){
	//�������� ���������� ����������
	global $project_id;

	//���������� ����������
	$html="";

	//���������� �������� �������
	return template_get('versioncontrol/project_menu', array(
															'project_id'=>$project_id));
}
?>