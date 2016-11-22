<?
//Изменение комментария
function action_edit_comment(){
	if(@$_GET['edit_comment']=="yes"){
		$comment_id=$_GET['comment'];
		$comment=db_easy("SELECT * FROM `intr_comments` WHERE `id`=$comment_id");
		$user=db_easy("SELECT * FROM `users` WHERE `id`={$comment['user_id']}");
		$message=db_easy("SELECT * FROM `intr_message` WHERE `id`={$comment['message_id']}");
		if(isset($_POST['text'])){
			if(check_group("writer") || $user['name']==get_user()){
				db_query("UPDATE `intr_comments` SET
							`text`='".mysql_real_escape_string($_POST['text'])."'
						WHERE `id`=".$comment_id."
						");
				header("location: ".uri_make_v1(array("UriScript"=>'intranet.php', 'page'=>'message', 'message'=>$message['id'])));
			}
		}else{
				
			$comment=db_easy("SELECT * FROM `intr_comments` WHERE `id`=$comment_id LIMIT 1");
			$GLOBALS['html'].=template_get('comments/add_comment', array(  'h1'=>"Редактировать комментарий",
																	'action'=>uri_make_v1(array("UriScript"=>'intranet.php', 'page'=>'message', 'message'=>$message['id'], 'edit_comment'=>'yes', 'comment'=>$comment['id'])),
																	'text'=>$comment['text'],
																	'button'=>'Сохранить'
											));	
		}
	}
	
}
?>