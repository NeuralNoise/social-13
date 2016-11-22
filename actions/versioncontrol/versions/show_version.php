<?php
function show_version(){
	//�������� ���������� ����������
	global $project_id;
	global $project;
	global $project_name;
	global $project_dir;
	global $project_backup_dir;
	global $version_id;
	global $version_date;
	global $version_comment;
	global $version_dir;
	global $previous_version_id;
	global $previous_version_date;
	global $chooseprevious;
	
	//���������� ����������
	$html="";
	
	/*�������� �������������� �������*/
	if(isset($_GET['project'])){
		$project_id=$project['id']=(int)$_GET['project'];
	}else{
		//���������� HTTP ���������
		header("location: /versions.php?action=show_version&project=".db_short_easy("SELECT `id` FROM `projects` ORDER BY `sort` ASC"));
		exit;
	}
	$project_name=$project['name']=db_short_easy("SELECT `name` FROM `projects` WHERE `id`=".$project['id']);
	$project_dir=correct_path(db_short_easy("SELECT `dir` FROM `projects` WHERE `id`=".$project['id']));
	$project_backup_dir=correct_path(db_short_easy("SELECT `backup_dir` FROM `projects` WHERE `id`=".$project['id']));	

	//�������� id ������
	if(isset($_GET['version'])){
		$version_id=$_GET['version'];
	}else{
		$versionRES=db_query("SELECT * FROM `versions` WHERE `project_id`=".$project['id']." ORDER BY `date` DESC");
		if(db_count($versionRES)>0){
			$version_id=db_fetch($versionRES)['id'];
		}else{
			$version_id=false;
		}
	}

	//�������� ����, ���������� � ����������� ������
	if($version_id){
		$version_date=db_short_easy("SELECT `date` FROM `versions` WHERE `id`=".$version_id);
		$version_comment=db_short_easy("SELECT `comment` FROM `versions` WHERE `id`=".$version_id);
		$version_dir=correct_path($project_backup_dir."projects/".$project['id']."/versions/".$previous_version_id."-to-".$version_id);

		//����������, ������� �� ����� ������ ���������� ������
		if(@$_GET['chooseprevious']=="on"){
			$chooseprevious=true;
		}else{
			$chooseprevious=false;
		}

		//�������� id ���������� ������
		if(isset($_GET['previous_version']) && $chooseprevious===true){
			$previous_version_id=$_GET['previous_version'];
		}else{
			$q=db_query("SELECT `id` FROM `versions` WHERE `date`<'".$version_date."' AND `project_id`=".$project['id']." ORDER BY `date` DESC LIMIT 1");
			if(db_count($q)>0){
				$previous_version_id=db_fetch($q)['id'];
			}else{
				$previous_version_id=false;
			}
		}

		//�������� ���� ���������� ������
		if($previous_version_id!==false){
			$previous_version_date=db_short_easy("SELECT `date` FROM `versions` WHERE `id`=".$previous_version_id);
		}

		//�������������� ���������� ������ ���������� ������, ���� �� ���� ����� ��� ������ ���� ������� ������
		if($previous_version!==false){
			if($previous_version_date>=$version_date){
				//����� ������� ���� ��������������� ���
			}
		}

	}

	//���������� ������� ����
	$html.= versioncontrol_menu().project_menu();

	//��������� ��������
	$html.="<h1 style='margin:20px 0 10px 0;'>��������� ������</h1>";

	if($version_id){
		/*���������� ������ ������*/
		$html.=filter_versions();
		
		/*���������� ������ � ����������*/
		$html.=compare_version();
	}

	if(!$version_id){
		$html.="� ������ ������� ��� ��� ������<br/>";
		$html.="<a href='/versions.php?action=add_version&project=".$project['id']."'>�������� ������ ������</a>";
	}

	//���� - � �������
	return template_get('header').menu_top().$html.template_get('footer');
}


//------���� ��������������� ������� ��� ����� ��������------//

