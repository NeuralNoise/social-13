<?php
function action_delete_message(){
	//Удаление записи
	if(isset($_GET['delete_message'])){
		if(check_group("writer")){
			db_query("DELETE FROM `intr_message` WHERE `id`=".$_GET['message']);
			header("location: ".uri_make(array('UriScript'=>'intranet.php', 'UriClean'=>'DeleteAllArguments')));
		}
	}
}
?>