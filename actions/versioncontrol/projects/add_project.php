<?php
function add_project(){
	//���������� ����������
	$html="";
	
	//���������� ������� ����
	$html.= versioncontrol_menu();

	//��������� ��������
	$html.="<h1 style='margin:20px 0 10px 0;'>�������� ������</h1>";
	
	if(isset($_POST['name'])){
		$project_name=$_POST['name'];
	}else{
		$html.=template_get('versioncontrol/projects/add_project', array());
	}
	
	//���������� �������� �������
	return template_get('header').menu_top().$html.template_get('footer');
}
?>