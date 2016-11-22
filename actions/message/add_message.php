<?
//Добавление новости
function action_add_message(){
	if(@$_GET['add_message']=="yes"){
		if(isset($_POST['text'])){
			if(check_group("writer")){
				$user=db_easy("SELECT * FROM `users` WHERE `name`='".get_user()."'");
				$q=db_query("INSERT INTO `intr_message`
								SET	`user_id`={$user['id']},
									`date`='".date("Y-m-d H:i:s")."',
									`title`='".mysql_real_escape_string($_POST['title'])."',
									`text`='".mysql_real_escape_string($_POST['text'])."'
						");
				//$new_document_id=db_insert_id($q);
				header("location: ".uri_make_v1(array('UriScript'=>'intranet.php', 'UriClean'=>'DeleteAllArguments')));
			}
		}else{
			$GLOBALS['html'].=template_get('message/add_message', array('h1'=>"Добавить сообщение",
																	'action'=>uri_make_v1(array('UriScript'=>'intranet.php', 'add_message'=>'yes')),
																	'date'=>'',
																	'title'=>'',
																	'text'=>'',
																	'button'=>'Добавить'
																	));	
		}
	}
}
?>