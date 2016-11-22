<?
//Добавление новости
function action_add_comment(){
	if(@$_GET['add_comment']=="yes"){
		$message_id=$_GET['message'];
		$user=db_easy("SELECT * FROM `users` WHERE `name`='".get_user()."'");
		if(isset($_POST['text'])){
			$q=db_query("INSERT INTO `intr_comments`
							SET	date='".date("Y-m-d H:i:s")."',
								`user_id`={$user['id']},
								`message_id`=$message_id,
								`text`='".mysql_real_escape_string($_POST['text'])."'
					");
			//$new_document_id=db_insert_id($q);
			header("location: ".uri_make_v1(array('UriScript'=>'intranet.php', 'page'=>'message', 'message'=>$message_id)));
		}else{
			$GLOBALS['html'].=template_get('comments/add_comment', array('h1'=>"Добавить комментарий",
																	'action'=>uri_make_v1(array('UriScript'=>'intranet.php', 'SaveArguments'=>'yes')),
																	'text'=>'',
																	'button'=>'Добавить'
																	));	
		}
	}
}
?>