<?
//Изменение документа
function action_edit_message(){
	if(@$_GET['edit_message']=="yes"){
		$message_id=$_GET['message'];
		if(isset($_POST['text'])){
			if(get_user_group()=="writer"){
					
				db_query("UPDATE `intr_message` SET
							`title`='".mysql_real_escape_string($_POST['title'])."',
							`text`='".mysql_real_escape_string($_POST['text'])."'
						WHERE `id`=".$message_id."
						");
				if(isset($_POST['uri_after_action'])){
					header("location: ".$_POST['uri_after_action']);
				}else{
					header("location: ".uri_make_v1());
				}
			}
		}else{
			$message=db_easy("SELECT * FROM `intr_message` WHERE `id`=$message_id LIMIT 1");
			$GLOBALS['html'].=template_get('message/add_message', array(  'h1'=>"Редактировать сообщение",
																	'action'=>uri_make_v1(array('UriScript'=>'intranet.php', "edit_message"=>"yes", "message"=>$message_id)),
																	'date'=>date("d.m.Y", strtotime($message['date'])),
																	'title'=>$message['title'],
																	'text'=>$message['text'],
																	'button'=>'Сохранить',
																	'uri_after_action'=>$_SERVER['REQUEST_URI']
											));	
		}
	}
	
}
?>