<?php
function action_delete_low_fodmap(){
	//Удаление записи
	if(isset($_GET['delete_low_fodmap'])){
		if(get_user_group($_SESSION['user'])=="writer"){
			db_connect("diet");
			db_query("DELETE FROM `low_fodmaps` WHERE `id`=".$_GET['low_fodmap']);
			header("location: ".uri_make(array('UriScript'=>'diet.php', 'UriClean'=>'DeleteAllArguments')));
		}
	}
}
?>