//���������� � ������ �������� �����
function full_file_copy_info(){
	//���������� ����������
	$html="";
	global $version_id;
	global $project_id;
	global $project_name;
	
	if(file_exists("/backups/full/projects/$project_id/versions/$version_id/files")){
		$full_copy_size=trim(explode("/", exec("du -h --summarize /backups/full/projects/$project_id/versions/$version_id/files"))[0]);
		$full_copy_size=str_replace("K", "��", str_replace("M", "��", $full_copy_size));
		$full_copy_exists="<span style='color:green;'>����</span> <span style='font-size:8pt;font-style:italic;'>(".$full_copy_size.")</span>";
	}else{
		$full_copy_exists="<span style='color:red;'>���</span>";
	}
	
	$html.="��������: ".$full_copy_exists;
	
	
	//���������� �������� �������
	return $html;
}

//���������� �� ������������ �������� �����
function increment_file_copy_info(){
	//�������� ���������� ����������
	global $version_id;
	global $previous_version_id;
	global $project_id;
	global $project_name;

	//���������� ����������
	$html="";
	
	if(file_exists("/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files")){
		$increment_copy_size=trim(explode("/", exec("du -h --summarize /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/files"))[0]);
		$increment_copy_size=str_replace("K", "��", str_replace("M", "��", $increment_copy_size));
		$increment_copy_exists="<span style='color:green;'>����</span> <span style='font-size:8pt;font-style:italic;'>(".$increment_copy_size.")</span>";
	}else{
		$increment_copy_exists="<span style='color:red;'>���</span> (<a href='/versions.php?action=create_increment_backup&previous_version=$previous_version_id&version=$version_id'>�������</a>)";
	}
	
	$html.="��������: ".$increment_copy_exists;
	
	//���������� �������� �������
	return $html;
}

//��������� ������ ������ ��������� �����
function get_full_copy_size(){
	//���������� ����������
	global $version_id;
	global $project_id;
	
	//IF
	if(file_exists("/backups/full/projects/$project_id/versions/$version_id/")){
		$full_copy_size=trim(explode("/", exec("du -h --summarize /backups/full/projects/$project_id/versions/$version_id/"))[0]);
		$full_copy_size=str_replace("K", "��", str_replace("M", "��", $full_copy_size));
		return $full_copy_size;
	}else{
		return false;
	}
}

//��������� ������ ������ ��������� �����
function get_increment_copy_size(){
	//���������� ����������
	global $version_id;
	global $previous_version_id;	
	global $project_id;
	
	//IF
	if(file_exists("/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/")){
		$increment_copy_size=trim(explode("/", exec("du -h --summarize /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/"))[0]);
		$increment_copy_size=str_replace("K", "��", str_replace("M", "��", $increment_copy_size));
		return $increment_copy_size;
	}else{
		return false;
	}
}


//���������� � ������ ����� ���
function full_databases_copy_info(){
	//���������� ����������
	global $version_id;
	global $project_id;
	global $project_name;
	
	//���������� ����������
	$html.="���� ������: ";
	
	//������ � ��
	$dbsRES=db_query("SELECT * FROM `project_databases` WHERE `project_id`=$project_id");
	
	//IF
	if(db_count($dbsRES)>0){
		//���������� ����������
		$iWHILE=0;
		
		//����
		while($db=db_fetch($dbsRES)){
			//IF
			if(file_exists("/backups/full/projects/$project_id/versions/$version_id/databases/{$db['name']}.sql")){
				//���������� ����������
				$full_copy_size=trim(explode("/", exec("du -h --summarize /backups/full/projects/$project_id/versions/$version_id/databases/{$db['name']}.sql"))[0]);
				$full_copy_size=str_replace("K", "��", str_replace("M", "��", $full_copy_size));
				$full_copy_exists="<span style='color:green;'>����</span> <span style='font-size:8pt;font-style:italic;'>(".$full_copy_size.")</span>";
			//ELSE
			}else{
				//���������� ����������
				$full_copy_exists="<span style='color:red;'>���</span>";
			}
			
			//���������� ����������
			$html.="<b>".$db['name']."</b> $full_copy_exists";
			
			//IF
			if($iWHILE<db_count($dbsRES)-1) $html.=" | ";
			
			//���������� ����������
			$iWHILE++;
		}
	//ELSE
	}else{
		//���������� ����������
		$html.="��� ��� � �������";
	}
	
	//���������� �������� �������
	return $html;
}

