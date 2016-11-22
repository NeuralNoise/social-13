<?php
	function page_index(){
		$q=db_query("SELECT * FROM `intr_message`");
		$message_html="";
		if(get_user_group()=="writer"){
			$add_message_html="<a href='".uri_make_v1(array("UriScript"=>"intranet.php", "add_message"=>"yes"))."' style='padding:0 0 0 10px;'><img src='/_content/img/add-icon.png'/></a>";
		}else{
			$add_message_html="";
		}
		while($message=db_fetch($q)){
			$message_html.="<a href='".uri_make_v1(array("UriScript"=>"intranet.php", "page"=>"message", "message"=>$message['id']))."'>".trim($message['title'])."</a>";
			if(check_group("writer") || $user['name']==get_user()){
				//$message_html.="<br/><a href='".uri_make_v1(array("UriScript"=>'intranet.php', 'edit_message'=>'yes', 'message'=>$message['id']))."' style='font-size:8pt;'>Редактировать</a>";
				//$message_html.="<a href='".uri_make_v1(array("UriScript"=>'intranet.php', 'delete_message'=>'yes', 'message'=>$message['id']))."' style='padding-left:10px;font-size:8pt;' onClick=\"if(!confirm('Удалить?')) return false;\">Удалить</a>";
				//$message_html.="<br/><br/>";
				/*$comments_html.="<a href='".uri_make_v1(array("UriScript"=>'intranet.php', 'page'=>'message', 'message'=>$message['id'], 'edit_comment'=>'yes', 'comment'=>$comment['id']))."' style='font-size:8pt;'>Редактировать</a>";
				$comments_html.="<a href='".uri_make_v1(array("UriScript"=>'intranet.php', 'page'=>'message', 'message'=>$message['id'], 'delete_comment'=>'yes', 'comment'=>$comment['id']))."' style='padding-left:10px;font-size:8pt;' onClick=\"if(!confirm('Удалить?')) return false;\">Удалить</a>";
				*/ 
				$message_html.="<a href='".uri_make_v1(array("UriScript"=>"intranet.php", "edit_message"=>"yes", "message"=>$message['id']))."' style='padding:0 0 0 10px;'><img src='/_content/img/edit-icon.png'/></a>";
				$message_html.="<a href='".uri_make_v1(array("UriScript"=>"intranet.php", "delete_message"=>"yes", "message"=>$message['id']))."' style='padding:0 0 0 10px;' onClick=\"if(!confirm('Удалить?')) return false;\"><img src='/_content/img/remove-icon.png'/></a>"."<br/>";
				
			}
		}
		
		$contacts_html="";
		$q=db_query("SELECT * FROM `users`");
		while($user=db_fetch($q)){
			$contacts_html.="<a href='".uri_make_v1(array("UriScript"=>"intranet.php", "page"=>"contact", "name"=>$user['name']))."'>".$user['name_rus']."</a>"."<br/>";
		}

		$html.=template_get('intranet/intranet', array(	"add_message"=>$add_message_html,
														"message"=>$message_html,
														"contacts"=>$contacts_html
													));

		//Подключаем подвал
		$html.=template_get('footer');
		return $html;
	}
?>