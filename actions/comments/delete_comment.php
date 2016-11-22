<?php
function action_delete_comment(){
	//Удаление комментария
	if(isset($_GET['delete_comment'])){
		$comment_id=$_GET['comment'];
		$comment=db_easy("SELECT * FROM `intr_comments` WHERE `id`=$comment_id");
		$user=db_easy("SELECT * FROM `users` WHERE `id`={$comment['user_id']}");
		$message=db_easy("SELECT * FROM `intr_message` WHERE `id`={$comment['message_id']}");
		if(check_group("writer") || $user['name']==get_user()){
			db_query("DELETE FROM `intr_comments` WHERE `id`=".$comment_id);
			header("location: ".uri_make_v1(array("UriScript"=>'intranet.php', 'page'=>'message', 'message'=>$message['id'])));
		}
	}
}
?>