//���������� � ������ ����� ���
function increment_databases_copy_info(){
	//���������� ����������
	global $version_id;
	global $previous_version_id;
	global $project_id;
	global $project_name;
	
	//���������� ����������
	$html.="���� ������: ";
	
	//������ � ��
	$dbsRES=db_query("SELECT * FROM `project_databases` WHERE `project_id`=$project_id");
	
	//IF
	if(db_count($dbsRES)>0){
		//���������� ����������
		$iWHILE=0;
		
		//����
		while($db=db_fetch($dbsRES)){
			//IF
			if(file_exists("/backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/databases/{$db['name']}.$previous_version_id-to-$version_id.patch.sql")){
				//���������� ����������
				$increment_copy_size=trim(explode("/", exec("du -h --summarize /backups/increment/projects/$project_id/versions/$previous_version_id-to-$version_id/databases/{$db['name']}.$previous_version_id-to-$version_id.patch.sql"))[0]);
				$increment_copy_size=str_replace("K", "��", str_replace("M", "��", $increment_copy_size));
				$full_copy_exists="<span style='color:green;'>����</span> <span style='font-size:8pt;font-style:italic;'>(".$increment_copy_size.")</span>";
			//ELSE
			}else{
				//���������� ����������
				$full_copy_exists="<span style='color:red;'>���</span>";
			}
			
			//���������� ����������
			$html.="<b>".$db['name']."</b> $full_copy_exists";
			
			//IF
			if($iWHILE<db_count($dbsRES)-1) $html.=" | ";
			
			//���������� ����������
			$iWHILE++;
		}
	//ELSE
	}else{
		//���������� ����������
		$html.="��� ��� � �������";
	}
	
	//���������� �������� �������
	return $html;
}

//���������� ������
function compare_version(){
	//���������� ����������
	$html="";
	
	//�������� ���������� ����������
	global $version_id;
	global $version_comment;
	global $previous_version_id;
	global $project_id;
	
	/*�������� ������ ������ ��������� �����*/
	if($full_copy_size=get_full_copy_size()){
		$full_copy_size="<span style='font-style:italic;'>(".$full_copy_size.")</span>";
	}else{
		$full_copy_size="";
	}
	
	/*�������� ������ ������������ ��������� �����*/	
	if($increment_copy_size=get_increment_copy_size()){
		$increment_copy_size="<span style='font-style:italic;'>(".$increment_copy_size.")</span>";
	}else{
		$increment_copy_size="";
	}
	
	//�������� ���������� � �������� �������� � ������ � ���� ������ � ���������� .versioncontrol
	if(stored_in_files($project_id, $version_id, $previous_version_id)){
		$store_info_in_files="<b style='color:green;'>�������� � ������ ��������</b>";
	}else{
		$store_info_in_files="<b style='color:red;'>�������� � ������ �����������</b>";
	}
	
	$html.="<form method='post' action='/versions.php?action=save_version&version=$version_id'>
				<div style='float:left;padding:30px 10px 0 0;'>	�����������:</div>
				<div style='float:left;'><textarea name='comment' style='width:600px;height:150px;'>".$version_comment."</textarea></div>
				<div class='clr' style='height:10px;'></div>".
				$store_info_in_files."<br/><br/>".
				"<b>������ ��������� �����</b> $full_copy_size<br/>".
				full_file_copy_info()."<br/>".
				full_databases_copy_info()."<br/>".
				"<a href='/versions.php?action=download&mode=full&version=$version_id'>���������</a>".
				"<br/>"."<br/>".
				"<b>������������ ��������� �����</b> $increment_copy_size
					<a href='/versions.php?action=delete_increment_backup&version=$version_id&previous_version=$previous_version_id'><img src='/_content/img/remove-icon.png' style='padding-left:10px;' onClick=\"if(!confirm('�������?')) return false;\"/></a><br/>".
				increment_file_copy_info()."<br/>".
				increment_databases_copy_info()."<br/>".
				"<a href='/versions.php?action=download&mode=increment&version=$version_id'>���������</a>".
				"<div style='height:20px;'></div>";	
	if($previous_version_id!==false){
		$html.=versions_compare('new', $previous_version_id, $version_id, 'html', 'green');
		$html.="<br/>";
		$html.=versions_compare('deleted', $previous_version_id, $version_id, 'html', 'red');
		$html.="<br/>";
		$html.=versions_compare('different', $previous_version_id, $version_id, 'html', 'orange');
	}else{
		$html.="<h3>������� ������, ������ ������: $counter</h3>";
		$q=db_query("SELECT * FROM `files` WHERE `version_id`=".$version_id);

		while($r1=db_fetch($q)){
			$html.="<span style='color:black;'>".$r1['name']."</span><br/>";
		}
	}
	$html.="<br/><input type='submit' value='���������'/></form>";
	
	//���������� �������� �������
	return $html;
}

