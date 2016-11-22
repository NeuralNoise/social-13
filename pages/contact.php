<?php
	function page_contact(){
		$user_name=$_GET['name'];
		$user=db_easy("SELECT * FROM `users` WHERE `name`='".$user_name."'");

		$edit_del_comment_html="";
		if(check_group("writer") || $user['name']==get_user()){
		}
		
		$html.=template_get('contacts/contact', array(
													"name_rus"=>$user['name_rus'],
													"position"=>$user['position'],
													"mobile_phone"=>$user['mobile_phone']
											));

		//Подключаем подвал
		$html.=template_get('footer');
		return $html;
	}
?>