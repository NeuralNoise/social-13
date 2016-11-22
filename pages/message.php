<?php
	function page_message(){
		
		$message_id=$_GET['message'];
		$message=db_easy("SELECT * FROM `intr_message` WHERE `id`=$message_id");
		$q_comm=db_query("SELECT * FROM `intr_comments` WHERE `message_id`=$message_id ORDER BY `date` DESC");
		$user=db_easy("SELECT * FROM `users` WHERE `id`=".$message['user_id']);
		$edit_del_comment_html="";
		if(check_group("writer") || $user['name']==get_user()){
			$edit_del_message_html.="<br/><a href='".uri_make_v1(array("UriScript"=>'intranet.php', 'page'=>'message', 'message'=>$message_id, 'edit_message'=>'yes', 'message'=>$message['id']))."' style='font-size:8pt;'>Редактировать</a>";
			$edit_del_message_html.="<a href='".uri_make_v1(array("UriScript"=>'intranet.php', 'page'=>'message', 'message'=>$message_id, 'delete_message'=>'yes', 'message'=>$message['id']))."' style='padding-left:10px;font-size:8pt;' onClick=\"if(!confirm('Удалить?')) return false;\">Удалить</a>";
		}
		$comments_html="";
		while($comment=db_fetch($q_comm)){
			$user=db_easy("SELECT * FROM `users` WHERE `id`={$comment['user_id']}");
			$comments_html.="<div style='margin:15px 0 0 0;padding:0 0 0 10px;border-left:2px solid #AAA;'>";
			$comments_html.="<span style='font-size:8pt;font-style:italic;'><b>".$user['name_rus'].",</b> ".date("d.m.Y H:i", strtotime($comment['date']))."</span><br/>".$comment['text']."</div>";
			if(check_group("writer") || $user['name']==get_user()){
				$comments_html.="<a href='".uri_make_v1(array("UriScript"=>'intranet.php', 'page'=>'message', 'message'=>$message_id, 'edit_comment'=>'yes', 'comment'=>$comment['id']))."' style='font-size:8pt;'>Редактировать</a>";
				$comments_html.="<a href='".uri_make_v1(array("UriScript"=>'intranet.php', 'page'=>'message', 'message'=>$message_id, 'delete_comment'=>'yes', 'comment'=>$comment['id']))."' style='padding-left:10px;font-size:8pt;' onClick=\"if(!confirm('Удалить?')) return false;\">Удалить</a>";
			}
		}
		
		$html.=template_get('message/message', array(		"user"=>$user['name_rus'],
													"date"=>date("d.m.Y", strtotime($message['date'])),
													"title"=>$message['title'],
													"edit_del_message"=>$edit_del_message_html,
													"text"=>$message['text'],
													"uri_back"=>uri_make_v1(array("UriScript"=>"intranet.php")),
													"uri_comment"=>uri_make_v1(array("UriScript"=>"intranet.php", "page"=>"message", "message"=>$message_id, "add_comment"=>"yes")),
													"comments"=>$comments_html
											));

		//Подключаем подвал
		$html.=template_get('footer');
		return $html;
	}
?>