//����������, �������� �� �������� ���������� � ������ � ������
function stored_in_files($project_id, $current_version_id, $previous_version_id){
	//�������� ������ ���� � ����������
	$hidden_dir="/backups/increment/projects/$project_id/versions/$previous_version_id-to-$current_version_id/.versioncontrol";
	
	//��������� ���� ��������
	if(file_exists($hidden_dir."/info") && file_exists($hidden_dir."/new_files") && file_exists($hidden_dir."/different_files") && file_exists($hidden_dir."/deleted_files")){
		return true;
	}else{
		return false;
	}
}

//���������� ������
function versions_compare($type, $previous_version_id, $current_version_id, $mode="html", $color){
	//���������� ���������� ����������
	global $project_id;
	
	$body="";$counter=0;
	$previous_version_id_files=array();$current_version_id_files=array();
	$same=array();$different=array();$new=array();$deleted=array();
	
	if(stored_in_files($project_id, $current_version_id, $previous_version_id)){
		$list_from_file=get_list_from_file("/backups/increment/projects/$project_id/versions/{$previous_version_id}-to-{$current_version_id}/.versioncontrol/".$type."_files", 0, $color);
		$counter=$list_from_file['counter'];
		$body=$list_from_file['body'];
	}else{
		switch($type){
			case "different":
				$q=db_query("SELECT * FROM `files` WHERE `version_id`=".$previous_version_id);
				while($r=db_fetch($q)){
					$previous_version_id_files[$r['name']]=$r;
				}
				
				$q=db_query("SELECT * FROM `files` WHERE `version_id`=".$current_version_id);
				while($r=db_fetch($q)){
					$current_version_id_files[$r['name']]=$r;
				}
			
				foreach($previous_version_id_files as $name=>$previous_version_id_file){
					if(isset($current_version_id_files[$name]) && $current_version_id_files[$name]['md5']!=$previous_version_id_file['md5']){
						$different[]=$name;
						
						if(!check_dir_exclusion($name)){ //���������� ����������
							$counter++;
							$body.="<span style='color:orange;'>".$name."</span><br/>";
						}
					}
				}	
			break;
			case "new":
				$q=db_query("SELECT * FROM `files` WHERE `version_id`=".$previous_version_id);
				while($r=db_fetch($q)){
					$previous_version_id_files[$r['name']]=$r;
				}
				$q=db_query("SELECT * FROM `files` WHERE `version_id`=".$current_version_id);
				while($r=db_fetch($q)){
					$current_version_id_files[$r['name']]=$r;
				}
				
				foreach($current_version_id_files as $name=>$current_version_id_file){
					if(!isset($previous_version_id_files[$name])){
						$new[]=$name;
						if(!check_dir_exclusion($name)){ //���������� ����������
							$counter++;
							$body.="<span style='color:lightgreen;'>".$name."</span><br/>";
						}
					}
				}		
			break;
			case "deleted":
				$q=db_query("SELECT * FROM `files` WHERE `version_id`=".$previous_version_id);
				while($r=db_fetch($q)){
					$previous_version_id_files[$r['name']]=$r;
				}
				$q=db_query("SELECT * FROM `files` WHERE `version_id`=".$current_version_id);
				while($r=db_fetch($q)){
					$current_version_id_files[$r['name']]=$r;
				}
				
				foreach($previous_version_id_files as $name=>$previous_version_id_file){
					if(!isset($current_version_id_files[$name])){
						$deleted[]=$name;
						
						if(!check_dir_exclusion($name)){ //���������� ����������
							$counter++;
							$body.="<span style='color:red;'>".$name."</span><br/>";
						}
					}
				}
			break;
		}
	}	
	switch($type){
		case "different":
			$html.="<h3>���������� ����������: $counter</h3>";
		break;
		case "new":
			$html.="<h3>����� �����: $counter</h3>";
		break;
		case "deleted":
			$html.="<h3>��������� �����: $counter</h3>";
		break;
	}
	
	$html.=$body;
	
	//���������� �������� �������
	switch($mode){
		case 'html':
			return $html;
		break;
		case 'array':
			return array('same'=>$same, 'different'=>$different, 'new'=>$new, 'deleted'=>$deleted);
		break;
	}
}

