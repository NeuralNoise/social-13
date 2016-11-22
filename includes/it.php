<?php
function getOrAdd_document($name){
	$name=trim($name);
	if($name!=""){
		$qstr="SELECT * FROM `documents` WHERE `name`='".$name."'";
		$q=db_query($qstr);
		if(db_count($q)>0){
			$document_id=db_easy($qstr)['id'];
		}else{
			db_query("INSERT INTO `documents` SET `name`='".$name."'");
			$document_id=db_easy("SELECT * FROM `documents` WHERE `name`='".$name."'")['id'];
		}
		return $document_id;
	}else{
		return 0;
	}
}

function getOrAdd_model($name){
	$name=trim($name);
	if($name!=""){
		$qstr="SELECT * FROM `models` WHERE `name`='".$name."'";
		$q=db_query($qstr);
		if(db_count($q)>0){
			$model_id=db_easy($qstr)['id'];
		}else{
			db_query("INSERT INTO `models` SET `name`='".$name."'");
			$model_id=db_easy("SELECT * FROM `models` WHERE `name`='".$name."'")['id'];
		}
		return $model_id;
	}else{
		return 0;
	}
}
?>