//�������� ������ �� ���������� �����, ��� � ������ ������� ��������� �����-���� ������
function get_list_from_file($file, $col, $color){
	$body="<div style='color:$color'>";
	$counter=0;
	if($fp=fopen($file, "r")){
		while (($string = fgets($fp, 4096)) !== false) {
			$line=explode(":", $string);
			if(!check_dir_exclusion($line[$col])){
				$body.=$line[$col]."<br/>";
				$counter++;
			}
		}
		if (!feof($fp)) {
			echo "Error: unexpected fgets() fail\n";
		}
		fclose($fp);
	}
	$body.="</div>";
	return array('body'=>$body, 'counter'=>$counter);
}
		

//������ ������
function filter_versions(){
	//�������� ���������� ����������
	$project_id=$GLOBALS['project']['id'];
	$project_name=$GLOBALS['project']['name'];
	$version_id=$GLOBALS['version_id'];
	$version_date=$GLOBALS['version_date'];
	$previous_version_id=$GLOBALS['previous_version_id'];
	$chooseprevious=$GLOBALS['chooseprevious'];
	
	//���������� ����������
	$options_current="";
	$options_previous="";

	//������ � ����
	$q0=db_query("SELECT	*
						FROM `versions`
						WHERE `project_id`=$project_id
						ORDER BY `date` DESC
						
				");
				
	//������ � ����
	$q1=db_query("SELECT	*
						FROM `versions`
						WHERE `project_id`=$project_id
						ORDER BY `date` DESC
						
				");
				
	//���� (������ ������ � ���� "�������")
	while($versionWHILE=db_fetch($q0)){
		$selected="";
		if($versionWHILE['id']==$version_id) $selected="selected";
		$options_current.="<option value='".$versionWHILE['id']."' $selected>".$versionWHILE['id']." / ".date("d.m.Y", strtotime($versionWHILE['date']))." / ".date("H:i", strtotime($versionWHILE['date']))."</option>";
	}
	
	//���� (������ ������ � ���� "����������")
	while($versionWHILE=db_fetch($q1)){
		$selected="";
		if($versionWHILE['id']==$previous_version_id) $selected="selected";
		$options_previous.="<option value='".$versionWHILE['id']."' $selected>".$versionWHILE['id']." / ".date("d.m.Y", strtotime($versionWHILE['date']))." / ".date("H:i", strtotime($versionWHILE['date']))."</option>";
	}

	$hiddens=make_hiddens_from_uri("/versions.php?action=show_version&project=$project_id");
	if($chooseprevious){
		$previous_version_name='previous_version';
		$chooseprevious_checkbox="checked";
		$chooseprevious_disabled='';
	}else{
		$previous_version_name='';
		$chooseprevious_checkbox="";
		$chooseprevious_disabled='disabled';
	}
	
	return template_get('versioncontrol/choose_version', array(
													'options_current'=>$options_current,
													'options_previous'=>$options_previous,
													'hiddens'=>$hiddens,
													'uri'=>"/versions.php",
													'add'=>"/versions.php?action=add_version&project=$project_id",
													'delete'=>"/versions.php?action=delete_version&version=$version_id",
													'download'=>"/versions.php?action=download&version=$version_id",
													'chooseprevious_checkbox'=>$chooseprevious_checkbox,
													'previous_version_name'=>$previous_version_name,
													'chooseprevious_disabled'=>$chooseprevious_disabled
												));

}

//���������, �������� �� ���������� �����������
function check_dir_exclusion($name){
	//�������� ���������� ����������
	$project_id=$GLOBALS['project']['id'];
	
	//������ � ����
	$q=db_query("SELECT * FROM `direxclusions` WHERE `project_id`=$project_id");
	
	//���������� ����������
	$result=false;
	
	//����
	while($exclusionWHILE=db_fetch($q)){
		if(substr($name, 0, strlen($exclusionWHILE['name']))==$exclusionWHILE['name']){
			$result=true;
		}
	}
	return $result;
}
?>